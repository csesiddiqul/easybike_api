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
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('expiration_reminder')->default(7)->comment("Expiration reminder is set to 7 days");
            $table->unsignedInteger('access_action_minutes')->default(10)->comment("Access action minutes are set to 10 minutes");


            // Additional fields
            $table->string('logo')->nullable();
            $table->string('title')->nullable();
            $table->string('email')->nullable();
            $table->string('youtube')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};
