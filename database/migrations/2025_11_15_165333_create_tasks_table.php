<?php

// database/migrations/2025_11_15_000000_create_tasks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('creator_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('assignee_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title', 255);
            $table->text('description')->nullable();

            $table->string('status', 30)->default('OPEN');

            $table->string('priority', 20)->default('MÉDIA');

            $table->date('due_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
