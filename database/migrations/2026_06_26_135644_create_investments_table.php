<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('investment_date');
            $table->timestamps();

            $table->unique(['investor_id', 'investment_date']);
            $table->index('investment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
