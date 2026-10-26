# Research: Cadastro e gerenciamento de produtos

## Decisões

### Aplicação Laravel 13 em PHP 8.3–8.5

- **Decision**: Criar uma aplicação Laravel 13 em `backend/` e usar PHP 8.3–8.5, faixa listada como suportada atualmente.
- **Rationale**: A stack é obrigatória na constituição. A documentação oficial do Laravel 13 declara PHP 8.3 como versão mínima e lista as versões atualmente suportadas.
- **Alternatives considered**: .NET 10/Minimal API e outras opções aparecem como sugestões no material didático, mas não são compatíveis com a stack definida para este projeto.
- **Sources**: [Laravel 13 release notes](https://laravel.com/docs/13.x/releases); [Laravel 13 deployment requirements](https://laravel.com/docs/13.x/deployment#server-requirements); [PDO SQLite](https://www.php.net/manual/en/ref.pdo-sqlite.php); [project constitution](../../.specify/memory/constitution.md).

### Runtime e ferramentas em Docker

- **Decision**: Construir uma imagem Docker da aplicação com uma versão de PHP suportada pelo Laravel 13, Composer 2 e as extensões necessárias, incluindo PDO SQLite. Usar Docker Compose para iniciar a API e disponibilizar Artisan, Composer e PHPUnit no container `api`; persistir o arquivo SQLite por volume.
- **Rationale**: A pessoa desenvolvedora só precisa de Docker Engine/Compose no host, e a aplicação, migrations, seeders e testes usam um runtime reproduzível e comum. Composer precisa estar disponível dentro da imagem/serviço, não apenas numa etapa de build.
- **Alternatives considered**: Instalar PHP e Composer no host contraria o requisito de ambiente containerizado; serviços separados de web server ou banco de dados adicionariam complexidade sem necessidade para uma API didática com SQLite.
- **Sources**: [Docker Compose documentation](https://docs.docker.com/compose/); [Docker official PHP image](https://hub.docker.com/_/php); [Composer in Docker](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos).

### Persistência em SQLite com migrations e Eloquent

- **Decision**: Usar SQLite e migrations Laravel para tabelas de produtos e atividades; usar Eloquent sem Repository.
- **Rationale**: É a persistência exigida pela constituição. Migrations mantêm a estrutura reproduzível, e o uso direto do Eloquent satisfaz a necessidade sem adicionar uma camada de acesso genérica.
- **Alternatives considered**: Outro banco contradiz a constituição; Repository foi rejeitado por não haver necessidade técnica identificada.
- **Sources**: [Laravel database](https://laravel.com/docs/13.x/database); [Laravel migrations](https://laravel.com/docs/13.x/migrations).

### Recursos e paginação nativos para respostas da API

- **Decision**: Representar itens com `ProductResource`; usar a coleção paginada do recurso para incluir `data`, links e metadados de navegação. O tamanho padrão é 15, máximo 100, e os parâmetros dos filtros são preservados nos links.
- **Rationale**: O projeto exige API Resources e respostas paginadas; a paginação completa fornece contagem e páginas coerentes e evita serialização manual duplicada.
- **Alternatives considered**: Arrays de resposta montados nos controllers foram rejeitados porque contornam o contrato explícito de API Resource.
- **Sources**: [Laravel Eloquent API Resources and pagination](https://laravel.com/docs/13.x/eloquent-resources#pagination); [Laravel pagination](https://laravel.com/docs/13.x/pagination).

### Busca textual em SQLite

- **Decision**: Usar `LIKE` parametrizado para busca parcial por nome e categoria; sem distinção de caixa para caracteres ASCII e sensível a acentos.
- **Rationale**: O escopo didático não exige busca linguística Unicode, então a função nativa atende sem dependências ou campos de normalização adicionais.
- **Alternatives considered**: Colunas normalizadas ou SQLite compilado com ICU suportariam comparação Unicode mais ampla, mas ampliariam schema e manutenção sem requisito explícito.
- **Sources**: [SQLite expressions and LIKE](https://sqlite.org/lang_expr.html); [SQLite collations](https://sqlite.org/datatype3.html#collation).

### Representação interna de valores monetários

- **Decision**: Armazenar preço como decimal com duas casas, validar a escala decimal e expor a saída como string decimal canônica; não executar cálculos monetários neste recurso.
- **Rationale**: O contrato trabalha com valores de preço e igualdade exata. A saída como string preserva as duas casas do cast decimal do Eloquent sem conversão de resposta para ponto flutuante. A especificação não solicita operações aritméticas sobre preços.
- **Alternatives considered**: Armazenar centavos inteiros reduz riscos de ponto flutuante em cálculos, mas exigiria conversão e um novo formato interno sem benefício para as operações atuais de CRUD e igualdade.
- **Sources**: [SQLite datatypes and numeric affinity](https://sqlite.org/datatype3.html).

### Validação em Form Requests e filtros explícitos

- **Decision**: Usar Form Requests separados para listagem, criação e atualização. Validar os parâmetros de página, limites, texto e números; compor apenas filtros autorizados na consulta do Eloquent.
- **Rationale**: Segue a constituição, mantém validação fora dos controllers e previne que parâmetros arbitrários alterem a consulta.
- **Alternatives considered**: Validação inline no controller e interpolação livre de parâmetros de consulta foram rejeitadas.
- **Sources**: [Laravel validation](https://laravel.com/docs/13.x/validation#form-request-validation); [Laravel query builder](https://laravel.com/docs/13.x/queries).

### Exclusão lógica pelo recurso SoftDeletes

- **Decision**: Usar `SoftDeletes` e a coluna `deleted_at`; manter o escopo padrão excluindo registros removidos das consultas normais.
- **Rationale**: O mecanismo nativo corresponde ao comportamento requerido e evita exclusão física nos fluxos de CRUD.
- **Alternatives considered**: Um booleano de exclusão próprio foi rejeitado por duplicar o estado e o comportamento já fornecidos pelo framework.
- **Sources**: [Laravel Eloquent soft deleting](https://laravel.com/docs/13.x/eloquent#soft-deleting).

### Integridade referencial em SQLite

- **Decision**: Declarar chave estrangeira de atividade para produto sem cascata de exclusão e verificar a integridade referencial nos testes SQLite.
- **Rationale**: O produto é removido logicamente pela API e o histórico deve continuar associado. O SQLite pode operar com verificações de chaves estrangeiras desabilitadas por conexão, então o ambiente/testes precisam confirmar que elas estão habilitadas.
- **Alternatives considered**: `ON DELETE CASCADE` poderia apagar o histórico em uma exclusão física, contrariando sua retenção; omitir chave estrangeira reduz integridade.
- **Sources**: [SQLite foreign key support](https://sqlite.org/foreignkeys.html); [SQLite transactions](https://sqlite.org/lang_transaction.html).

### Histórico de alterações transacional e privado

- **Decision**: Persistir atividades em tabela própria e criar cada atividade na mesma transação que a mutação do produto; não registrar leituras nem seed e não expor endpoint de histórico.
- **Rationale**: A tabela satisfaz o requisito de persistir o histórico. A transação evita um produto alterado sem atividade correspondente, ou vice-versa. O escopo de privacidade foi confirmado na clarificação.
- **Alternatives considered**: Logs de texto da aplicação não oferecem associação referencial consultável; listeners assíncronos podem perder consistência entre mutação e atividade. Pacote de auditoria é desnecessário para os três tipos de evento exigidos.
- **Sources**: [Laravel database transactions](https://laravel.com/docs/13.x/database#database-transactions); [clarifications in spec](./spec.md#clarifications).

### Seeder de reset didático, transacional e bloqueado em produção

- **Decision**: O `ProductSeeder` verifica o ambiente de produção antes de qualquer operação de banco e lança erro explícito se estiver em `production`. Em ambiente não produtivo, executa numa única transação e numa única conexão: apaga todas as linhas de `product_activities`, depois todas as linhas de `products`, e insere 100 novos produtos com chaves determinísticas. Se qualquer operação falhar, a exceção sai do callback para provocar rollback integral. O seed usa Query Builder/gravação direta e não cria atividades para os novos registros.
- **Rationale**: O comportamento solicitado é resetar integralmente o catálogo e o histórico em cada execução, sem depender de chaves de seed para preservar registros anteriores. A ordem de exclusão respeita a chave estrangeira sem cascata do histórico. A transação garante que a base anterior seja restaurada se a criação do novo conjunto falhar. A guarda permanece como proteção explícita para impedir perda de dados em produção, embora o projeto seja didático.
- **Alternatives considered**: Upsert/idempotência preservando dados não-seed conflita com o requisito de recriar tudo. `migrate:fresh` é mais amplo, pois elimina todas as tabelas e reexecuta migrations; foi descartado porque o reset deve pertencer ao seeder e afetar apenas as tabelas `products` e `product_activities`. Excluir `products` antes das atividades viola a restrição de chave estrangeira sem cascata. Capturar e suprimir exceções dentro da transação impediria o rollback necessário.
- **Sources**: [Laravel seeding](https://laravel.com/docs/13.x/seeding#running-seeders); [Laravel environment detection](https://laravel.com/docs/13.x/configuration#determining-the-current-environment); [Laravel database transactions](https://laravel.com/docs/13.x/database#database-transactions); [Laravel foreign-key constraints](https://laravel.com/docs/13.x/migrations#foreign-key-constraints); [Laravel Eloquent mass deletes](https://laravel.com/docs/13.x/eloquent#deleting-models-using-queries); [SQLite foreign keys](https://www.sqlite.org/foreignkeys.html); [SQLite transactions](https://www.sqlite.org/lang_transaction.html).

### Testes com PHPUnit e SQLite

- **Decision**: Usar PHPUnit e os testes HTTP/de banco oferecidos pelo Laravel. Cobrir regras de serviço, contratos dos endpoints, exclusão lógica, consistência do histórico, filtros/paginação e proteção/reset/rollback do seeder destrutivo.
- **Rationale**: PHPUnit é o executor de testes padrão do scaffold Laravel e permite validar comportamento HTTP e persistência sobre a mesma pilha.
- **Alternatives considered**: Adicionar Pest ou dependências de integração externas foi rejeitado por não ser necessário ao escopo.
- **Sources**: [Laravel testing](https://laravel.com/docs/13.x/testing); [Laravel HTTP tests](https://laravel.com/docs/13.x/http-tests).

## Observações de ambiente

- A inspeção do repositório encontrou apenas arquivos de documentação e nenhum manifesto Composer ou código Laravel. `backend/` foi escolhido como diretório limpo de scaffold, sem misturar arquivos da aplicação às especificações na raiz.
- PHP, Composer e SQLite CLI não estão disponíveis na máquina desta sessão. A versão do framework e os testes deverão ser confirmados ao instalar o ambiente e gerar o scaffold.
- A documentação oficial Laravel 13 e referências oficiais SQLite/PHP foram consultadas para os requisitos de runtime e recursos nativos usados acima.
