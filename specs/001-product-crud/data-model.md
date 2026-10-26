# Data Model: Cadastro e gerenciamento de produtos

## Product

Representa um item do catálogo.

| Field | Type | Required | Rules |
|---|---|---:|---|
| `id` | Integer | Yes | Identificador crescente gerado pelo banco. |
| `name` | String | Yes | Texto não vazio após trim; até 255 caracteres. |
| `price` | Decimal(12, 2) | Yes | Maior ou igual a zero; no máximo duas casas decimais. |
| `category` | String | Yes | Texto não vazio após trim; até 255 caracteres. |
| `quantity` | Integer | Yes | Inteiro maior ou igual a zero. |
| `seed_key` | Nullable string | No | Chave interna única para identificar registros de demonstração; não é entrada nem saída da API. |
| `created_at` | Date/time | Yes | Data/hora de criação gerenciada pela aplicação. |
| `updated_at` | Date/time | Yes | Data/hora da atualização mais recente. |
| `deleted_at` | Nullable date/time | No | Nulo enquanto ativo; preenchido na exclusão lógica. |

`seed_key` é uma chave operacional interna para identificar os produtos de demonstração criados pelo seeder. Registros criados pela API têm `seed_key = null`. A API não permite atribuí-la ou alterá-la. Como o seeder reinicializa todo o catálogo, essa chave não limita a exclusão aos produtos de demonstração.

### Lifecycle

- Created → Active.
- Active → Active on successful update.
- Active → Soft-deleted on successful delete.
- A soft-deleted product is omitted from ordinary find/list operations. Restore and permanent deletion are out of scope.
- The HTTP API does not expose permanent deletion.

### Validation and indexes

- `name` and `category`: required non-empty text, maximum 255 characters.
- `price`: non-negative decimal, at most two fractional digits, stored with fixed decimal precision.
- `quantity`: non-negative integer.
- Unique nullable index on `seed_key` identifies the 100 products created by one seed run without imposing uniqueness on product names or categories; every run resets the catalog before reusing the deterministic key sequence.
- Indexes on `price` and `quantity` support equality filters. A normal index does not optimize substring searches on `name` or `category`.
- Stable list ordering uses `id` ascending.
- API requests send `price` as a decimal string with up to two fractional digits; responses use a canonical string with exactly two digits after the decimal point.
- SQLite uses flexible numeric affinity and may not enforce decimal precision solely from the column declaration; request validation enforces non-negativity and scale.

## ProductActivity

Internal immutable record of a successful product mutation. It is not exposed in API responses or through an endpoint.

| Field | Type | Required | Rules |
|---|---|---:|---|
| `id` | Integer | Yes | Identificador crescente. |
| `product_id` | Integer | Yes | Foreign key to the product; retained when the product is soft-deleted. |
| `activity_type` | String/enum | Yes | One of `created`, `updated`, `deleted`. |
| `created_at` | Date/time | Yes | Time the successful mutation occurred. |

No actor is stored because authentication is outside this feature. Failed mutations, reads, list operations, and seeder inserts do not create activity records. Product mutation and activity creation occur in the same database transaction.

## Relationships

- Product has many ProductActivity records.
- ProductActivity belongs to one Product.
- A database foreign key prevents accidental physical removal of a product that still has history. Normal API deletion is soft deletion and preserves the relationship.

## Seed data

Each non-production execution of the seeder runs in a single database transaction. It first physically removes all `ProductActivity` and `Product` records, then creates 100 new active products with stable keys such as `demo-product-001` through `demo-product-100`. Across the seeded products, it provides at least 10 distinct names, 5 categories, 10 prices, and 10 quantities. Re-running therefore resets the entire catalog and its complete activity history; product IDs may change. New seed inserts create no `ProductActivity` records. Any failure rolls back the cleanup and recreation. The seeder refuses to run in production. Migrations change schema only and do not perform this data reset.
