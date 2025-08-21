<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Empresa;
use App\Models\Servico;
use App\Models\Usuario;

class AgendamentosAtivosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            // Criar um usuário dummy para a empresa se não existir
            $usuario = Usuario::firstOrCreate(
                [
                    'telefone' => '11888888888',
                ],
                [
                    'nome' => 'Cliente Teste Ativo ' . $empresa->nome,
                    'email' => 'cliente_ativo_' . $empresa->id . '@teste.com',
                    'tipo' => 'cliente',
                ]
            );

            $servicos = Servico::where('empresa_id', $empresa->id)->get();

            if ($servicos->isEmpty()) {
                continue; // Pular se não houver serviços para a empresa
            }

            // Criar 3 agendamentos para hoje com status 'realizado'
            for ($i = 0; $i < 3; $i++) {
                $servico = $servicos->random();
                $dataHoraInicio = Carbon::today()->subHours(rand(1, 5))->subMinutes(rand(0, 59)); // Horário no passado de hoje

                DB::table('agendamentos')->insert([
                    'empresa_id' => $empresa->id,
                    'servico_id' => $servico->id,
                    'usuario_id' => $usuario->id,
                    'data_hora_inicio' => $dataHoraInicio,
                    'data_hora_fim' => $dataHoraInicio->copy()->addMinutes($servico->duracao),
                    'status' => 'realizado',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Criar 3 agendamentos para hoje com status 'agendado'
            for ($i = 0; $i < 3; $i++) {
                $servico = $servicos->random();
                $dataHoraInicio = Carbon::today()->addHours(rand(1, 5))->addMinutes(rand(0, 59)); // Horário no futuro de hoje

                DB::table('agendamentos')->insert([
                    'empresa_id' => $empresa->id,
                    'servico_id' => $servico->id,
                    'usuario_id' => $usuario->id,
                    'data_hora_inicio' => $dataHoraInicio,
                    'data_hora_fim' => $dataHoraInicio->copy()->addMinutes($servico->duracao),
                    'status' => 'agendado',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Manter alguns agendamentos futuros com status 'confirmado' para outros testes
            for ($i = 0; $i < 2; $i++) {
                $servico = $servicos->random();
                $dataHoraInicio = Carbon::now()->addDays(rand(1, 30))->addHours(rand(1, 23))->addMinutes(rand(0, 59));

                DB::table('agendamentos')->insert([
                    'empresa_id' => $empresa->id,
                    'servico_id' => $servico->id,
                    'usuario_id' => $usuario->id,
                    'data_hora_inicio' => $dataHoraInicio,
                    'data_hora_fim' => $dataHoraInicio->copy()->addMinutes($servico->duracao),
                    'status' => 'confirmado',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}


