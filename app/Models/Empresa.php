<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'slug',
        'status',
        'telefone',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'tamanho_slot_minutos',
        'antecedencia_minima_horas',
        'status'
    ];

    protected $casts = [
         'status' => 'boolean',
    ];

    // Relacionamentos
    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }

    public function servicos()
    {
        return $this->hasMany(Servico::class);
    }

    public function jornadasTrabalho()
    {
        return $this->hasMany(JornadaTrabalho::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    public function excecoesAgenda()
    {
        return $this->hasMany(ExcecaoAgenda::class);
    }

    public function waConfig()
    {
        return $this->hasOne(WaConfig::class);
    }
}
