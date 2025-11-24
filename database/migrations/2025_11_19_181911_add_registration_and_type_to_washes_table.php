<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('washes', function (Blueprint $table) {
            $table->string('registration')->nullable()->after('vehicle_id');
            $table->enum('type', ['cash', 'company'])->default('cash')->after('registration');
        });
    }

    public function down()
    {
        Schema::table('washes', function (Blueprint $table) {
            $table->dropColumn(['registration', 'type']);
        });
    }

};
