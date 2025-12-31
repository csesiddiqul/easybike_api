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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', [
                'driver_licence',
                'auto_registration',
            ]);

            $table->unsignedBigInteger('reference_id');

            $table->foreignId('fiscal_year_id')
                ->constrained('fiscal_years')
                ->cascadeOnDelete();

            $table->decimal('amount', 10, 2);

            $table->string('payment_method')->nullable();
            $table->string('trx_id')->nullable();

            $table->enum('status', ['pending', 'paid', 'failed'])
                ->default('pending');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
