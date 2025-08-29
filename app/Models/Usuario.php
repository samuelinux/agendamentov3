<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'empresa_id',
        'nome',
        'telefone',
        'email',
        'password',
        'tipo'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'usuario_id');
    }

    // Scopes
    public function scopeClientes($query)
    {
        return $query->where('tipo', 'cliente');
    }

    public function scopeAdmins($query)
    {
        return $query->where('tipo', 'admin');
    }
}
