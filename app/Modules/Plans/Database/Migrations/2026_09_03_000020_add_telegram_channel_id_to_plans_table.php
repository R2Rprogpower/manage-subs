<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->foreignId('telegram_channel_id')
                ->nullable()
                ->after('id')
                ->constrained('telegram_channels')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('telegram_channel_id');
        });
    }
};
