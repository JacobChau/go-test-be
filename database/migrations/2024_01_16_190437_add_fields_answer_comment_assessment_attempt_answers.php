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
        Schema::table('assessment_attempt_answers', function (Blueprint $table) {
            $table->text('answer_comment')->nullable()->after('answer_content');
            $table->integer('marks')->nullable()->after('answer_comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_attempt_answers', function (Blueprint $table) {
            $table->dropColumn('answer_comment');
            $table->dropColumn('marks');
        });
    }
};
