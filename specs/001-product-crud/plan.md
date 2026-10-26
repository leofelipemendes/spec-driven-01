# Implementation Plan: Cadastro e gerenciamento de produtos

**Branch**: `001-product-crud` | **Date**: 2026-10-25 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification at `specs/001-product-crud/spec.md`

## Summary

Criar uma API REST em Laravel 13 para cadastro, consulta, atualização e exclusão lógica de produtos, com listagem paginada e filtros combináveis. Persistir alterações de criação, atualização e exclusão em histórico interno na mesma transação da operação. Criar migrations para os produtos e o histórico e um seeder didático que, em uma única transação, apaga todos os produtos e atividades e recria 100 produtos de exemplo; o seeder recusa execução em produção. Os contratos HTTP estão descritos em [contracts/openapi.yaml](./contracts/openapi.yaml).

O repositório contém apenas documentação e não possui uma aplicação Laravel ou ferramentas PHP/Composer instaladas no ambiente atual. A aplicação será criada em `backend/`, deixando a raiz disponível para as especificações e preservando `src/`, que já contém uma instrução para não gerar arquivos manualmente ali. O ambiente de execução será fornecido por uma imagem Docker, orquestrada com Docker Compose.

## Technical Context

**Language/Version**: PHP 8.3–8.5, faixa suportada atualmente pelo Laravel 13.

**Primary Dependencies**: Laravel 13; componentes incluídos no framework para roteamento, validação, Eloquent, API Resources, migrations, seeders e testes; PHP 8.3–8.5 com Composer 2 e extensões PHP necessárias, fornecidos pela imagem Docker. Nenhuma dependência Composer adicional planejada.

**Storage**: SQLite via PDO SQLite; arquivo `backend/database/database.sqlite`, persistido em volume/mount Docker.

**Testing**: PHPUnit, usando os recursos de teste HTTP e de banco do Laravel; execução via container, com SQLite em memória quando suportado pelo cenário. Cada regra de negócio terá teste unitário em `ProductRequestTest`, `ProductServiceTest`, `ProductSearchTest` ou `ProductSeederTest`; testes de integração cobrirão cada endpoint, persistência, seed e configuração SQLite.

**Target Platform**: Aplicação web PHP executada em container Docker; Docker Engine e Docker Compose são necessários no host.

**Project Type**: Serviço web / API HTTP sem frontend.

**Performance Goals**: Sem meta de latência ou throughput especificada. A listagem deve limitar cada página a 100 itens e usar paginação no banco.

**Constraints**: API JSON em português do Brasil; rotas em `routes/api.php`; runtime, Composer 2 e extensões PHP da aplicação na imagem Docker; inicialização por Docker Compose; SQLite persistido em volume; Form Requests; API Resources; migrations; soft delete; filtros de texto por correspondência parcial sem distinção de caixa ASCII (acentos são significativos) e filtros numéricos por igualdade; autenticação fora do escopo; logs internos somente para mutações e não expostos pela API; seeder intencionalmente destrutivo, transacional e bloqueado em produção.

**Scale/Scope**: Um catálogo didático, uma entidade de produto, um histórico interno de alterações por produto, quatro operações CRUD e listagem paginada. O MVP inclui o histórico transacional das mutações. O seed fornece 100 produtos de demonstração com variedade mensurável.

## Constitution Check

| Gate | Resultado | Evidência |
|---|---|---|
| PHP 8.3–8.5 e Laravel 13 | PASS | Stack exigida pela constituição; a faixa PHP suportada atualmente é confirmada pela documentação; a aplicação será criada em `backend/`. |
| API JSON sem views | PASS | Escopo é exclusivamente HTTP/JSON. |
| Rotas em `routes/api.php` | PASS | Estrutura Laravel planejada inclui esse arquivo. |
| Form Requests e Resources | PASS | Validação de entrada e representação de respostas serão responsabilidades dedicadas. |
| Controllers não acessam banco diretamente | PASS | Controller coordena requisições e delega consultas e mutações ao serviço de aplicação; o serviço usa Eloquent e mantém as gravações com auditoria transacionais. |
| SQLite e migrations | PASS | Banco SQLite; tabelas criadas por migrations. |
| Testes unitários e de integração | PASS | Cobertura das regras de domínio e dos endpoints, incluindo CRUD, filtros, soft delete, auditoria e seed. |
| Dados sensíveis e segurança | PASS | Sem autenticação neste escopo; sem credenciais. O histórico é interno e não há rota que o exponha. |
| Simplicidade e dependências | PASS | Sem Repository ou pacote extra; Service somente para manter mutação e auditoria atômicas. |
| Limitação do seeder | PASS | Seeder recusa execução em produção antes de qualquer mutação; o reset integral em ambiente não produtivo é explícito e coberto por teste transacional. |
| Runtime containerizado | PASS | A imagem fornece PHP, Composer e extensões; Compose executa API, Artisan e testes sem requerer PHP/Composer no host. |

Não há violações constitucionais que precisem de exceção.

## Project Structure

### Documentation (this feature)

```text
specs/001-product-crud/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── checklists/
│   └── requirements.md
├── contracts/
│   └── openapi.yaml
└── tasks.md
```

`tasks.md` contém as tarefas geradas pelo comando `/speckit-tasks`.

### Source Code (`backend/`)

```text
backend/
├── .env.example
├── .dockerignore
├── Dockerfile
├── compose.yaml
├── artisan
├── composer.json
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/ProductController.php
│   │   ├── Requests/Product/
│   │   │   ├── IndexProductRequest.php
│   │   │   ├── StoreProductRequest.php
│   │   │   └── UpdateProductRequest.php
│   │   └── Resources/ProductResource.php
│   ├── Models/
│   │   ├── Product.php
│   │   └── ProductActivity.php
│   └── Services/ProductService.php
├── bootstrap/app.php
├── config/database.php
├── database/
│   ├── factories/ProductFactory.php
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── ProductSeeder.php
├── routes/api.php
├── public/index.php
└── tests/
    ├── Feature/Api/ProductCrudTest.php
    ├── Feature/Api/ProductListingTest.php
    ├── Feature/Database/ProductSeederTest.php
    ├── Feature/Database/SqliteConfigurationTest.php
    └── Unit/
        ├── Http/Requests/ProductRequestTest.php
        ├── Services/ProductServiceTest.php
        ├── Services/ProductSearchTest.php
        └── Seeders/ProductSeederTest.php
```

**Structure Decision**: Criar uma aplicação Laravel única em `backend/`, separada dos documentos SDD na raiz e sem gerar arquivos dentro de `src/`. A imagem é definida em `backend/Dockerfile`; `backend/compose.yaml` constrói e inicia o serviço HTTP e preserva o banco SQLite em volume. Usar a organização convencional de controllers, requests, resources, models, services, migrations, seeders e testes. `backend/bootstrap/app.php` será atualizado para carregar `backend/routes/api.php` caso o scaffold escolhido não o registre por padrão.

## Design Decisions

- Utilizar os recursos nativos do Laravel; não instalar Sanctum, pacote de auditoria, gerador de dados ou camada de Repository. Autenticação e consulta pública do histórico estão fora do escopo.
- Construir a imagem Docker com uma base oficial de PHP 8.3–8.5 compatível com Laravel 13, Composer 2 e as extensões usadas pela aplicação, incluindo PDO SQLite. A imagem deve permitir instalar as dependências Composer e rodar Artisan e PHPUnit.
- Executar API, migrations, seeders e testes pelo serviço Docker Compose. O container do runtime não exige PHP nem Composer no host; o host precisa de Docker Engine e Docker Compose.
- Persistir `backend/database/database.sqlite` com volume/mount gerenciado pelo Compose para que reinícios do container não descartem dados locais.
- Usar identificador inteiro crescente para produtos e chave estrangeira para associar cada registro de atividade. Produtos não terão exclusão física pela API; a chave estrangeira deve impedir que uma exclusão física acidental apague o histórico.
- Usar `ProductService` para operações de produto, filtros e paginação; ele mantém controllers livres de consultas ao banco e executa cada mutação com sua atividade em uma única transação. Falha ao registrar o evento cancela também a mutação.
- O histórico registra somente criação, atualização e exclusão lógica efetuadas pela API. Leituras, listagens e seed não produzem registros. Não haverá rota nem representação de histórico nas respostas.
- Adicionar uma chave interna de seed única e anulável a produtos para identificar os 100 registros de demonstração. Ela não será aceita na entrada nem incluída em `ProductResource`. O seeder usará chaves determinísticas para identificar os novos registros após reinicializar o catálogo.
- Usar o `LIKE` nativo de SQLite para busca parcial de texto; a comparação sem distinção de caixa vale para ASCII, enquanto letras acentuadas permanecem significativas. Busca Unicode sem distinção de caixa/acentos exigiria normalização adicional ou SQLite com ICU, fora do escopo sem requisito explícito.
- Executar o seeder com uma guarda explícita de produção antes de qualquer operação de banco. Em ambientes não produtivos, usar uma única transação e a mesma conexão SQLite para apagar primeiro todas as linhas de `product_activities`, depois todas as linhas de `products`, e em seguida criar os 100 novos exemplos. Propagar qualquer erro para que a transação reverta tanto a limpeza quanto a recriação. Usar exclusões explícitas via Query Builder para evitar eventos de modelos/observadores durante o reset; o seed novo não cria atividades.
- A ordenação da listagem será por identificador crescente para tornar a navegação entre páginas determinística.
- `ProductResource` expõe o preço como texto decimal com duas casas para preservar a precisão do cast decimal do Eloquent; a entrada aceita o texto decimal descrito no contrato e a aplicação valida a escala.
- A data de criação das atividades usa o relógio da aplicação e é serializada como data/hora ISO 8601 no armazenamento/contrato definido pelo framework.
- O MVP conforme à especificação inclui o CRUD e o histórico transacional das mutações bem-sucedidas; a US1 isolada, sem auditoria, é apenas uma etapa parcial e não satisfaz todos os requisitos.
- O seeder é intencionalmente destrutivo em ambientes não produtivos: em uma única transação, apaga todas as atividades e todos os produtos existentes e cria um novo conjunto de 100 exemplos; qualquer falha reverte a transação. Migrations alteram o esquema e não limpam os dados por si mesmas. O projeto é didático e não deve ser usado em produção sem a revisão prevista na constituição.
- O seeder fornece pelo menos 10 nomes, 5 categorias, 10 preços e 10 quantidades distintos entre os 100 produtos; os testes verificam esses limites.

## Risks and Follow-up

- O ambiente atual não tem PHP, Composer nem SQLite CLI, e não existe `composer.json`; instalação e execução de testes ficam pendentes até preparar o toolchain e criar o scaffold Laravel.
- Docker Engine e Docker Compose também precisam estar disponíveis no host para construir e executar o projeto; não estão instalados/verificados neste ambiente.
- O template não define formato detalhado de erros; o plano adota o envelope JSON de validação do Laravel e mensagens em português, documentados no contrato.
- O histórico não identifica ator, pois autenticação foi explicitamente mantida fora do escopo.

## Post-design Constitution Check

Todos os gates da seção inicial continuam **PASS** após definir o modelo, o contrato e a estrutura. As mutações e seus registros internos ficam na mesma transação; leituras e novos registros de seed não produzem atividades; respostas 204 não têm corpo ou API Resource. A limpeza destrutiva do seeder é um requisito didático explícito, limitada ao seeder, transacional e proibida em produção; migrations não removem dados. Nenhuma dependência adicional ou exceção constitucional foi introduzida.

## Complexity Tracking

Não há violações da constituição nem abstrações adicionais sem justificativa.
