<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parental_controls', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('notify_on_purchase');
        });
    }

    public function down(): void
    {
        Schema::table('parental_controls', function (Blueprint $table) {
            $table->dropColumn('is_blocked');
        });
    }
};
