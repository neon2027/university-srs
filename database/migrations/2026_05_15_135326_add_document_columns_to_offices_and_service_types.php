<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->string('citizen_charter')->nullable()->after('email');
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->string('work_instruction')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('citizen_charter');
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('work_instruction');
        });
    }
};
