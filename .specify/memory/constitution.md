<!--
Sync Impact Report
Version change: 1.1.0 -> 2.0.0
Modified sections: Project title and scope; Stack and API contract now apply to a product registration API.
Added sections: Product catalog project scope.
Removed sections: Password-generator educational constraints and plaintext-password exception.
Follow-up TODOs: None.
-->

# Constituição da API de Cadastro de Produtos

## Princípios Fundamentais

### I. Stack e contrato da API
O backend DEVE utilizar PHP e Laravel 13, respeitando as restrições e os arquivos de lock do repositório. A aplicação DEVE ser uma API HTTP que retorna JSON, sem renderizar views. Rotas da API DEVEM ser definidas em `routes/api.php`. Respostas DEVEM usar os verbos e códigos de status HTTP apropriados. Erros DEVEM seguir um formato JSON consistente. Respostas sem conteúdo, como HTTP 204, NÃO DEVEM incluir corpo nem API Resource.

### II. Responsabilidades arquiteturais
Controllers DEVEM coordenar o fluxo da requisição, sem concentrar validação ou regras de negócio. A validação de entrada DEVE ser implementada em Form Requests. A autorização DEVE ser aplicada no backend por Policies ou Gates, podendo ser acionada pelo método `authorize()` dos Form Requests. Regras de negócio com múltiplas etapas ou reutilização DEVEM ficar em Services ou Actions; operações simples NÃO DEVEM exigir camadas adicionais sem necessidade. Controllers NÃO DEVEM acessar o banco diretamente: o acesso DEVE ocorrer por Eloquent ou Query Builder, usando Repository somente quando houver necessidade técnica documentada.

### III. Contratos explícitos e persistência
Respostas que representem recursos de negócio DEVEM utilizar API Resources para definir explicitamente os campos retornados. Models e coleções Eloquent NÃO DEVEM ser retornados diretamente pelos controllers. Listagens DEVEM utilizar coleções de API Resources e, quando potencialmente grandes, DEVEM ser paginadas. Relacionamentos usados pelos Resources DEVEM ser carregados explicitamente quando necessário, evitando consultas adicionais durante a transformação. Alterações na estrutura do banco DEVEM ser feitas por migrations.

### IV. Testes e qualidade
Cada regra de negócio DEVE ter teste unitário. Cada endpoint DEVE ter pelo menos um teste de integração do caminho feliz. Fluxos protegidos DEVEM testar acesso permitido e negado. Correções de bugs DEVEM incluir teste de regressão quando o comportamento puder ser reproduzido automaticamente. Testes relacionados à alteração DEVEM passar antes da entrega, e a entrega DEVE atender aos critérios de aceitação da especificação.

### V. Simplicidade, segurança e convenções
Camadas, abstrações e dependências adicionais DEVEM resolver uma necessidade identificada; novas dependências DEVEM ter justificativa registrada no plano. Entradas externas DEVEM ser validadas antes do uso. Credenciais e segredos NÃO DEVEM ser versionados nem registrados em logs. Classes, métodos, variáveis e identificadores de banco DEVEM usar nomes em inglês; textos apresentados ao usuário DEVEM usar português do Brasil. A nomenclatura, a organização dos arquivos e a formatação DEVEM seguir as convenções do Laravel e a configuração versionada do repositório. Comentários DEVEM explicar decisões ou restrições relevantes, sem repetir o que o código já expressa. Documentação afetada por mudanças de comportamento DEVE ser atualizada na mesma entrega.

## Restrições técnicas e de escopo

- O banco de dados DEVE ser SQLite.
- O projeto é uma API didática para cadastro e gerenciamento de produtos. A aplicação NÃO DEVE ser usada em produção sem uma revisão explícita de segurança, operação e privacidade.
- Versões de linguagem, framework e dependências DEVEM respeitar os arquivos de lock existentes. A stack definida nesta constituição prevalece sobre stacks apresentadas apenas como sugestões no material do desafio.
- A aplicação DEVE iniciar sem erros. A entrega DEVE passar pelos testes e verificações aplicáveis do projeto, sem erros ou avisos impeditivos.
- Alterações incompatíveis em APIs ou dados DEVEM incluir estratégia de migração documentada.

## Fluxo de desenvolvimento

- Especificação, plano, tarefas e implementação DEVEM ser revisados quanto à conformidade com esta constituição.
- O plano DEVE registrar decisões técnicas relevantes e justificativas para dependências novas.
- A definição de pronto inclui aplicação iniciando corretamente, testes passando e critérios de aceitação da especificação atendidos.
- Commits DEVEM descrever a finalidade da alteração.
- Toda entrega DEVE informar o que foi alterado, o que foi validado e quais limitações permanecem.

## Governança

Esta constituição rege as especificações, os planos e as implementações do projeto. Conflitos entre uma solicitação e estas regras DEVEM ser explicitados antes de implementar a parte conflitante. Exceções DEVEM registrar justificativa, impacto, escopo e aprovação do responsável técnico.

Alterações nesta constituição DEVEM ser versionadas no Git, seguir versionamento semântico e receber aprovação do responsável técnico. Mudanças incompatíveis nas regras exigem incremento MAJOR; novas regras ou expansões materiais exigem MINOR; esclarecimentos e correções semânticas exigem PATCH. O responsável técnico é Leonardo Mendes. A IA NÃO DEVE alterar ou flexibilizar estas regras por conta própria.

**Version**: 2.0.0 | **Ratified**: 2026-10-20 | **Last Amended**: 2026-10-22
