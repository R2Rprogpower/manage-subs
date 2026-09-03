<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->timestamp('activated_at')->nullable()->after('started_at');
            $table->timestamp('suspended_at')->nullable()->after('activated_at');
            $table->timestamp('cancelled_at')->nullable()->after('suspended_at');
        });

        DB::table('subscriptions')
            ->where('status', 'active')
            ->update(['activated_at' => DB::raw('started_at')]);
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropColumn(['activated_at', 'suspended_at', 'cancelled_at']);
        });
    }
};
