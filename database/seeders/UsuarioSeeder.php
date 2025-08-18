<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Empresa;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barbearia = Empresa::where('slug', 'barbearia-do-joao')->first();
        $salao = Empresa::where('slug', 'salao-da-maria')->first();

        // Super Admin (sem empresa)
        Usuario::create([
            'empresa_id' => null,
            'nome' => 'Super Administrador',
            'telefone' => '+5511999999999',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('123456'),
            'tipo' => 'admin'
        ]);

        // Admin da Barbearia
        Usuario::create([
            'empresa_id' => $barbearia->id,
            'nome' => 'João Silva',
            'telefone' => '+5511888888888',
            'email' => 'joao@barbearia.com',
            'password' => Hash::make('123456'),
            'tipo' => 'admin'
        ]);

        // Admin do Salão
        Usuario::create([
            'empresa_id' => $salao->id,
            'nome' => 'Maria Santos',
            'telefone' => '+5511777777777',
            'email' => 'maria@salao.com',
            'password' => Hash::make('123456'),
            'tipo' => 'admin'
        ]);

        // Clientes de exemplo
        Usuario::create([
            'empresa_id' => null,
            'nome' => 'Carlos Oliveira',
            'telefone' => '+5511666666666',
            'email' => null,
            'password' => null,
            'tipo' => 'cliente'
        ]);

        Usuario::create([
            'empresa_id' => null,
            'nome' => 'Ana Costa',
            'telefone' => '+5511555555555',
            'email' => null,
            'password' => null,
            'tipo' => 'cliente'
        ]);
    }
}
