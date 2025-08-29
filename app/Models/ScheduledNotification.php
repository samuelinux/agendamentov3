<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledNotification extends Model
{
    use HasFactory;

    protected $table = 'scheduled_notifications';

    protected $fillable = [
        'empresa_id',
        'agendamento_id',
        'type',
        'to_msisdn',
        'send_at',
        'status',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'send_at' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function agendamento()
    {
        return $this->belongsTo(Agendamento::class);
    }
}


