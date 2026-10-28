# JobFlow API

API Laravel 13 / PHP 8.3+; o front-end Vue e Quasar fica em ../frontend.

- Leia ../README.md para os contratos e os limites atuais.
- Todas as operações em candidaturas devem usar a relação do usuário autenticado. Nunca aceite user_id do cliente como proprietário.
- Use FormRequest para validação e ApplicationResource para a resposta JSON.
- Ao mudar o contrato da API, atualize os tipos do front-end e os testes de comportamento.
- Crie migrations reproduzíveis. Não publique .env, tokens, senhas reais ou bancos locais.
- Os dados do seeder são fictícios e exclusivos para local/testing.
- Execute php artisan test --compact e vendor/bin/pint ao alterar PHP.
- A alternativa com Docker está documentada no README principal.
