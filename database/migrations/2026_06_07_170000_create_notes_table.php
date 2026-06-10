<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('color', 7)->default('#fff9c4');
            $table->timestamps();
        });

        Schema::create('note_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('collaborator');
            $table->timestamps();
            $table->unique(['note_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_user');
        Schema::dropIfExists('notes');
    }
};
