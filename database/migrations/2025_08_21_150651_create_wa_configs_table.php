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
        Schema::create("wa_configs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("empresa_id")->constrained("empresas")->onDelete("cascade");
            $table->string("phone_number_id");
            $table->string("waba_id");
            $table->text("token"); // Token criptografado
            $table->string("sender_display_name");
            $table->timestamps();

            $table->unique(["empresa_id"], "unique_wa_config_per_empresa");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("wa_configs");
    }
};


