<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Funding and Opportunity Centers (grants/tenders/partnerships) were CRM
 * features that don't belong in an academic research portal. Drop the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('opportunities');
    }

    public function down(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('grant');
            $table->string('title');
            $table->string('organisation')->nullable();
            $table->text('description')->nullable();
            $table->string('amount')->nullable();
            $table->date('deadline')->nullable();
            $table->string('url')->nullable();
            $table->string('status')->default('open');
            $table->boolean('ai_suggested')->default(false);
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }
};
