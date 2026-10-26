# Feature Specification: Cadastro e gerenciamento de produtos

**Feature Branch**: `001-product-crud`

**Created**: 2026-10-22

**Status**: Draft

**Input**: User description: "para esse projeto de ser criado uma api para cadastro de produto contendo um crud com softdelete. devemos ter cada end-point construindo usando api resourses nas rotas e vamos adicionar logs para todas as atividade executados em cada produto. Devemos ter a listagem paginada com filtros de busca por nome, preço, categoria, quantidade ou o conjunto de todos os filtros."

## Clarifications

### Session 2026-10-22

- Q: Quais operações devem gerar registros no histórico de cada produto? → A: Somente criação, atualização e exclusão lógica; consultas não geram registros.
- Q: Como o responsável pelo catálogo deve consultar o histórico de alterações de um produto? → A: O histórico é interno e não há endpoint de consulta nesta versão.
- Q: A execução do seeder deve criar registros de histórico para os 100 produtos de exemplo? → A: Não; o histórico registra alterações feitas após a carga inicial, não os dados de exemplo.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Gerenciar produtos (Priority: P1)

Como consumidor da API, quero cadastrar, consultar, atualizar e excluir produtos para manter o catálogo atualizado sem perder definitivamente os registros excluídos, com as mutações bem-sucedidas registradas no histórico interno.

**Why this priority**: O ciclo de gerenciamento é a capacidade principal e necessária para manter os dados do catálogo.

**Independent Test**: Cadastrar um produto, consultar seus dados, alterá-los e excluí-lo; verificar que criação, atualização e exclusão geram atividades correspondentes, que a exclusão o remove das consultas normais sem apagar permanentemente seus dados, que leituras não geram atividades e que a falha ao gravar uma atividade reverte a mutação.

**Acceptance Scenarios**:

1. **Given** dados válidos de um produto, **When** o consumidor o cadastra, **Then** o produto é criado e retornado com identificador e os dados cadastrados.
2. **Given** um produto ativo, **When** o consumidor o consulta pelo identificador, **Then** recebe os dados atuais do produto.
3. **Given** um produto ativo e novos dados válidos, **When** o consumidor o atualiza, **Then** a consulta posterior apresenta os dados atualizados.
4. **Given** um produto ativo, **When** o consumidor o exclui, **Then** ele deixa de aparecer nas consultas normais e sua exclusão fica registrada sem remoção permanente.
5. **Given** dados obrigatórios ausentes ou inválidos, **When** o consumidor tenta cadastrar ou atualizar o produto, **Then** recebe uma resposta de erro de validação e os dados existentes permanecem inalterados.
6. **Given** um identificador inexistente ou de produto excluído, **When** o consumidor tenta consultar, atualizar ou excluir o produto, **Then** recebe uma resposta de não encontrado.
7. **Given** uma criação, atualização ou exclusão lógica bem-sucedida, **When** a operação é concluída, **Then** uma atividade com o tipo e a data e hora é persistida junto com a mutação na mesma transação; se a atividade não puder ser persistida, a mutação também é revertida.
8. **Given** uma consulta, listagem, operação rejeitada ou carga pelo seeder, **When** a operação é executada, **Then** nenhuma atividade é registrada; atividades existentes permanecem associadas após exclusão lógica e não são expostas em respostas ou rotas públicas.

---

### User Story 2 - Consultar e filtrar o catálogo (Priority: P2)

Como consumidor da API, quero listar produtos em páginas e combinar filtros para encontrar os itens que atendem aos critérios desejados.

**Why this priority**: A consulta paginada permite localizar produtos sem exigir o retorno de todo o catálogo em uma única resposta.

**Independent Test**: Cadastrar vários produtos com valores distintos e solicitar páginas e combinações de filtros; conferir que cada resultado corresponde a todos os filtros aplicados.

**Acceptance Scenarios**:

1. **Given** mais produtos que cabem em uma página, **When** o consumidor solicita a listagem, **Then** recebe uma página limitada de produtos e informações para navegar pelas demais páginas.
2. **Given** produtos com nomes e categorias diferentes, **When** o consumidor filtra por nome ou categoria, **Then** recebe somente produtos que correspondem ao filtro.
3. **Given** produtos com preços e quantidades diferentes, **When** o consumidor filtra por preço ou quantidade, **Then** recebe somente produtos que correspondem ao valor informado.
4. **Given** filtros válidos de nome, preço, categoria e quantidade, **When** o consumidor combina dois ou mais filtros, **Then** recebe apenas produtos que correspondem simultaneamente a todos eles.
5. **Given** filtros sem correspondência, **When** o consumidor solicita a listagem, **Then** recebe uma página vazia e metadados de paginação válidos.
6. **Given** filtros ou parâmetros de paginação inválidos, **When** o consumidor solicita a listagem, **Then** recebe uma resposta de validação que identifica a entrada inválida.

---

### User Story 3 - Preparar catálogo de demonstração (Priority: P3)

Como responsável pelo projeto, quero preparar o ambiente com 100 produtos de exemplo para exercitar a listagem, os filtros e o gerenciamento do catálogo.

**Why this priority**: Dados variados permitem validar e demonstrar os fluxos do catálogo sem cadastro manual inicial.

**Independent Test**: Preparar o banco de dados do ambiente de desenvolvimento e conferir que o seeder disponibiliza exatamente 100 produtos de exemplo ativos, com pelo menos 10 nomes, 5 categorias, 10 preços e 10 quantidades distintos; criar produtos e atividades pela API, executar o seeder novamente e confirmar que todo o catálogo e histórico anteriores foram removidos e substituídos por exatamente 100 novos produtos sem atividades.

**Acceptance Scenarios**:

1. **Given** a estrutura do banco de dados criada e nenhum produto de exemplo, **When** o seeder é executado no ambiente de desenvolvimento, **Then** são disponibilizados exatamente 100 produtos ativos com pelo menos 10 nomes, 5 categorias, 10 preços e 10 quantidades distintos.
2. **Given** produtos de exemplo, produtos cadastrados pela API e atividades existentes, **When** o seeder é executado novamente em ambiente não produtivo, **Then** todos os produtos e atividades anteriores são removidos e substituídos por exatamente 100 novos produtos de exemplo ativos, sem atividades, dentro de uma única transação; se a recriação falhar, a limpeza é revertida.
3. **Given** um ambiente de produção, **When** o seeder é executado, **Then** produtos de demonstração não são inseridos.

---

### User Story 4 - Executar a API em container (Priority: P4)

Como pessoa desenvolvedora, quero construir e executar a API em um container Docker com o runtime e as ferramentas necessárias para iniciar a aplicação, instalar dependências e executar migrations, seeders e testes.

**Why this priority**: Um ambiente containerizado torna a execução do projeto reproduzível sem exigir que PHP e Composer estejam instalados diretamente na máquina hospedeira.

**Independent Test**: Construir a imagem, iniciar a API com Docker Compose, executar migrations, seeder e testes dentro do container e confirmar que uma chamada HTTP recebe resposta da API.

**Acceptance Scenarios**:

1. **Given** Docker Engine e Docker Compose instalados na máquina hospedeira, **When** a imagem do projeto é construída, **Then** ela contém PHP compatível com Laravel 13, Composer e as extensões necessárias para a aplicação e SQLite.
2. **Given** a imagem construída, **When** a aplicação é iniciada pelo Docker Compose, **Then** a API aceita requisições HTTP na porta configurada e responde de acordo com seu contrato.
3. **Given** a aplicação em execução no container, **When** migrations, seeder ou testes são executados por comandos do container, **Then** eles concluem usando o mesmo ambiente de runtime da API.
4. **Given** o container reiniciado, **When** a API volta a acessar o banco SQLite configurado, **Then** os dados persistidos anteriormente continuam disponíveis.

---

### Edge Cases

- Valores monetários negativos, quantidades negativas, campos obrigatórios vazios e formatos inválidos são rejeitados sem alterar o produto.
- Uma consulta ou alteração que use o identificador de um produto excluído deve tratá-lo como não encontrado nas operações normais.
- A exclusão repetida de um produto já excluído deve retornar não encontrado e não criar um segundo evento de exclusão.
- Uma página solicitada além da última página deve retornar uma coleção vazia com metadados de paginação coerentes.
- Valores de filtros sem correspondência devem resultar em coleção vazia, não em erro.
- O histórico de atividades deve continuar disponível para o produto após sua exclusão lógica.
- Consultas individuais e listagens não geram registros no histórico.
- O histórico de alterações é interno e não pode ser consultado por consumidores nesta versão.
- A carga dos 100 produtos de exemplo não gera registros no histórico.
- A execução do seeder é destrutiva: em ambiente não produtivo, remove todos os produtos e todas as atividades existentes antes de recriar os 100 produtos de exemplo; a operação é transacional e a falha reverte a limpeza.
- Migrations aplicam alterações de esquema e não devem, por si só, apagar os dados; a limpeza integral ocorre somente quando o seeder de produtos é executado.
- O seeder permanece bloqueado em produção, ainda que o projeto seja didático e não destinado a uso em produção.
- A execução local da API, migrations, seeders e testes deve ocorrer no ambiente Docker definido pelo projeto; Docker Engine e Docker Compose são dependências da máquina hospedeira.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: A API MUST permitir cadastrar um produto com nome, preço, categoria e quantidade.
- **FR-002**: A API MUST permitir consultar um produto ativo pelo identificador.
- **FR-003**: A API MUST permitir atualizar os dados de um produto ativo.
- **FR-004**: A API MUST permitir excluir logicamente um produto ativo, preservando seus dados e seu histórico.
- **FR-005**: Consultas normais MUST omitir produtos excluídos logicamente.
- **FR-006**: Operações de criação, atualização e exclusão lógica concluídas com sucesso MUST gerar um registro de atividade associado ao produto, contendo o tipo da alteração e sua data e hora.
- **FR-007**: Uma operação rejeitada ou sem sucesso MUST NOT gerar um registro que a represente como concluída.
- **FR-008**: Toda resposta da API que contenha corpo MUST usar uma representação JSON consistente dos dados do produto; a listagem MUST retornar uma coleção paginada com informações de navegação. Operações respondidas com HTTP 204 MUST NOT incluir corpo ou API Resource.
- **FR-009**: A listagem MUST aceitar filtros independentes por nome, preço, categoria e quantidade.
- **FR-010**: A listagem MUST permitir combinar filtros, aplicando todos simultaneamente.
- **FR-011**: A listagem MUST excluir produtos removidos logicamente, inclusive quando filtros forem aplicados.
- **FR-012**: A API MUST validar os dados de produto e os parâmetros de busca e paginação, informar entradas inválidas e preservar os dados existentes em caso de falha.
- **FR-013**: Tentativas de consultar, atualizar ou excluir produto inexistente ou excluído logicamente MUST retornar não encontrado.
- **FR-014**: O projeto MUST criar, por meio de migrations, a estrutura do banco de dados para persistir produtos com identificador, nome, preço, categoria, quantidade e estado de exclusão lógica, além de atividades associadas a cada produto com tipo e data e hora.
- **FR-015**: O projeto MUST disponibilizar um seeder que crie exatamente 100 produtos ativos de exemplo, com pelo menos 10 nomes, 5 categorias, 10 preços e 10 quantidades distintos.
- **FR-016**: Em ambiente não produtivo, cada execução do seeder MUST, em uma única transação, remover todos os produtos e todas as atividades existentes e recriar exatamente 100 produtos ativos de exemplo; se qualquer etapa falhar, MUST reverter a transação. O seeder MUST NOT executar em ambiente de produção.
- **FR-017**: Consultas individuais e listagens MUST NOT gerar registros no histórico de alterações.
- **FR-018**: Consumidores MUST NOT poder consultar os registros internos de alterações nesta versão.
- **FR-019**: A carga dos novos produtos de exemplo pelo seeder MUST NOT gerar registros no histórico de alterações; atividades de execuções anteriores são removidas junto com o catálogo.
- **FR-020**: O projeto MUST fornecer uma imagem Docker com PHP em versão suportada pelo Laravel 13, Composer 2 e as extensões necessárias para executar a API, instalar dependências Composer, usar SQLite e rodar migrations, seeders e testes.
- **FR-021**: O projeto MUST fornecer uma configuração Docker Compose que permita construir e iniciar a API dentro de um container, disponibilizar sua porta HTTP e preservar o arquivo de banco SQLite entre reinicializações do container.
- **FR-022**: O fluxo documentado de desenvolvimento MUST permitir executar comandos Artisan e testes dentro do container, sem exigir PHP ou Composer instalados na máquina hospedeira.

### Key Entities *(include if feature involves data)*

- **Produto**: Item do catálogo com identificador, nome, preço, categoria, quantidade e situação de ativo ou excluído logicamente.
- **Atividade do produto**: Registro associado a um produto que informa a ação concluída e a data e hora em que ocorreu.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Em testes de aceitação, 100% das operações válidas de cadastro, consulta individual, atualização e exclusão lógica produzem o estado esperado do produto.
- **SC-002**: Em testes de aceitação, 100% das alterações bem-sucedidas de criação, atualização e exclusão lógica feitas pela API aparecem no histórico do produto, nenhuma operação rejeitada aparece como concluída e consultas ou carga inicial de exemplo não adicionam registros.
- **SC-003**: Em testes de aceitação, todos os resultados de uma busca com múltiplos filtros correspondem a cada filtro aplicado, sem incluir produtos excluídos logicamente.
- **SC-004**: Em testes com mais itens que o limite de uma página, nenhuma página excede o limite configurado e todas as páginas permitem percorrer os resultados sem duplicar ou omitir produtos ativos.
- **SC-005**: Consumidores conseguem identificar em cada resposta paginada a página atual, a quantidade de resultados e como navegar entre páginas.
- **SC-006**: Em ambiente não produtivo, após cada execução bem-sucedida do seeder existem exatamente 100 produtos de exemplo ativos, com pelo menos 10 nomes, 5 categorias, 10 preços e 10 quantidades distintos, e nenhum outro produto ou atividade da execução anterior permanece.
- **SC-007**: Em ambiente de produção, a execução do seeder não insere produtos de exemplo.
- **SC-008**: Em uma máquina com Docker Engine e Docker Compose, uma pessoa consegue construir e iniciar a API sem instalar PHP ou Composer no host.
- **SC-009**: Usando apenas os comandos documentados do container, uma pessoa consegue executar migrations, popular os 100 produtos de exemplo, rodar os testes e acessar a API por HTTP.
- **SC-010**: Após reiniciar o container da API, os produtos e as atividades persistidos no SQLite permanecem disponíveis.

## Assumptions

- Um produto tem os campos nome, preço, categoria e quantidade, conforme os filtros solicitados.
- Nome e categoria são textos; preço aceita valor igual ou superior a zero com até duas casas decimais; quantidade é um número inteiro igual ou superior a zero.
- Filtros por nome e categoria procuram correspondência parcial sem distinção entre maiúsculas e minúsculas para caracteres ASCII; acentos permanecem significativos. Preço e quantidade usam correspondência exata. Quando combinados, os filtros são aplicados com lógica AND.
- A paginação usa um tamanho padrão de 15 itens e limita pedidos a no máximo 100 itens por página.
- A exclusão lógica não inclui restauração nem consulta de produtos excluídos por operações normais nesta versão.
- Os 100 produtos de exemplo são destinados somente a desenvolvimento e testes; cada execução do seeder reinicializa destrutivamente todo o catálogo e histórico em uma transação.
- O ambiente hospedeiro precisa ter Docker Engine e Docker Compose; PHP, Composer e extensões da aplicação são fornecidos pelo container.
- O histórico registra alterações bem-sucedidas de criação, atualização e exclusão lógica. Consultas individuais e listagens não são registradas.
- Os registros de alterações são mantidos internamente e não são incluídos nas respostas disponíveis aos consumidores nesta versão.
- A carga inicial do seeder é preparação de dados de exemplo, não uma alteração feita por consumidor da API, e não gera registros no histórico.
- Como não foi especificada autenticação, o histórico não identifica um usuário responsável; registra apenas a ação, o produto e a data e hora.
- Esta API de produtos substitui o domínio anterior de gerador de senhas neste projeto; a constituição foi atualizada para refletir o novo escopo.
- A especificação define resultados e comportamento observável; detalhes de rotas, formato exato dos campos JSON e mecanismos de armazenamento serão definidos no planejamento.
