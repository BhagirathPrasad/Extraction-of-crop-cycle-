<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('crop_type');
            $table->string('variety')->nullable();
            $table->string('region');
            $table->string('field_id')->nullable();       // farm field identifier
            $table->integer('season_year');
            $table->enum('season', ['Kharif', 'Rabi', 'Zaid', 'Summer', 'Winter', 'Year-round'])->default('Kharif');

            // Extracted Parameters
            $table->date('sowing_date')->nullable();
            $table->date('emergence_date')->nullable();
            $table->date('tillering_date')->nullable();
            $table->date('jointing_date')->nullable();
            $table->date('heading_date')->nullable();
            $table->date('peak_growth_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->date('harvest_date')->nullable();

            // NDVI Statistics
            $table->decimal('ndvi_max', 5, 4)->nullable();
            $table->decimal('ndvi_min', 5, 4)->nullable();
            $table->decimal('ndvi_mean', 5, 4)->nullable();
            $table->decimal('ndvi_at_sowing', 5, 4)->nullable();
            $table->decimal('ndvi_at_peak', 5, 4)->nullable();
            $table->decimal('ndvi_at_harvest', 5, 4)->nullable();

            // Yield & Predictions
            $table->decimal('yield_prediction', 10, 2)->nullable();   // kg/ha
            $table->string('yield_unit')->default('kg/ha');
            $table->decimal('actual_yield', 10, 2)->nullable();
            $table->enum('yield_category', ['low', 'medium', 'high'])->nullable();

            // Smart Agriculture
            $table->json('irrigation_suggestions')->nullable();
            $table->json('fertilizer_suggestions')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'completed', 'failed'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_cycles');
    }
};
