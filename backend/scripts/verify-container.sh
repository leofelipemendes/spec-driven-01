#!/usr/bin/env bash

set -euo pipefail

cd "$(dirname "$0")/.."

docker compose config --quiet

if [[ ! -f .env ]]; then
    cp .env.example .env
fi

mkdir -p database
touch database/database.sqlite

if ! grep -q '^APP_KEY=base64:' .env; then
    docker compose run --rm api php artisan key:generate --force
fi

docker compose up -d --build
docker compose exec -T api php artisan migrate --force

published_port=$(docker compose port api 8000 | sed 's/.*://')
base_url="http://127.0.0.1:$published_port"
ready=false

for _ in {1..30}; do
    if curl --silent --fail "$base_url/api/products?per_page=1" >/dev/null; then
        ready=true
        break
    fi

    sleep 1
done

if [[ "$ready" != true ]]; then
    docker compose logs api
    printf '%s\n' 'API did not become ready within 30 seconds.' >&2
    exit 1
fi

product_name="Container smoke test $(date +%s)"
response=$(curl --fail --silent --show-error \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -d "{\"name\":\"$product_name\",\"price\":\"0.01\",\"category\":\"Smoke test\",\"quantity\":1}" \
    "$base_url/api/products")

if [[ ! "$response" =~ \"id\":([0-9]+) ]]; then
    printf 'Could not read product id from API response: %s\n' "$response" >&2
    exit 1
fi

product_id="${BASH_REMATCH[1]}"

docker compose restart api

for _ in {1..30}; do
    if curl --silent --fail "$base_url/api/products?per_page=1" >/dev/null; then
        break
    fi

    sleep 1
done

persisted=$(curl --fail --silent --show-error "$base_url/api/products/$product_id")
if [[ "$persisted" != *"$product_name"* ]]; then
    printf '%s\n' 'The product was not available after restarting the API container.' >&2
    exit 1
fi

delete_status=$(curl --silent --output /dev/null --write-out '%{http_code}' \
    -X DELETE "$base_url/api/products/$product_id")
if [[ "$delete_status" != 204 ]]; then
    printf 'Smoke-test cleanup returned HTTP %s instead of 204.\n' "$delete_status" >&2
    exit 1
fi

printf '%s\n' 'Container configuration, HTTP readiness, and SQLite persistence verified.'
