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
        Schema::create('project_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('week_number');
            $table->date('week_start');
            $table->date('week_end');
            $table->dateTime('report_open_at');
            $table->dateTime('report_close_at');
            $table->dateTime('revision_close_at');
            $table->timestamps();

            // Constraint: Nomor minggu tidak boleh ganda di project yang sama
            $table->unique(['project_id', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_weeks');
    }
};
