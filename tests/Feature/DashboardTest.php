<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Servico;
use App\Models\Agendamento;
use Carbon\Carbon;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_and_future_value_is_calculated_correctly()
    {
        // 1. Criar um admin de empresa
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'tipo' => 'admin',
            'empresa_id' => $empresa->id,
        ]);

        // 2. Criar serviços para a empresa
        $servico1 = Servico::factory()->create([
            'empresa_id' => $empresa->id,
            'valor' => 50.00,
        ]);
        $servico2 = Servico::factory()->create([
            'empresa_id' => $empresa->id,
            'valor' => 75.00,
        ]);

        // 3. Criar agendamentos para hoje com diferentes status
        $usuario = \App\Models\Usuario::factory()->create([
            'empresa_id' => $empresa->id,
        ]);

        // Agendamento 'agendado'
        Agendamento::factory()->create([
            'empresa_id' => $empresa->id,
            'servico_id' => $servico1->id,
            'usuario_id' => $usuario->id,
            'data_hora_inicio' => Carbon::today()->addHours(9),
            'status' => 'agendado',
        ]);

        // Agendamento 'confirmado'
        Agendamento::factory()->create([
            'empresa_id' => $empresa->id,
            'servico_id' => $servico2->id,
            'usuario_id' => $usuario->id,
            'data_hora_inicio' => Carbon::today()->addHours(10),
            'status' => 'confirmado',
        ]);

        // Agendamento 'realizado'
        Agendamento::factory()->create([
            'empresa_id' => $empresa->id,
            'servico_id' => $servico1->id,
            'usuario_id' => $usuario->id,
            'data_hora_inicio' => Carbon::today()->addHours(11),
            'status' => 'realizado',
        ]);

        // Agendamento 'cancelado' (não deve ser incluído no valor futuro)
        Agendamento::factory()->create([
            'empresa_id' => $empresa->id,
            'servico_id' => $servico2->id,
            'usuario_id' => $usuario->id,
            'data_hora_inicio' => Carbon::today()->addHours(12),
            'status' => 'cancelado',
        ]);

        // Agendamento para outro dia (não deve ser incluído no valor futuro)
        Agendamento::factory()->create([
            'empresa_id' => $empresa->id,
            'servico_id' => $servico1->id,
            'usuario_id' => $usuario->id,
            'data_hora_inicio' => Carbon::tomorrow()->addHours(9),
            'status' => 'agendado',
        ]);

        // 4. Autenticar o admin e acessar o dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        // 5. Verificar se a página foi carregada com sucesso
        $response->assertStatus(200);

        // 6. Calcular o valor futuro esperado manualmente
        $expectedValorFuturo = $servico1->valor + $servico2->valor + $servico1->valor;

        // 7. Verificar se o valor futuro na view está correto
        $response->assertSee('Valor Futuro Hoje');
        $response->assertSee('R$ ' . number_format($expectedValorFuturo, 2, ',', '.'));
    }

    public function test_admin_can_view_dashboard_with_no_future_value()
    {
        // 1. Criar um admin de empresa
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'tipo' => 'admin',
            'empresa_id' => $empresa->id,
        ]);

        // 2. Autenticar o admin e acessar o dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        // 3. Verificar se a página foi carregada com sucesso
        $response->assertStatus(200);

        // 4. Verificar se o valor futuro na view é R$ 0,00
        $response->assertSee('Valor Futuro Hoje');
        $response->assertSee('R$ 0,00');
    }

    public function test_admin_can_view_dashboard_with_no_services_or_appointments()
    {
        // 1. Criar um admin de empresa
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'tipo' => 'admin',
            'empresa_id' => $empresa->id,
        ]);

        // 2. Autenticar o admin e acessar o dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        // 3. Verificar se a página foi carregada com sucesso
        $response->assertStatus(200);

        // 4. Verificar se o valor futuro na view é R$ 0,00
        $response->assertSee('Valor Futuro Hoje');
        $response->assertSee('R$ 0,00');
    }
}


