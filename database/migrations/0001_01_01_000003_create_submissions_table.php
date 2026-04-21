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
            $table->foreignUuid('requestor_id')->constrained('users');
            $table->foreignUuid('current_role_id')->constrained('roles');
            $table->string('status', 50)->default('draft');
            $table->string('warehouse_name');
            $table->text('warehouse_address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('budget_estimate', 15, 2);
            $table->text('description')->nullable();
            $table->foreignUuid('rejected_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
