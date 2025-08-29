<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Servico;
use App\Models\Empresa;

class ServicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barbearia = Empresa::where('slug', 'barbearia-do-joao')->first();
        $salao = Empresa::where('slug', 'salao-da-maria')->first();

        // Serviços da Barbearia
        Servico::create([
            'empresa_id' => $barbearia->id,
            'nome' => 'Corte de Cabelo',
            'descricao' => 'Corte masculino tradicional',
            'duracao_minutos' => 30,
            'ativo' => true
        ]);

        Servico::create([
            'empresa_id' => $barbearia->id,
            'nome' => 'Barba',
            'descricao' => 'Aparar e modelar a barba',
            'duracao_minutos' => 20,
            'ativo' => true
        ]);

        Servico::create([
            'empresa_id' => $barbearia->id,
            'nome' => 'Corte + Barba',
            'descricao' => 'Pacote completo',
            'duracao_minutos' => 45,
            'ativo' => true
        ]);

        // Serviços do Salão
        Servico::create([
            'empresa_id' => $salao->id,
            'nome' => 'Corte Feminino',
            'descricao' => 'Corte e finalização',
            'duracao_minutos' => 60,
            'ativo' => true
        ]);

        Servico::create([
            'empresa_id' => $salao->id,
            'nome' => 'Escova',
            'descricao' => 'Lavagem e escova',
            'duracao_minutos' => 45,
            'ativo' => true
        ]);

        Servico::create([
            'empresa_id' => $salao->id,
            'nome' => 'Manicure',
            'descricao' => 'Cuidados com as unhas',
            'duracao_minutos' => 30,
            'ativo' => true
        ]);
    }
}
