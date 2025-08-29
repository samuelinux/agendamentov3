<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\WaConfig;
use Illuminate\Support\Facades\Crypt;

class WaConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_admin_can_view_whatsapp_config_form()
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(["empresa_id" => $empresa->id]);

        $response = $this->actingAs($admin)->get(route("admin.wpp.config.form"));

        $response->assertStatus(200);
        $response->assertViewIs("admin.wpp.config");
        $response->assertSee("Configurações da API do WhatsApp");
    }

    public function test_admin_can_save_whatsapp_config()
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(["empresa_id" => $empresa->id]);

        $configData = [
            "phone_number_id" => "123456789012345",
            "waba_id" => "543210987654321",
            "token" => "test_token",
            "sender_display_name" => "Test Sender",
        ];

        $response = $this->actingAs($admin)->post(route("admin.wpp.config.save"), $configData);

        $response->assertRedirect(route("admin.wpp.config.form"));
        $response->assertSessionHas("success", "Configurações do WhatsApp salvas com sucesso!");

        $this->assertDatabaseHas("wa_configs", [
            "empresa_id" => $empresa->id,
            "phone_number_id" => "123456789012345",
            "waba_id" => "543210987654321",
            "sender_display_name" => "Test Sender",
        ]);

        $config = WaConfig::first();
        $this->assertEquals("test_token", Crypt::decryptString($config->getRawOriginal("token")));
    }

    public function test_admin_can_update_whatsapp_config()
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(["empresa_id" => $empresa->id]);
        $config = WaConfig::factory()->create(["empresa_id" => $empresa->id]);

        $newConfigData = [
            "phone_number_id" => "987654321098765",
            "waba_id" => "012345678901234",
            "token" => "new_test_token",
            "sender_display_name" => "New Test Sender",
        ];

        $response = $this->actingAs($admin)->post(route("admin.wpp.config.save"), $newConfigData);

        $response->assertRedirect(route("admin.wpp.config.form"));

        $this->assertDatabaseHas("wa_configs", [
            "empresa_id" => $empresa->id,
            "phone_number_id" => "987654321098765",
            "waba_id" => "012345678901234",
            "sender_display_name" => "New Test Sender",
        ]);

        $this->assertEquals(1, WaConfig::count());
        $updatedConfig = WaConfig::first();
        $this->assertEquals("new_test_token", Crypt::decryptString($updatedConfig->getRawOriginal("token")));
    }

    public function test_validation_fails_with_missing_data()
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(["empresa_id" => $empresa->id]);

        $response = $this->actingAs($admin)->post(route("admin.wpp.config.save"), []);

        $response->assertSessionHasErrors(["phone_number_id", "waba_id", "token", "sender_display_name"]);
    }
}


