<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_with_a_hashed_password_and_issues_a_token(): void
    {
        $response = $this->postJson('/api/v1/register', ['name' => 'Pessoa Teste', 'email' => 'TESTE@example.com', 'password' => 'senha-de-teste', 'password_confirmation' => 'senha-de-teste']);
        $response->assertCreated()->assertJsonPath('user.email', 'teste@example.com')->assertJsonStructure(['token', 'user' => ['name', 'email']])->assertJsonMissingPath('user.password');
        $this->assertTrue(Hash::check('senha-de-teste', User::firstOrFail()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_accepts_valid_credentials_and_token_reads_protected_data(): void
    {
        User::factory()->create(['email' => 'dev@example.com', 'password' => 'senha-de-teste']);
        $token = $this->postJson('/api/v1/login', ['email' => 'dev@example.com', 'password' => 'senha-de-teste'])->assertOk()->json('token');
        $this->withToken($token)->getJson('/api/v1/applications')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_invalid_credentials_return_401_without_creating_a_token(): void
    {
        User::factory()->create(['email' => 'dev@example.com', 'password' => 'senha-de-teste']);
        $this->postJson('/api/v1/login', ['email' => 'dev@example.com', 'password' => 'incorreta'])->assertUnauthorized()->assertJsonPath('message', 'E-mail ou senha inválidos.');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson('/api/v1/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_registration_returns_422_and_no_account(): void
    {
        $this->postJson('/api/v1/register', ['name' => '', 'email' => 'invalido', 'password' => '123', 'password_confirmation' => '456'])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 0);
    }
}
