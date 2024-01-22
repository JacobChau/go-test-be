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
        Schema::table('assessments', function (Blueprint $table) {
            $table->integer('duration')->nullable()->change();
            $table->decimal('total_marks')->nullable()->change();
            $table->decimal('pass_marks')->nullable()->change();
            $table->integer('max_attempts')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->integer('duration')->nullable(false)->change();
            $table->decimal('total_marks')->nullable(false)->change();
            $table->decimal('pass_marks')->nullable(false)->change();
            $table->integer('max_attempts')->nullable(false)->change();
        });
    }
};
