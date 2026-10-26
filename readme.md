# API de Catálogo de Produtos

Projeto didático de uma API REST para cadastro e gerenciamento de produtos. Ele demonstra o desenvolvimento de uma aplicação usando **Spec-Driven Development (SDD)**: os requisitos e decisões são documentados antes da implementação, e servem de guia para construir e validar o software.

O projeto também pode ser usado como exemplo de portfólio para estudar Laravel, APIs REST, testes automatizados e execução de aplicações em containers Docker.

## O que foi feito

- Preparação do projeto com GitHub Spec Kit e definição da constituição, com princípios e regras para orientar o desenvolvimento.
- Criação da especificação dos requisitos, pesquisa técnica, plano de implementação, modelo de dados, contrato OpenAPI, guia rápido e tarefas.
- Implementação de uma API REST em Laravel 13, PHP 8.4 e SQLite, executada com Docker Compose.
- Criação das operações de produtos com `apiResource`:
  - `GET /api/products`: listar, paginar e filtrar produtos.
  - `POST /api/products`: cadastrar um produto.
  - `GET /api/products/{id}`: consultar um produto.
  - `PUT /api/products/{id}`: substituir os dados de um produto.
  - `PATCH /api/products/{id}`: atualizar parcialmente um produto.
  - `DELETE /api/products/{id}`: excluir logicamente um produto.
- Adição de validações, respostas JSON por API Resources, filtros combináveis e histórico interno das alterações.
- Criação de migrations, factories, seeder de demonstração, testes automatizados e script para verificar o container, o acesso HTTP e a persistência do SQLite.
- Criação de uma collection do Postman e documentação para executar e explorar a API.

O seeder cria 100 produtos de demonstração. **Em ambiente local, cada execução apaga os produtos e atividades existentes e os recria**; essa operação é transacional e o seeder não pode ser executado em produção.

## Etapas do desenvolvimento

1. Definir os princípios do projeto na constituição.
2. Especificar o problema, as funcionalidades e os critérios de aceitação.
3. Esclarecer requisitos e registrar decisões técnicas.
4. Planejar a arquitetura, os dados, o contrato da API e os cenários de uso.
5. Analisar a consistência dos documentos e gerar tarefas de implementação.
6. Implementar a API e o ambiente Docker seguindo as tarefas.
7. Validar o resultado com testes automatizados, verificações do container e testes dos endpoints.
8. Documentar como executar e testar o projeto.

Os documentos do processo estão em [`specs/001-product-crud/`](./specs/001-product-crud/), e as tarefas concluídas estão em [`specs/001-product-crud/tasks.md`](./specs/001-product-crud/tasks.md).

## Requisitos

- Git
- Docker Engine
- Docker Compose (plugin `docker compose`)

PHP e Composer não precisam estar instalados na máquina: são fornecidos pelo container.

## Baixar o projeto

Clone o repositório e entre na pasta da aplicação:

```sh
git clone https://github.com/leofelipemendes/spec-driven-01.git
cd spec-driven-01/backend
```

## Executar pela primeira vez

Os comandos abaixo criam a configuração local, preparam o SQLite, constroem a imagem, geram a chave da aplicação e iniciam a API com o banco e os produtos de demonstração:

```sh
cp .env.example .env
touch database/database.sqlite
docker compose build
docker compose run --rm api php artisan key:generate
docker compose up -d
docker compose exec api php artisan migrate --seed
```

A API ficará disponível em `http://localhost:8000/api`. Para publicar em outra porta, defina `APP_PORT` no ambiente antes de iniciar o Compose.

Confirme que a aplicação está respondendo:

```sh
curl -i "http://localhost:8000/api/products?per_page=5"
```

## Testar

Execute os testes automatizados dentro do container:

```sh
docker compose exec api php artisan test
```

Para verificar a configuração do Compose, a disponibilidade HTTP e a persistência do banco após reiniciar o serviço:

```sh
./scripts/verify-container.sh
```

O script cria e remove um produto de teste. O SQLite persistente fica em `backend/database/database.sqlite`.

Para testar os endpoints manualmente, importe a collection [`backend/postman/Product Catalog API.postman_collection.json`](./backend/postman/Product%20Catalog%20API.postman_collection.json) no Postman. Cadastre um produto antes de consultar, atualizar ou excluir por ID; a collection armazena automaticamente o ID retornado.

## Reiniciar os dados de demonstração

Para apagar e recriar os produtos e o histórico, execute:

```sh
docker compose exec api php artisan db:seed --class=ProductSeeder
```

> **Atenção:** esse seeder remove permanentemente todos os produtos e atividades existentes antes de criar os 100 produtos de demonstração. Use apenas no ambiente local deste projeto didático.

Se também precisar recriar o esquema do banco desde o início, use:

```sh
docker compose exec api php artisan migrate:fresh --seed
```

> **Atenção:** `migrate:fresh` apaga todas as tabelas e os dados do banco antes de executar as migrations e o seeder. Não use para preservar dados.

## Encerrar a aplicação

Na pasta `backend/`, pare os containers com:

```sh
docker compose down
```

Esse comando não apaga o arquivo SQLite. Para mais detalhes sobre a API e os comandos de desenvolvimento, consulte [`backend/README.md`](./backend/README.md).
