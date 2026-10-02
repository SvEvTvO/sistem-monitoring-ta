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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->date('actual_start_date');
            $table->date('week_1_start_date');
            $table->date('end_date');
            $table->foreignId('project_leader_id')->constrained('users');
            $table->string('status')->default('PLANNED'); // PLANNED, ACTIVE, COMPLETED, ARCHIVED
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
