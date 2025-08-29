<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JornadaTrabalho extends Model
{
    use HasFactory;

    protected $table = 'jornadas_trabalho';

    protected $fillable = [
        'empresa_id',
        'dia_semana',
        'hora_inicio',
        'hora_fim'
    ];

    protected $casts = [
        'dia_semana' => 'integer',
    ];

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // Métodos auxiliares
    public function getNomeDiaAttribute()
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado'
        ];

        return $dias[$this->dia_semana] ?? 'Desconhecido';
    }
}
