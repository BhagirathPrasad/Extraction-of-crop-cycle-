<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['PDF', 'Excel', 'CSV'])->default('PDF');
            $table->enum('report_category', ['dataset', 'crop_cycle', 'ndvi', 'yield', 'summary'])->default('summary');
            $table->json('filters')->nullable();           // applied filters as JSON
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->default(0);
            $table->enum('status', ['pending', 'generating', 'ready', 'failed'])->default('pending');
            $table->integer('record_count')->default(0);
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();  // auto-cleanup
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
