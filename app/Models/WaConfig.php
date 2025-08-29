<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class WaConfig extends Model
{
    use HasFactory;

    protected $table = 'wa_configs';

    protected $fillable = [
        'empresa_id',
        'phone_number_id',
        'waba_id',
        'token',
        'sender_display_name',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'token' => 'encrypted',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }


}


