<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApplicationApiTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge(['company' => 'Empresa Exemplo', 'role' => 'Desenvolvedor Vue', 'location' => 'Brasil', 'url' => 'https://example.com/vaga', 'salary_min' => 4200, 'status' => 'applied', 'notes' => 'Aguardar retorno.', 'applied_on' => '2026-10-24', 'remote' => true], $overrides);
    }

    public function test_returns_401_without_authentication(): void
    {
        $this->getJson('/api/v1/applications')->assertUnauthorized();
        $this->postJson('/api/v1/applications', $this->payload())->assertUnauthorized();
    }

    public function test_creates_and_lists_only_the_authenticated_users_applications(): void
    {
        $user = User::factory()->create();
        JobApplication::factory()->create(['company' => 'Outra conta']);
        Sanctum::actingAs($user);
        $this->postJson('/api/v1/applications', $this->payload(['user_id' => 999]))->assertCreated()->assertJsonPath('data.company', 'Empresa Exemplo')->assertJsonPath('data.remote', true);
        $this->assertDatabaseHas('job_applications', ['user_id' => $user->id, 'company' => 'Empresa Exemplo', 'salary_min' => 4200]);
        $this->getJson('/api/v1/applications')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.company', 'Empresa Exemplo');
    }

    public function test_returns_404_for_foreign_read_update_and_delete_without_modifying_data(): void
    {
        $foreign = JobApplication::factory()->create(['company' => 'Empresa original']);
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/v1/applications/'.$foreign->id)->assertNotFound();
        $this->putJson('/api/v1/applications/'.$foreign->id, $this->payload())->assertNotFound();
        $this->deleteJson('/api/v1/applications/'.$foreign->id)->assertNotFound();
        $this->assertDatabaseHas('job_applications', ['id' => $foreign->id, 'company' => 'Empresa original']);
    }

    public function test_updates_the_stage_and_deletes_an_owned_record(): void
    {
        $user = User::factory()->create();
        $record = JobApplication::factory()->for($user)->create();
        Sanctum::actingAs($user);
        $this->putJson('/api/v1/applications/'.$record->id, $this->payload(['status' => 'interview']))->assertOk()->assertJsonPath('data.status', 'interview');
        $this->assertDatabaseHas('job_applications', ['id' => $record->id, 'status' => 'interview']);
        $this->deleteJson('/api/v1/applications/'.$record->id)->assertNoContent();
        $this->assertDatabaseMissing('job_applications', ['id' => $record->id]);
    }

    public function test_required_fields_return_422_and_nothing_is_created(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/v1/applications', [])->assertUnprocessable()->assertJsonValidationErrors(['company', 'role', 'status', 'applied_on', 'remote']);
        $this->assertDatabaseCount('job_applications', 0);
    }

    public static function invalidValues(): array
    {
        return [
            'unknown stage' => ['status', 'hired'],
            'negative salary' => ['salary_min', -10],
            'unsafe URL scheme' => ['url', 'javascript:alert(1)'],
            'invalid date' => ['applied_on', '2026-02-30'],
            'oversized company' => ['company', str_repeat('a', 121)],
            'not a boolean' => ['remote', 'maybe'],
        ];
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_input_returns_422(string $field, mixed $value): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/v1/applications', $this->payload([$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_cors_allows_the_configured_frontend_origin(): void
    {
        $this->options('/api/v1/applications', [], ['Origin' => 'http://localhost:5173', 'Access-Control-Request-Method' => 'GET'])->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }
}
