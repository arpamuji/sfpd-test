<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('requestor_id');
            $table->uuid('current_role_id');
            $table->string('status', 50)->default('draft');
            $table->string('warehouse_name');
            $table->text('warehouse_address');
            $table->decimal('latitude', 12, 8);
            $table->decimal('longitude', 12, 8);
            $table->decimal('budget_estimate', 15, 2);
            $table->text('description')->nullable();
            $table->uuid('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('requestor_id')->references('id')->on('users');
            $table->foreign('current_role_id')->references('id')->on('roles');
            $table->foreign('rejected_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
