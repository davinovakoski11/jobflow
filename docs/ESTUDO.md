# Roteiro de estudo do JobFlow

O objetivo é conseguir explicar o percurso de uma ação e modificar o sistema com segurança.

## 1. Execute e explore

Crie uma oportunidade, mude a etapa e exporte a lista. Repita no modo conectado à API. Recarregue a página e observe a diferença entre os modos de armazenamento.

## 2. Siga uma candidatura pelo código

1. `ApplicationForm.vue` coleta e valida a entrada na interface.
2. `App.vue` recebe o evento de salvar.
3. `useApplications.ts` escolhe o armazenamento local ou a API.
4. `services/api.ts` envia a requisição e trata respostas de erro.
5. `StoreApplicationRequest.php` valida a entrada no servidor.
6. `ApplicationController.php` opera pela relação do usuário autenticado.
7. `ApplicationResource.php` define a resposta recebida pelo front-end.

## 3. Faça uma mudança sua

Adicione `next_interview_on`, uma data opcional para a próxima entrevista. Atualize o esquema, as regras, a resposta JSON, o tipo TypeScript e o formulário. Escreva um teste que aceite uma data válida e recuse um valor inválido.

Depois implemente um indicador de entrevistas nos próximos sete dias, com testes para datas-limite e ausência de entrevistas. Essa evolução é mais valiosa que adicionar números de commits sem uma mudança real.

## 4. Pratique a apresentação

- Qual problema o projeto resolve?
- Por que há validação no front-end e no back-end?
- Como o usuário A é impedido de alterar a candidatura de B?
- Por que o token é mantido em memória?
- O que acontece quando a API ou o armazenamento local falha?
- O que precisaria mudar para receber muitos usuários?

Descreva com clareza o uso de IA e as partes que você revisou, entendeu e evoluiu. Use o histórico do Git para registrar essas mudanças conforme forem acontecendo.
