<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('onboarding_status')->nullable()->after('avatar');
            $table->foreignId('pending_office_id')->nullable()->constrained('offices')->nullOnDelete()->after('onboarding_status');
            $table->timestamp('onboarding_completed_at')->nullable()->after('pending_office_id');
        });

        // Backfill so existing users skip the onboarding screen.
        DB::table('users')->update(['onboarding_completed_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('pending_office_id');
            $table->dropColumn(['onboarding_status', 'onboarding_completed_at']);
        });
    }
};
