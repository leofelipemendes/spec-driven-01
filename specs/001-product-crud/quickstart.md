# Quickstart: validar cadastro e gerenciamento de produtos

## Pré-requisitos

- Docker Engine e Docker Compose.
- A aplicação em `backend/`; o container fornece PHP 8.4, Composer 2, PDO SQLite e as demais extensões requeridas.
- Não é necessário instalar PHP ou Composer no host.

Execute a configuração inicial a partir da raiz do repositório; os comandos seguintes são executados em `backend/`.

## Construir e iniciar o container

```sh
cd backend
cp .env.example .env
touch database/database.sqlite
docker compose build
docker compose run --rm api php artisan key:generate
docker compose up -d
```

Resultado esperado: a imagem é construída com PHP 8.4, Composer, extensões Laravel/SQLite e dependências do projeto; a API fica disponível em `http://127.0.0.1:8000`. Para uma porta diferente, defina `APP_PORT` no ambiente do Compose.

> **Atenção:** cada execução de `ProductSeeder` apaga permanentemente todos os produtos e todas as atividades existentes antes de recriar os 100 produtos de demonstração. Use somente em bancos didáticos/de desenvolvimento. A operação é transacional: em caso de falha, os dados anteriores são restaurados. Migrations alteram o esquema, mas não fazem essa limpeza.

## Executar comandos Artisan

```sh
docker compose exec api php artisan migrate
docker compose exec api php artisan db:seed --class=ProductSeeder
```

Resultado esperado: migrations criam produtos e atividades; no ambiente local existem exatamente 100 produtos de exemplo ativos, com pelo menos 10 nomes, 5 categorias, 10 preços e 10 quantidades distintos, e nenhum registro de atividade criado pela carga.

Execute o seeder novamente:

```sh
docker compose exec api php artisan db:seed --class=ProductSeeder
```

Resultado esperado: todo o catálogo e histórico anteriores são removidos e existem somente 100 novos produtos de demonstração ativos, identificáveis pelas chaves internas; as atividades antigas também foram apagadas e os novos produtos não têm atividades.

## Executar e testar a API

Validar os caminhos básicos pela porta publicada no Compose (exemplo: `8000`):

```sh
curl -i http://127.0.0.1:8000/api/products
curl -i 'http://127.0.0.1:8000/api/products?name=notebook&category=Informatica&page=1&per_page=15'
curl -i -X POST http://127.0.0.1:8000/api/products \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"name":"Produto de teste","price":"19.90","category":"Acessorios","quantity":5}'
```

Resultado esperado: listagens retornam coleção JSON paginada; criação retorna HTTP 201 com representação do produto. Use o `id` retornado para testar `GET /api/products/{id}`, `PUT /api/products/{id}` (substituição), `PATCH /api/products/{id}` (atualização parcial) e `DELETE /api/products/{id}`. A exclusão retorna HTTP 204, e consultas posteriores retornam 404. Todas as respostas usam o contrato em [contracts/openapi.yaml](./contracts/openapi.yaml).

## Verificações automatizadas

```sh
docker compose exec api php artisan test
```

Resultado esperado: testes unitários e de integração passam, incluindo validação de campos, filtros independentes e combinados, paginação, soft delete, auditoria transacional, ausência de atividades para novos registros do seed, reinicialização integral e transacional do catálogo/histórico e bloqueio em produção.

## Persistência e encerramento

O Compose deve mapear o arquivo SQLite para armazenamento persistente. Após reiniciar o container com `docker compose restart`, os produtos continuam disponíveis.

```sh
./scripts/verify-container.sh
docker compose down
```
