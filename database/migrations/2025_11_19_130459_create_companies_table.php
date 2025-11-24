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
       Schema::create('companies', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->decimal('price_per_wash', 8, 2)->default(6);
    $table->integer('daily_limit')->default(1);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
