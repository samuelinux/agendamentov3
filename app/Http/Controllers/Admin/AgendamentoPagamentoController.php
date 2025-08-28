<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Servico;
use App\Models\Usuario;
use App\Models\Agendamento;
use App\Services\DisponibilidadeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AgendamentoPagamentoController extends Controller
{

    // app/Http/Controllers/Admin/AgendamentoPagamentoController.php

    public function store(Request $request, Agendamento $agendamento)
    {
        $data = $request->validate([
            'valor_pago' => ['required','numeric','min:0'],
        ]);

        $agendamento->valor_pago = $data['valor_pago'];
        $agendamento->status = Agendamento::STATUS_PAGO; // "pago" (string)
        $agendamento->save();

        return back()->with('success', 'Pagamento registrado com sucesso!');
    }

}
