<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company', 120);
            $table->string('role', 160);
            $table->string('location', 160)->nullable();
            $table->string('url', 2048)->nullable();
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->string('status', 20)->default('saved');
            $table->text('notes')->nullable();
            $table->date('applied_on');
            $table->boolean('remote')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
