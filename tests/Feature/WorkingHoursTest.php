<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\JornadaTrabalho;

class WorkingHoursTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_admin_can_view_working_hours_form()
    {
        // 1. Criar um admin de empresa
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'tipo' => 'admin',
            'empresa_id' => $empresa->id,
        ]);

        // 2. Autenticar o admin e acessar o formulário de carga horária
        $response = $this->actingAs($admin)->get(route('admin.working_hours.form'));

        // 3. Verificar se a página foi carregada com sucesso
        $response->assertStatus(200);
        $response->assertSee('Definir Carga Horária Semanal');
        $response->assertSee('Segunda-feira');
        $response->assertSee('Terça-feira');
        $response->assertSee('Quarta-feira');
        $response->assertSee('Quinta-feira');
        $response->assertSee('Sexta-feira');
        $response->assertSee('Sábado');
        $response->assertSee('Domingo');
    }

    public function test_admin_can_save_working_hours()
    {
        // 1. Criar um admin de empresa
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'tipo' => 'admin',
            'empresa_id' => $empresa->id,
        ]);

        // 2. Dados de carga horária para salvar
        $jornadasData = [
            1 => ['dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'], // Segunda
            2 => ['dia_semana' => 2, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'], // Terça
            3 => ['dia_semana' => 3, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'], // Quarta
            4 => ['dia_semana' => 4, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'], // Quinta
            5 => ['dia_semana' => 5, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'], // Sexta
            6 => ['dia_semana' => 6, 'hora_inicio' => '', 'hora_fim' => ''], // Sábado (vazio)
            0 => ['dia_semana' => 0, 'hora_inicio' => '', 'hora_fim' => ''], // Domingo (vazio)
        ];

        // 3. Autenticar o admin e salvar a carga horária
        $response = $this->actingAs($admin)->post(route('admin.working_hours.save'), [
            'jornadas' => $jornadasData
        ]);

        // 4. Verificar se foi redirecionado com sucesso
        $response->assertRedirect(route('admin.working_hours.form'));
        $response->assertSessionHas('success', 'Carga horária atualizada com sucesso!');

        // 5. Verificar se as jornadas foram salvas no banco de dados
        $this->assertDatabaseHas('jornadas_trabalho', [
            'empresa_id' => $empresa->id,
            'dia_semana' => 1,
            'hora_inicio' => '08:00',
            'hora_fim' => '18:00',
        ]);

        $this->assertDatabaseHas('jornadas_trabalho', [
            'empresa_id' => $empresa->id,
            'dia_semana' => 5,
            'hora_inicio' => '08:00',
            'hora_fim' => '18:00',
        ]);

        // 6. Verificar que os dias vazios não foram salvos
        $this->assertDatabaseMissing('jornadas_trabalho', [
            'empresa_id' => $empresa->id,
            'dia_semana' => 6,
        ]);

        $this->assertDatabaseMissing('jornadas_trabalho', [
            'empresa_id' => $empresa->id,
            'dia_semana' => 0,
        ]);
    }

    public function test_admin_can_update_existing_working_hours()
    {
        // 1. Criar um admin de empresa
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'tipo' => 'admin',
            'empresa_id' => $empresa->id,
        ]);

        // 2. Criar uma jornada existente
        JornadaTrabalho::create([
            'empresa_id' => $empresa->id,
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fim' => '17:00',
        ]);

        // 3. Dados atualizados
        $jornadasData = [
            1 => ['dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'], // Atualizar
        ];

        // 4. Autenticar o admin e atualizar a carga horária
        $response = $this->actingAs($admin)->post(route('admin.working_hours.save'), [
            'jornadas' => $jornadasData
        ]);

        // 5. Verificar se foi redirecionado com sucesso
        $response->assertRedirect(route('admin.working_hours.form'));

        // 6. Verificar se a jornada foi atualizada
        $this->assertDatabaseHas('jornadas_trabalho', [
            'empresa_id' => $empresa->id,
            'dia_semana' => 1,
            'hora_inicio' => '08:00',
            'hora_fim' => '18:00',
        ]);

        // 7. Verificar que não há duplicatas
        $this->assertEquals(1, JornadaTrabalho::where('empresa_id', $empresa->id)->where('dia_semana', 1)->count());
    }

    public function test_admin_can_delete_working_hours_by_leaving_empty()
    {
        // 1. Criar um admin de empresa
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'tipo' => 'admin',
            'empresa_id' => $empresa->id,
        ]);

        // 2. Criar uma jornada existente
        JornadaTrabalho::create([
            'empresa_id' => $empresa->id,
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fim' => '17:00',
        ]);

        // 3. Dados com campos vazios para deletar
        $jornadasData = [
            1 => ['dia_semana' => 1, 'hora_inicio' => '', 'hora_fim' => ''], // Deletar
        ];

        // 4. Autenticar o admin e "deletar" a carga horária
        $response = $this->actingAs($admin)->post(route('admin.working_hours.save'), [
            'jornadas' => $jornadasData
        ]);

        // 5. Verificar se foi redirecionado com sucesso
        $response->assertRedirect(route('admin.working_hours.form'));

        // 6. Verificar se a jornada foi deletada
        $this->assertDatabaseMissing('jornadas_trabalho', [
            'empresa_id' => $empresa->id,
            'dia_semana' => 1,
        ]);
    }

    public function test_validation_fails_with_invalid_time_format()
    {
        // 1. Criar um admin de empresa
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'tipo' => 'admin',
            'empresa_id' => $empresa->id,
        ]);

        // 2. Dados com formato de hora inválido
        $jornadasData = [
            1 => ['dia_semana' => 1, 'hora_inicio' => 'invalid', 'hora_fim' => '18:00'],
        ];

        // 3. Autenticar o admin e tentar salvar dados inválidos
        $response = $this->actingAs($admin)->post(route('admin.working_hours.save'), [
            'jornadas' => $jornadasData
        ]);

        // 4. Verificar se houve erro de validação
        $response->assertSessionHasErrors();
    }

    public function test_validation_fails_when_end_time_is_before_start_time()
    {
        // 1. Criar um admin de empresa
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'tipo' => 'admin',
            'empresa_id' => $empresa->id,
        ]);

        // 2. Dados com hora fim antes da hora início
        $jornadasData = [
            1 => ['dia_semana' => 1, 'hora_inicio' => '18:00', 'hora_fim' => '08:00'],
        ];

        // 3. Autenticar o admin e tentar salvar dados inválidos
        $response = $this->actingAs($admin)->post(route('admin.working_hours.save'), [
            'jornadas' => $jornadasData
        ]);

        // 4. Verificar se houve erro de validação
        $response->assertSessionHasErrors();
    }
}

