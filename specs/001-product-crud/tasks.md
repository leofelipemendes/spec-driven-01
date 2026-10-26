---

description: "Tasks for product registration API implementation"

---

# Tasks: Cadastro e gerenciamento de produtos

**Input**: Design documents from `specs/001-product-crud/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/openapi.yaml`, `quickstart.md`

**Tests**: Required by the project constitution: every business rule must have unit tests and every endpoint must have at least one happy-path integration test. Include feature tests per user story and service tests for mutation and audit rules.

**Organization**: Tasks are grouped by user story in priority order. The Laravel project is created in `backend/`; do not create application code under `src/`.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the Laravel project and the containerized development environment.

- [X] T001 Create the Laravel 13 project in `backend/` using Composer 2 from a container, targeting PHP 8.3–8.5 and preserving all existing repository files
- [X] T002 [P] Create `backend/.dockerignore` excluding `.env`, `vendor/`, SQLite runtime files, and other local-only artifacts from the Docker build context
- [X] T003 Create `backend/Dockerfile` and `backend/compose.yaml` with a supported PHP base image, Composer 2, PDO SQLite and Laravel-required extensions; configure the API service, published HTTP port, project source mount, and persistent SQLite file
- [X] T004 Configure `backend/.env.example` and `backend/config/database.php` for local SQLite at `database/database.sqlite`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Ensure every story can use the same API routing, JSON behavior, and database/test setup.

**⚠️ CRITICAL**: Complete this phase before implementing user stories.

- [X] T005 Configure `backend/bootstrap/app.php` to register `routes/api.php` and retain Laravel's standard API middleware
- [X] T006 Configure `backend/bootstrap/app.php` or `backend/app/Exceptions/Handler.php` to return JSON for API validation, not-found, and unexpected errors without adding HTML view responses
- [X] T007 Configure `backend/phpunit.xml` to run the Laravel test suite with an isolated SQLite in-memory database and deterministic app environment
- [X] T008 Configure the SQLite connection in `backend/config/database.php` to enforce foreign keys and verify the setting with a database-level test in `backend/tests/Feature/Database/SqliteConfigurationTest.php`

**Checkpoint**: Laravel boots in the container, API routes resolve, JSON errors are consistent, and tests use an isolated SQLite database.

---

## Phase 3: User Story 1 - Gerenciar produtos (Priority: P1) 🎯 MVP

**Goal**: Deliver create, read, update, and soft-delete operations with validation and explicit product JSON resources.

**Independent Test**: Through HTTP and service unit tests, create a valid product, read and update it, soft-delete it, and verify the deleted product returns 404, invalid requests preserve stored values, every successful create/update/delete creates the corresponding activity, reads create none, and failure to persist an activity rolls back its mutation.

### Tests for User Story 1

- [X] T009 [P] [US1] Add HTTP integration tests for POST, GET-by-id, PUT, and DELETE happy paths plus 404 and validation; assert one associated activity for every successful mutation including repeated updates, prior history remains after soft delete, reads/rejections create none, and no public activity endpoint exists in `backend/tests/Feature/Api/ProductCrudTest.php`
- [X] T010 [P] [US1] Add unit tests for trimmed required text up to 255 characters, non-negative prices with at most two decimal places, and non-negative integer quantities in `backend/tests/Unit/Http/Requests/ProductRequestTest.php`; test ProductService create/read/update/soft-delete/not-found behavior and atomic rollback when activity persistence fails in `backend/tests/Unit/Services/ProductServiceTest.php`

### Implementation for User Story 1

- [X] T011 [US1] Create product and activity migrations in `backend/database/migrations/*_create_products_table.php` and `backend/database/migrations/*_create_product_activities_table.php`; products have `id`, `name`, `price`, `category`, `quantity`, `seed_key`, timestamps, nullable `deleted_at`, required non-empty trimmed `name` and `category` up to 255 characters, non-negative decimal `price` with at most two fractional digits, non-negative integer `quantity`, and nullable unique `seed_key`; activities have a required product foreign key, type constrained to `created`, `updated`, or `deleted`, and timestamp, with physical product deletion prevented
- [X] T012 [US1] Create `Product` with soft deletes, decimal price casting, allowed fillable fields, hidden seed key, and `activities` relationship plus `ProductActivity` with immutable activity type, timestamp, and product relationship in `backend/app/Models/Product.php` and `backend/app/Models/ProductActivity.php`
- [X] T013 [US1] Create `StoreProductRequest` and `UpdateProductRequest` with the exact product validation rules in `backend/app/Http/Requests/Product/StoreProductRequest.php` and `backend/app/Http/Requests/Product/UpdateProductRequest.php`
- [X] T014 [US1] Create `ProductResource` returning only `id`, `name`, `price` as a canonical decimal string with exactly two fractional digits, `category`, `quantity`, `created_at`, and `updated_at` in `backend/app/Http/Resources/ProductResource.php`
- [X] T015 [US1] Implement create, lookup, update, and soft-delete operations using Eloquent in `backend/app/Services/ProductService.php`; persist one corresponding activity with each successful API mutation in the same transaction, roll back on activity failure, and do not create activities for reads or expose hard-delete/restore operations
- [X] T016 [US1] Implement `ProductController` using validated Form Request data and `ProductResource` responses in `backend/app/Http/Controllers/Api/ProductController.php`; controllers must not query or mutate the database directly, and resources must not expose activities
- [X] T017 [US1] Register the API resource for products in `backend/routes/api.php`, mapping index/store/show/update/destroy to the standard HTTP verbs; support full replacement with PUT and partial update with PATCH (200)
- [X] T018 [US1] Run the US1 HTTP, validation, and service unit tests in `backend/tests/Feature/Api/ProductCrudTest.php`, `backend/tests/Unit/Http/Requests/ProductRequestTest.php`, and `backend/tests/Unit/Services/ProductServiceTest.php` inside the Docker Compose service and fix failures

**Checkpoint**: The CRUD endpoints return the specified JSON/status codes, reject invalid input without changing data, hide soft-deleted products, and atomically record each successful mutation without exposing history.

---

## Phase 4: User Story 2 - Consultar e filtrar o catálogo (Priority: P2)

**Goal**: List active products with stable pagination and independently combinable filters.

**Independent Test**: Use products with distinct names, prices, categories, and quantities to verify each individual filter, combined AND behavior, pagination links/metadata, and empty results.

### Tests for User Story 2

- [X] T019 [P] [US2] Add HTTP integration tests for default and maximum page sizes, filter query preservation, independent filters, combined AND filters, no matches, and invalid parameters in `backend/tests/Feature/Api/ProductListingTest.php`
- [X] T020 [P] [US2] Add unit tests for filter validation, decimal price equality, and ASCII case-insensitive but accent-sensitive text matching in `backend/tests/Unit/Services/ProductSearchTest.php`

### Implementation for User Story 2

- [X] T021 [US2] Create `IndexProductRequest` validating name and category as optional text up to 255 characters, price as a non-negative decimal with at most two fractional digits, quantity as a non-negative integer, `page` as an integer of at least 1, and `per_page` as an integer from 1 through 100 with default 15 in `backend/app/Http/Requests/Product/IndexProductRequest.php`
- [X] T022 [US2] Extend `ProductService` to apply only validated filters with AND semantics, partial `LIKE` matching for name/category that ignores ASCII case and remains accent-sensitive, exact price/quantity matching, active-only scope, `id` ascending order, database pagination, and preserved filter query parameters in `backend/app/Services/ProductService.php`
- [X] T023 [US2] Add GET `/products` to return a paginated `ProductResource` collection with standard navigation links and metadata in `backend/app/Http/Controllers/Api/ProductController.php` and `backend/routes/api.php`
- [X] T024 [US2] Run the listing and search tests in `backend/tests/Feature/Api/ProductListingTest.php` and `backend/tests/Unit/Services/ProductSearchTest.php` inside the Docker Compose service and fix failures

**Checkpoint**: Each filter and every filter combination returns only matching active products; pagination exposes navigation and does not exceed 100 products per page.

---

## Phase 5: User Story 3 - Preparar catálogo de demonstração (Priority: P3)

**Goal**: Reset the non-production product catalog and its entire activity history, then create exactly 100 varied demo products without generating new activity events.

**Independent Test**: Create API products and activity history, run the seeder in a non-production environment, and verify the entire prior catalog and history are replaced transactionally by exactly 100 new active demo products meeting all variety thresholds and having no activities; verify a failed reset rolls back and production execution fails before writing.

### Tests for User Story 3

- [X] T025 [P] [US3] Add database integration tests proving each seed run removes all prior products and activities, creates exactly 100 new active products with at least 10 distinct names, 5 categories, 10 prices, and 10 quantities and no new activities, and rolls back all deletion/recreation on failure in `backend/tests/Feature/Database/ProductSeederTest.php`; unit-test the production guard and deterministic 100-key/value-variety generation in `backend/tests/Unit/Seeders/ProductSeederTest.php`

### Implementation for User Story 3

- [X] T026 [US3] Create `ProductFactory` with valid varied values for the product fields and prices with at most two decimal places in `backend/database/factories/ProductFactory.php`
- [X] T027 [US3] Implement `ProductSeeder` to refuse `production` with an explicit error before writes; in one transaction physically delete all ProductActivity rows before all Product rows, then create exactly 100 new active products with deterministic `demo-product-001` through `demo-product-100` keys, at least 10 distinct names, 5 categories, 10 prices, and 10 quantities, and no activity rows; any failure must roll back the entire reset in `backend/database/seeders/ProductSeeder.php`
- [X] T028 [US3] Register `ProductSeeder` in `DatabaseSeeder` for non-production use, preserving its production refusal and explicit destructive-reset behavior in `backend/database/seeders/DatabaseSeeder.php`
- [X] T029 [US3] Run the seeder reset integration and unit tests in `backend/tests/Feature/Database/ProductSeederTest.php` and `backend/tests/Unit/Seeders/ProductSeederTest.php` inside the Docker Compose service and fix failures

**Checkpoint**: After each successful non-production seed run, the entire prior catalog and activity history are gone and exactly 100 varied active demo products remain without activity rows; failed runs restore the previous database state.

---

## Phase 6: User Story 4 - Executar a API em container (Priority: P4)

**Goal**: Build and run the API, Artisan commands, and tests using Docker without requiring PHP or Composer on the host.

**Independent Test**: On a host with Docker Engine and Compose only, build and start the service, run migrations, seed and tests in the container, send an HTTP request, restart the container, and verify SQLite data remains.

### Tests for User Story 4

- [X] T030 [P] [US4] Add a container smoke-test script in `backend/scripts/verify-container.sh` that checks Compose config, service readiness, HTTP response, and persistence after restart

### Implementation for User Story 4

- [X] T031 [US4] Configure `backend/compose.yaml` health check, development environment, writable persistent SQLite mount, and service command for the HTTP server plus Composer and Artisan exec commands
- [X] T032 [US4] Document image build, service startup, migrations, seeder, tests, HTTP access, persistence, and shutdown commands with expected results in `backend/README.md`
- [X] T033 [US4] Build and start the service, run migrations, seeder, and tests inside the container, then verify HTTP readiness and SQLite persistence across restart using `backend/scripts/verify-container.sh`

**Checkpoint**: The API is runnable and testable using Docker/Compose alone on the host, and its SQLite data survives container restart.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Verify that the complete feature is aligned with its specification and runnable instructions.

- [X] T034 [P] Validate all API request/response/status examples against `specs/001-product-crud/contracts/openapi.yaml` and update the contract if implementation behavior differs
- [X] T035 Run all automated tests and the end-to-end scenarios in `specs/001-product-crud/quickstart.md` through Docker Compose and resolve any failures
- [X] T036 Review and update `backend/README.md` and `specs/001-product-crud/quickstart.md` for accurate Docker setup, port, migrations, seeder, API examples, tests, persistence, and cleanup

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Starts immediately; T001 and T002 edit separate files and can proceed in parallel; T003 depends on the project scaffold and `.dockerignore`; T004 depends on T001.
- **Foundational (Phase 2)**: Depends on Setup; blocks all user stories.
- **US1 (Phase 3)**: Depends on Foundational; provides product CRUD, product/activity schema, and transactional internal history as the complete API MVP.
- **US2 (Phase 4)**: Depends on US1 product model, table, route/controller, and ProductService.
- **US3 (Phase 5)**: Depends on US1 product schema, especially `seed_key`.
- **US4 (Phase 6)**: Depends on Setup Docker files and Foundational runtime. Health route/test may be developed alongside product stories; full acceptance waits for migration and seeder completion.
- **Polish (Phase 7)**: Depends on all user stories selected for delivery.

### User Story Dependencies

- **US1 (P1)**: After Foundational; includes transactional audit behavior and is the spec-compliant API MVP.
- **US2 (P2)**: Requires US1's Product model and service; independently testable after that foundation.
- **US3 (P3)**: Requires US1's product schema and database setup.
- **US4 (P4)**: Base Docker build/configuration is created in Setup; Compose acceptance and end-to-end verification depend on completed application routes, migrations, and seeder from US1/US3.

### Within Each User Story

- Write the specified tests first and confirm they fail before implementing behavior.
- Implement migrations/models before dependent services; services before controller integration.
- Complete the story's test suite and independent acceptance criteria before moving to the next priority.
- When a schema requirement is shared, merge the product and activity migration design before running migrations in integration environments.

### Parallel Opportunities

- T002 Docker ignore rules can be prepared while T001 generates the Composer project; T003 follows T001/T002, and T004 follows T001.
- Within US1, T009 and T010 can be developed in parallel; model/schema and request/resource work can be split by file after the scaffold.
- Within US2, T019 and T020 can be developed in parallel; list request and service filter implementation are separate files after US1.
- US2, US3, and US4 have distinct tests and files and can be staffed in parallel after US1's shared product foundation, subject to the listed dependencies.
- T034 contract review and T036 documentation review can be prepared in parallel after feature behavior stabilizes; T035 is the final integrated validation.

## Parallel Example: User Story 1

```text
T009 Product CRUD HTTP integration tests in backend/tests/Feature/Api/ProductCrudTest.php
T010 Product request validation tests in backend/tests/Unit/Http/Requests/ProductRequestTest.php

After the product schema is agreed:
T012 Product and ProductActivity models in backend/app/Models/
T013 Product Form Requests in backend/app/Http/Requests/Product/
T014 ProductResource in backend/app/Http/Resources/ProductResource.php
```

## Implementation Strategy

### MVP First (User Story 1)

1. Complete Setup and Foundational phases.
2. Complete US1: product and activity migrations/models, validation, API Resource, transactional service, routes, and CRUD/audit tests.
3. Run US1 integration and unit tests inside Docker.
4. Stop and demonstrate create, read, update, and soft delete, including atomic internal history, before adding filtering or seeding.

### Incremental Delivery

1. Setup + Foundational → reproducible Laravel runtime and test configuration.
2. US1 → spec-compliant CRUD MVP including transactional internal history.
3. US2 → paginated filtered listing.
4. US3 → destructive transactional reset followed by exactly 100 varied local demo products.
5. US4 → verified Docker-only developer workflow and persistent SQLite across restart.
6. Polish → contract, documentation, and complete quickstart validation.

### Parallel Team Strategy

1. Complete Setup + Foundational together.
2. Deliver US1 as the shared product schema and service foundation.
3. Assign US2, US3, and US4 to separate contributors after their explicit dependencies are available.
4. Integrate database migrations and run the full Compose-based test suite during Polish.

## Notes

- Every task uses the required unchecked checkbox, sequential ID, optional `[P]`, story label for user-story phases, and concrete file path.
- `[P]` is used only where work can be performed independently in separate files.
- The environment currently lacks PHP and Composer; all application and test commands must use the Docker Compose service.
- Keep generated application source under `backend/`; do not create files in `src/`.
