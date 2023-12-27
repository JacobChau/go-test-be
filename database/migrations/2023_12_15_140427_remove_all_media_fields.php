<?php

use App\Enums\MediaType;
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
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('media_url');
            $table->dropColumn('media_type');
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->dropColumn('media_url');
            $table->dropColumn('media_type');
        });

        Schema::table('question_explanations', function (Blueprint $table) {
            $table->dropColumn('media_url');
            $table->dropColumn('media_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('media_url', 2048)->nullable();
            $table->smallInteger('media_type')->default(MediaType::Image)->nullable();
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->string('media_url', 2048)->nullable();
            $table->smallInteger('media_type')->default(MediaType::Image)->nullable();
        });

        Schema::table('question_explanations', function (Blueprint $table) {
            $table->string('media_url', 2048)->nullable();
            $table->smallInteger('media_type')->default(MediaType::Image)->nullable();
        });
    }
};
