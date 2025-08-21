<?php

namespace Database\Factories;

use App\Models\WaConfig;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class WaConfigFactory extends Factory
{
    protected $model = WaConfig::class;

    public function definition(): array
    {
        return [
            "empresa_id" => Empresa::factory(),
            "phone_number_id" => $this->faker->numerify("##############"),
            "waba_id" => $this->faker->numerify("##############"),
            "token" => $this->faker->sha256(),
            "sender_display_name" => $this->faker->company(),
        ];
    }
}


