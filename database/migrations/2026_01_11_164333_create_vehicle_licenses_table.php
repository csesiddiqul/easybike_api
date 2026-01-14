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
        Schema::create('vehicle_licenses', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->onDelete('cascade');

            // Licence info
            $table->decimal('licence_fee', 10, 2)->default(0);
            $table->enum('status', ['pending', 'active', 'expired'])->default('pending')->nullable();
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->nullable();

            // Dates
            $table->date('activated_at')->nullable();
            $table->date('expired_at')->nullable();

            // Unique constraint to prevent duplicate licence for same vehicle + fiscal year
            $table->unique(['vehicle_id', 'fiscal_year_id'], 'unique_vehicle_fiscal_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_licenses');
    }
};
