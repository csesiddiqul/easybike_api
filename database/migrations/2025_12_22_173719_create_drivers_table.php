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
    Schema::create('drivers', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->string('registration_number')->unique();
        $table->string('driver_image')->nullable();
        $table->string('nid')->unique();

        $table->date('registration_date');
        $table->integer('years_of_experience')->default(0);
        $table->text('present_address');
        $table->text('permanent_address');
        $table->enum('status', ['pending', 'active', 'expired','inactive','suspended'])->default('pending');

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
