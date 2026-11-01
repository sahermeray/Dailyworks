<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_work_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_work_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('original_file_name');
            $table->timestamps();

            $table->index('daily_work_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_work_photos');
    }
};
