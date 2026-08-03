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
        Schema::create('client_assets', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('client_name');
            $table->string('live_url')->nullable();
            $table->string('staging_url')->nullable();
            $table->string('server_provider')->nullable();
            $table->string('server_ip')->nullable();
            $table->string('domain_registrar')->nullable();
            $table->date('domain_expiry')->nullable();
            $table->date('ssl_expiry')->nullable();
            $table->string('tech_stack')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->foreignId('assigned_lead_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->decimal('monthly_cost', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_assets');
    }
};
