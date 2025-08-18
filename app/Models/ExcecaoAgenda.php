<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExcecaoAgenda extends Model
{
    use HasFactory;

    protected $table = 'excecoes_agenda';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'descricao',
        'data_inicio',
        'data_fim',
        'data_hora_inicio_intervalo',
        'data_hora_fim_intervalo'
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'data_hora_inicio_intervalo' => 'datetime',
        'data_hora_fim_intervalo' => 'datetime',
    ];

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // Scopes
    public function scopeFerias($query)
    {
        return $query->where('tipo', 'FERIAS');
    }

    public function scopeFeriados($query)
    {
        return $query->where('tipo', 'FERIADO');
    }

    public function scopeSaidinhas($query)
    {
        return $query->where('tipo', 'SAIDINHA');
    }

    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    // Métodos auxiliares
    public static function buscarPorData($empresaId, $data)
    {
        return self::porEmpresa($empresaId)
            ->where(function ($query) use ($data) {
                // Férias e Feriados (data completa)
                $query->whereIn('tipo', ['FERIAS', 'FERIADO'])
                    ->where('data_inicio', '<=', $data)
                    ->where('data_fim', '>=', $data);
            })
            ->orWhere(function ($query) use ($data, $empresaId) {
                // Saidinhas (intervalo de tempo)
                $query->where('empresa_id', $empresaId)
                    ->where('tipo', 'SAIDINHA')
                    ->whereDate('data_hora_inicio_intervalo', $data);
            })
            ->get();
    }
}
