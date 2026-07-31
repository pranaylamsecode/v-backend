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
        Schema::table('d_b_projects', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('live_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('d_b_projects', function (Blueprint $table) {
            $table->dropColumn(['description', 'repository_url', 'live_url']);
        });
    }
};
