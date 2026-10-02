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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_week_id')->constrained('project_weeks')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('division_id')->constrained('project_divisions');
            $table->string('type', 20); // PERSONAL atau DIVISION
            $table->string('title', 200);
            $table->text('work_done');
            $table->text('achievements')->nullable();
            $table->text('obstacles')->nullable();
            $table->text('solutions')->nullable();
            $table->text('next_plan');
            $table->text('support_needed')->nullable();
            $table->decimal('progress_percentage', 5, 2)->nullable(); // Hanya diisi jika tipe DIVISION
            $table->string('status', 30)->default('SUBMITTED'); // SUBMITTED, REVIEWED, APPROVED, REVISION_REQUIRED
            $table->integer('revision_count')->default(0);
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->dateTime('reviewed_at')->nullable();
            $table->foreignId('evaluation_label_id')->nullable()->constrained('report_evaluation_labels');
            $table->text('review_comment')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users');
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();

            // Index tambahan untuk mempercepat query pencarian laporan per minggu/author
            $table->index(['project_week_id', 'author_id']);
            $table->index(['project_week_id', 'division_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
