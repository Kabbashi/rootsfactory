<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version_no')->default(1);
            $table->text('abstract')->nullable();
            $table->longText('body')->nullable();
            $table->text('changelog')->nullable();
            $table->timestamps();

            $table->unique(['publication_id', 'version_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_versions');
    }
};
