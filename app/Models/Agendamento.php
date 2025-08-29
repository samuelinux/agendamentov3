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
        'status',
        'observacoes',
        'valor_pago', // novo
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim'    => 'datetime',
        'valor_pago'       => 'decimal:2', // novo
        'status'           => 'string',
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

    public function getStatusAttribute($value)
{
    return strtolower(trim((string) $value));
}

public function getStatusBadgeAttribute(): string
{
    return match ($this->status) {
        self::STATUS_PAGO       => '<span class="badge badge-primary">Pago</span>',
        self::STATUS_REALIZADO  => '<span class="badge badge-success">Realizado</span>',
        self::STATUS_CANCELADO  => '<span class="badge badge-danger">Cancelado</span>',
        self::STATUS_CONFIRMADO => '<span class="badge badge-info">Confirmado</span>',
        self::STATUS_AGENDADO   => '<span class="badge badge-warning">Agendado</span>',
        default                 => '<span class="badge badge-secondary">'.ucfirst($this->status).'</span>',
    };
}


}
