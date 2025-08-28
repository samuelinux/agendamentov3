<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Empresa::create([
            'nome' => 'Barbearia do João',
            'slug' => 'barbearia-do-joao',
            'tamanho_slot_minutos' => 15,
            'antecedencia_minima_horas' => 0,
            'ativo' => true
        ]);

        Empresa::create([
            'nome' => 'Salão da Maria',
            'slug' => 'salao-da-maria',
            'tamanho_slot_minutos' => 30,
            'antecedencia_minima_horas' => 2,
            'ativo' => true
        ]);
    }
}
