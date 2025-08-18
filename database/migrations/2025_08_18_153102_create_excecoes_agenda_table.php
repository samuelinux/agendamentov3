<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('excecoes_agenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->enum('tipo', ['FERIAS', 'FERIADO', 'SAIDINHA']);
            $table->string('descricao');
            $table->date('data_inicio')->nullable(); // Para Férias/Feriado
            $table->date('data_fim')->nullable(); // Para Férias/Feriado
            $table->dateTime('data_hora_inicio_intervalo')->nullable(); // Para Saidinha
            $table->dateTime('data_hora_fim_intervalo')->nullable(); // Para Saidinha
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excecoes_agenda');
    }
};
