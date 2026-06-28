<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One table behind two Centers: grants live in the Funding Center,
        // tenders and partnerships in the Opportunity Center. The `type`
        // column is the only thing that tells them apart.
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('grant');          // grant | tender | partnership
            $table->string('title');
            $table->string('organisation')->nullable();        // donor / issuer / partner
            $table->text('description')->nullable();
            $table->string('amount')->nullable();              // free text, e.g. "€50k–€200k"
            $table->date('deadline')->nullable();
            $table->string('url')->nullable();
            $table->string('status')->default('open');         // open | closed | draft
            $table->boolean('ai_suggested')->default(false);   // flag AI-proposed leads
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
