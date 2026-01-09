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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();

            $table->string('system_name')->nullable();
            $table->string('system_logo')->nullable();

            $table->string('city_corporation_name')->nullable();
            $table->string('city_corporation_logo')->nullable();
            $table->string('city_corporation_phone')->nullable();

            $table->decimal('vehicle_charge_per_year', 10, 2)->default(0);
            $table->decimal('driver_licence_renew_charge', 10, 2)->default(0);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
