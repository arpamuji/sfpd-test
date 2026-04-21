<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50);
            $table->uuid('next_role_id')->nullable();
            $table->timestamps();

            $table->foreign('next_role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
