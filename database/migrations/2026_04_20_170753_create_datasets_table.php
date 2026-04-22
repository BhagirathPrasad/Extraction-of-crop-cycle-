<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('type', ['CSV', 'GeoTIFF', 'JSON'])->default('CSV');
            $table->string('file_path');
            $table->string('original_filename');
            $table->bigInteger('file_size')->default(0);  // bytes
            $table->string('crop_type')->nullable();      // wheat, rice, maize, etc.
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->date('data_start_date')->nullable();
            $table->date('data_end_date')->nullable();
            $table->integer('record_count')->default(0);
            $table->enum('status', ['pending', 'processing', 'processed', 'failed'])->default('pending');
            $table->text('processing_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();         // extra metadata blob
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};
