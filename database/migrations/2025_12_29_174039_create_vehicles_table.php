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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('owners')->cascadeOnDelete();
            $table->string('vehicle_type', 50);
            $table->string('supplier_type', 20); // person | company
            $table->string('registration_number')->unique();
            $table->string('vehicle_model_name', 100);
            $table->string('chassis_number')->unique();
            $table->string('status', 20)->default('pending'); // pending | approved | expired
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
