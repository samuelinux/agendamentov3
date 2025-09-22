<?php

use App\Http\Controllers\Admin\AgendamentoPagamentoController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\EmpresaConfigController;
use App\Http\Controllers\Admin\EmpresaController;
use App\Http\Controllers\Admin\RelatorioController;
use App\Http\Controllers\Admin\ServicoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Wpp\WhatsAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rotas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rotas de agendamento
Route::get('/{empresa:slug}/cancelar-agendamento', [AgendamentoController::class, 'mostrarLoginCliente'])->name('agendamento.cancelar.form');
Route::post('/cancelar-agendamento', [AgendamentoController::class, 'cancelarAgendamento'])->name('agendamento.cancelar');

// Rotas da área do cliente
Route::get('/{empresa:slug}/cliente', [AgendamentoController::class, 'mostrarLoginCliente'])->name('cliente.login');
Route::post('/{empresa:slug}/cliente', [AgendamentoController::class, 'areaCliente'])->name('cliente.area');
Route::get('/{empresa:slug}/cliente/area', [AgendamentoController::class, 'areaClienteLogado'])->name('cliente.area.logado');
Route::post('/{empresa:slug}/cliente/logout', [AgendamentoController::class, 'logoutCliente'])->name('cliente.logout');

// Rotas de administração
Route::prefix('admin')->name('admin.')->group(function () {
    // Rotas de autenticação
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Rotas protegidas por autenticação
    Route::middleware('auth')->group(function () {
        Route::get('dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

        // Rotas para Super Admin (gestão de empresas)
        Route::resource('empresas', EmpresaController::class)->except(['show', 'destroy']);
        Route::patch('empresas/{empresa}/toggle-status', [EmpresaController::class, 'toggleStatus'])->name('empresas.toggle-status');

        // Usuários (novo CRUD)
        Route::resource('usuarios', UsuarioController::class)
            ->except(['show'])
            ->names('usuarios');

        // Rotas para Admin da Empresa (gestão de serviços)
        Route::resource('servicos', ServicoController::class)->except(['show']);
        Route::patch('servicos/{servico}/toggle-status', [ServicoController::class, 'toggleStatus'])->name('servicos.toggle-status');

        // Rotas para configurações da empresa
        Route::get('empresa-config', [EmpresaConfigController::class, 'edit'])->name('empresa-config.edit');
        Route::patch('empresa-config', [EmpresaConfigController::class, 'update'])->name('empresa-config.update');

        // Rotas para carga horária
        Route::get('working-hours', [AuthController::class, 'showWorkingHoursForm'])->name('working_hours.form');
        Route::post('working-hours', [AuthController::class, 'saveWorkingHours'])->name('working_hours.save');

        // Rotas para relatórios
        Route::get('relatorios/atendimentos', [RelatorioController::class, 'atendimentos'])->name('relatorios.atendimentos');

        // Rotas para configurações do WhatsApp
        Route::prefix('wpp')->name('wpp.')->group(function () {
            Route::get('config', [App\Http\Controllers\Wpp\WaConfigController::class, 'showForm'])->name('config.form');
            Route::post('config', [App\Http\Controllers\Wpp\WaConfigController::class, 'save'])->name('config.save');
        });
        Route::post('/agendamentos/{agendamento}/registrar-pagamento', [AgendamentoPagamentoController::class, 'store'])->name('agendamentos.registrar-pagamento');
    });
});

// Rotas públicas para webhooks do WhatsApp (sem autenticação)
Route::prefix('webhook/whatsapp')->name('webhook.whatsapp.')->group(function () {
    Route::get('/', [App\Http\Controllers\Wpp\WebhookController::class, 'verify'])->name('verify');
    Route::post('/', [App\Http\Controllers\Wpp\WebhookController::class, 'handle'])->name('handle');
});

Route::prefix('wpp')->group(function () {
    Route::post('/send-now', [WhatsAppController::class, 'sendNow'])->name('wpp.send.now');
    Route::post('/send-queued', [WhatsAppController::class, 'sendQueued'])->name('wpp.send.queued');
});

// Rotas da empresa (devem ficar por último para não conflitar)
Route::get('/{empresa:slug}', [HomeController::class, 'empresa'])->name('empresa');
Route::get('/{empresa:slug}/{servico}/agendar', [AgendamentoController::class, 'mostrarHorarios'])->name('agendamento.horarios');
Route::post('/{empresa:slug}/{servico}/agendar', [AgendamentoController::class, 'confirmarAgendamento'])->name('agendamento.confirmar');
