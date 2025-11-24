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
        Schema::create('washes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
    $table->decimal('amount', 8, 2);
    $table->boolean('is_cash')->default(false);
    $table->text('signature')->nullable(); // Base64 signature
    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('washes');
    }
};
