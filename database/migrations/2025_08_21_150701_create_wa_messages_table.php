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
        Schema::create("wa_messages", function (Blueprint $table) {
            $table->id();
            $table->foreignId("empresa_id")->constrained("empresas")->onDelete("cascade");
            $table->foreignId("agendamento_id")->nullable()->constrained("agendamentos")->onDelete("set null");
            $table->foreignId("usuario_id")->constrained("usuarios")->onDelete("cascade");
            $table->string("to_msisdn"); // Telefone do destinatário em E.164
            $table->string("type"); // CONFIRM, REMINDER, CANCEL, etc.
            $table->string("template_name")->nullable();
            $table->json("payload")->nullable(); // Conteúdo da mensagem ou variáveis do template
            $table->string("provider_message_id")->nullable(); // ID da mensagem retornado pelo provedor
            $table->string("status")->default("QUEUED"); // QUEUED, SENT, DELIVERED, READ, FAILED
            $table->text("error")->nullable();
            $table->integer("attempts")->default(0);
            $table->timestamp("sent_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("wa_messages");
    }
};


