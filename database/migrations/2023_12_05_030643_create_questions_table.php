<?php

use App\Enums\MediaType;
use App\Enums\QuestionType;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->smallInteger('type')->default(QuestionType::MultipleChoice);
            $table->string('media_url', 2048)->nullable();
            $table->smallInteger('media_type')->default(MediaType::Image)->nullable();
            $table->boolean('is_published')->default(true);
            $table->foreignId('passage_id')->nullable()->constrained('passages')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
