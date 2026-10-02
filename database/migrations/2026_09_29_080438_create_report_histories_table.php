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
        Schema::create('report_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users'); // User yang mengubah status/merevisi
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->integer('revision_number')->default(0);
            $table->foreignId('evaluation_label_id')->nullable()->constrained('report_evaluation_labels');
            $table->text('comment')->nullable();
            $table->timestamps(); // Menggunakan created_at untuk waktu pencatatan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_histories');
    }
};
