<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaMessage extends Model
{
    use HasFactory;

    protected $table = 'wa_messages';

    protected $fillable = [
        'empresa_id',
        'agendamento_id',
        'usuario_id',
        'to_msisdn',
        'type',
        'template_name',
        'payload',
        'provider_message_id',
        'status',
        'error',
        'attempts',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function agendamento()
    {
        return $this->belongsTo(Agendamento::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}


