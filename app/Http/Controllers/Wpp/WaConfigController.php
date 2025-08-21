<?php

namespace App\Http\Controllers\Wpp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WaConfig;

class WaConfigController extends Controller
{
    public function showForm()
    {
        $empresa = Auth::user()->empresa;
        $config = $empresa->waConfig;

        return view("admin.wpp.config", compact("config", "empresa"));
    }

    public function save(Request $request)
    {
        $empresa = Auth::user()->empresa;

        $request->validate([
            "phone_number_id" => "required|string|max:255",
            "waba_id" => "required|string|max:255",
            "token" => "required|string",
            "sender_display_name" => "required|string|max:255",
        ]);

        $config = WaConfig::updateOrCreate(
            ["empresa_id" => $empresa->id],
            [
                "phone_number_id" => $request->phone_number_id,
                "waba_id" => $request->waba_id,
                "token" => $request->token,
                "sender_display_name" => $request->sender_display_name,
            ]
        );

        return redirect()->route("admin.wpp.config.form")->with("success", "Configurações do WhatsApp salvas com sucesso!");
    }
}


