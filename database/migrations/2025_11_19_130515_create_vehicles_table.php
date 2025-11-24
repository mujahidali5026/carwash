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
    $table->string('registration')->unique();
    $table->string('driver_name');
    $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
    $table->boolean('banned')->default(false);
    $table->decimal('custom_price', 8, 2)->nullable();
    $table->integer('override_limit')->nullable();
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
