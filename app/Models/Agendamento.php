<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Agendamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'servico_id',
        'data_hora_inicio',
        'data_hora_fim',
        'status'
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
    ];

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    // Scopes
    public function scopeConfirmados($query)
    {
        return $query->where('status', 'confirmado');
    }

    public function scopeCancelados($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function scopePorData($query, $data)
    {
        return $query->whereDate('data_hora_inicio', $data);
    }

    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    // Métodos auxiliares
    public static function buscarPorData($empresaId, $data)
    {
        return self::porEmpresa($empresaId)
            ->porData($data)
            ->confirmados()
            ->get();
    }
}
