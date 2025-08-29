<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JornadaTrabalho;
use App\Models\Empresa;

class JornadaTrabalhoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barbearia = Empresa::where('slug', 'barbearia-do-joao')->first();
        $salao = Empresa::where('slug', 'salao-da-maria')->first();

        // Jornada da Barbearia (Segunda a Sexta)
        for ($dia = 1; $dia <= 5; $dia++) {
            // Manhã
            JornadaTrabalho::create([
                'empresa_id' => $barbearia->id,
                'dia_semana' => $dia,
                'hora_inicio' => '08:00',
                'hora_fim' => '12:00'
            ]);

            // Tarde
            JornadaTrabalho::create([
                'empresa_id' => $barbearia->id,
                'dia_semana' => $dia,
                'hora_inicio' => '14:00',
                'hora_fim' => '18:00'
            ]);
        }

        // Sábado da Barbearia (só manhã)
        JornadaTrabalho::create([
            'empresa_id' => $barbearia->id,
            'dia_semana' => 6,
            'hora_inicio' => '08:00',
            'hora_fim' => '12:00'
        ]);

        // Jornada do Salão (Terça a Sábado)
        for ($dia = 2; $dia <= 6; $dia++) {
            // Manhã
            JornadaTrabalho::create([
                'empresa_id' => $salao->id,
                'dia_semana' => $dia,
                'hora_inicio' => '09:00',
                'hora_fim' => '12:00'
            ]);

            // Tarde
            JornadaTrabalho::create([
                'empresa_id' => $salao->id,
                'dia_semana' => $dia,
                'hora_inicio' => '14:00',
                'hora_fim' => '19:00'
            ]);
        }
    }
}
