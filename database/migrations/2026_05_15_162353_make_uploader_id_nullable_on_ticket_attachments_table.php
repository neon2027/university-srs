<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->dropForeign(['uploader_id']);
            $table->unsignedBigInteger('uploader_id')->nullable()->change();
            $table->foreign('uploader_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->dropForeign(['uploader_id']);
            $table->unsignedBigInteger('uploader_id')->nullable(false)->change();
            $table->foreign('uploader_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
