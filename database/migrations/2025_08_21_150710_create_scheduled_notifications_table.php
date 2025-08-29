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
        Schema::create("scheduled_notifications", function (Blueprint $table) {
            $table->id();
            $table->foreignId("empresa_id")->constrained("empresas")->onDelete("cascade");
            $table->foreignId("agendamento_id")->constrained("agendamentos")->onDelete("cascade");
            $table->string("type"); // REMINDER, etc.
            $table->string("to_msisdn"); // Telefone do destinatário em E.164
            $table->timestamp("send_at");
            $table->string("status")->default("PENDING"); // PENDING, SENT, CANCELED, FAILED
            $table->integer("attempts")->default(0);
            $table->text("last_error")->nullable();
            $table->timestamps();

            $table->unique(["agendamento_id", "type", "send_at", "to_msisdn"], "unique_scheduled_notification");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("scheduled_notifications");
    }
};


