# Constituição da API de Cadastro de Produtos

Versão: 2.0.0
Data de aprovação: 2026-10-20
Última alteração: 2026-10-22


## Stack

- O backend DEVE utilizar PHP e Laravel 13.

- As versões DEVEM respeitar as restrições e os arquivos de lock existentes no repositório.

- O projeto DEVE ser uma API HTTP com respostas em JSON,
  sem renderização de views.

- Listagens DEVEM utilizar coleções de API Resources.
  Listagens potencialmente grandes DEVEM ser paginadas.

- Models e coleções Eloquent NÃO DEVEM ser retornados
  diretamente pelos controllers.

- Relacionamentos utilizados pelos Resources DEVEM
  ser carregados explicitamente quando necessários,
  evitando consultas adicionais durante a transformação.

- Respostas de erro DEVEM seguir um formato JSON  consistente e utilizar os códigos de status HTTP apropriados.

- Respostas sem conteúdo, como HTTP 204, NÃO DEVEM
  incluir corpo ou API Resource.

- Respostas HTTP devem respeitar os devidos verbos a serem retornados

- Alterações na estrutura do banco DEVEM ser realizadas
  por migrations.

- O banco de dados DEVE ser SQLite.

- O projeto é uma API didática para cadastro e gerenciamento de produtos. A aplicação NÃO DEVE ser usada em produção sem uma revisão explícita de segurança, operação e privacidade.

## Arquitetura
- As rotas da API DEVEM ser definidas em routes/api.php.

- Controllers DEVEM coordenar o fluxo da requisição,
  delegando validação, regras de negócio e transformação
  de dados aos componentes responsáveis.

- A validação de dados de entrada DEVE ser implementada
  em classes Form Request, evitando validações diretamente
  nos controllers.

- A autorização DEVE ser aplicada no backend por Policies
  ou Gates, podendo ser acionada pelo método authorize()
  dos Form Requests.

- Regras de negócio que envolvam múltiplas etapas ou
  reutilização DEVEM ser implementadas em Services ou Actions.
  Operações simples NÃO DEVEM exigir camadas adicionais
  sem necessidade.

- O acesso ao banco DEVE utilizar Eloquent ou Query Builder usando camada de Repository salvo necessidade técnica documentada.

- Respostas que representem recursos de negócio DEVEM
  utilizar API Resources para definir explicitamente
  os campos retornados.

- Camadas e abstrações adicionais DEVEM resolver uma necessidade identificada; não devem ser criadas apenas por antecipação.

- Alterações de estrutura do banco DEVEM ser feitas por migrations.

## Qualidade
- Novas regras de negócio e alterações de comportamento DEVEM ter
  testes automatizados proporcionais ao risco.
- Correções de bugs DEVEM incluir teste de regressão quando
  o comportamento puder ser reproduzido automaticamente.
- Os testes relacionados à alteração DEVEM passar antes da entrega.
- Fluxos protegidos DEVEM testar acesso permitido e acesso negado.
- Credenciais e segredos NÃO DEVEM ser versionados ou registrados
  em logs.
- Entradas externas DEVEM ser validadas antes de seu uso.
- Alterações incompatíveis em APIs ou dados DEVEM incluir
  estratégia documentada de migração.
- A entrega DEVE atender aos critérios de aceitação da especificação.

## Convenções
--
- Classes, métodos, variáveis e identificadores de banco DEVEM utilizar nomes em inglês.
- Textos apresentados ao usuário DEVEM utilizar  português  do Brasil.
- A nomenclatura e a organização de arquivos DEVEM seguir
  as convenções do Laravel e os padrões existentes no projeto.
- O código PHP DEVE seguir a configuração de formatação
  versionada no repositório.
- Comentários DEVEM explicar decisões ou restrições relevantes,
  evitando repetir o que o código já expressa.
- Commits DEVEM descrever a finalidade da alteração.
- Documentação afetada por mudanças de comportamento DEVE
  ser atualizada na mesma entrega.

## Governança
- Especificações, planos e implementações DEVEM ser revisados quanto à conformidade com esta constituição.

- Conflitos entre uma solicitação e esta constituição DEVEM ser explicitados antes de implementar a parte conflitante.
- Exceções DEVEM registrar justificativa, impacto, escopo
  e aprovação do responsável técnico.
- Mudanças nesta constituição DEVEM ser versionadas no Git, com justificativa e aprovação do responsável técnico.
- O responsável técnico pelo projeto é Leonardo Mendes.
- A IA NÃO DEVE alterar ou flexibilizar estas regras por conta própria.
- Toda entrega DEVE informar o que foi alterado, o que foi validado e quais limitações permanecem.