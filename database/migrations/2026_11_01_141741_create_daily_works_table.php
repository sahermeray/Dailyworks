<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->string('work_type');
            $table->string('unit')->nullable();
            $table->decimal('daily_quantity', 15, 3)->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'work_date']);
            $table->index(['contract_id', 'work_type', 'unit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_works');
    }
};
