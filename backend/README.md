# Product Catalog API

API didática de cadastro e gerenciamento de produtos com Laravel 13, PHP 8.4 e SQLite. O runtime, o Composer 2 e as extensões PHP são fornecidos pelo Docker; o host precisa apenas de Docker Engine e Docker Compose.

## Inicialização

Na primeira execução:

```sh
cp .env.example .env
touch database/database.sqlite
docker compose build
docker compose run --rm api php artisan key:generate
docker compose up -d
docker compose exec api php artisan migrate
```

A API fica disponível em `http://localhost:8000/api`. Para alterar a porta publicada, defina `APP_PORT` antes de executar o Compose.

## Comandos úteis

```sh
docker compose exec api php artisan test
docker compose exec api composer install
docker compose exec api php artisan migrate:status
docker compose logs -f api
docker compose down
```

Para verificar a configuração Compose, a disponibilidade HTTP e a persistência após reiniciar o serviço:

```sh
./scripts/verify-container.sh
```

O SQLite fica em `database/database.sqlite`, persistido no host. O volume nomeado `vendor` mantém as dependências Composer fora do código montado.

## Catálogo de demonstração

> **Atenção:** cada execução de `ProductSeeder` em ambiente não produtivo apaga permanentemente todos os produtos e atividades existentes antes de criar exatamente 100 novos produtos. A operação é transacional: falhas revertem a limpeza. O seeder é bloqueado em produção. Migrations alteram o esquema e não limpam os dados.

```sh
docker compose exec api php artisan db:seed --class=ProductSeeder
```

## API

- `GET /api/products` lista produtos ativos com paginação e filtros combináveis `name`, `price`, `category` e `quantity`.
- `POST /api/products` cria um produto.
- `GET /api/products/{id}` consulta um produto ativo.
- `PUT /api/products/{id}` substitui os dados de um produto ativo.
- `PATCH /api/products/{id}` atualiza somente os campos enviados.
- `DELETE /api/products/{id}` faz exclusão lógica e retorna HTTP 204 sem corpo.

As mutações bem-sucedidas geram histórico interno transacional. Não existe endpoint público para consultar as atividades.

Importe [`postman/Product Catalog API.postman_collection.json`](./postman/Product%20Catalog%20API.postman_collection.json) no Postman para testar os endpoints. Execute **Cadastrar produto** antes de consultar, atualizar ou excluir por ID; a collection guarda o ID retornado automaticamente.

O contrato e os cenários detalhados estão em [`../specs/001-product-crud/contracts/openapi.yaml`](../specs/001-product-crud/contracts/openapi.yaml) e [`../specs/001-product-crud/quickstart.md`](../specs/001-product-crud/quickstart.md).
