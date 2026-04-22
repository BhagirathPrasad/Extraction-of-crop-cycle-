<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ndvi_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_cycle_id')->constrained()->onDelete('cascade');
            $table->date('observation_date');
            $table->decimal('ndvi_value', 5, 4);          // -1.0000 to 1.0000
            $table->decimal('evi_value', 5, 4)->nullable();  // Enhanced Vegetation Index
            $table->decimal('savi_value', 5, 4)->nullable(); // Soil-Adjusted VI
            $table->decimal('lai_value', 6, 3)->nullable();  // Leaf Area Index
            $table->enum('growth_stage', [
                'pre_sowing', 'germination', 'emergence', 'tillering',
                'jointing', 'heading', 'flowering', 'grain_filling',
                'maturity', 'post_harvest'
            ])->nullable();
            $table->decimal('temperature', 5, 2)->nullable(); // Celsius
            $table->decimal('rainfall', 7, 2)->nullable();    // mm
            $table->decimal('humidity', 5, 2)->nullable();    // percentage
            $table->decimal('soil_moisture', 5, 2)->nullable(); // percentage
            $table->integer('day_of_year')->nullable();
            $table->string('satellite_source')->nullable();  // Sentinel-2, Landsat-8, etc.
            $table->decimal('cloud_cover', 5, 2)->nullable(); // percentage
            $table->json('band_values')->nullable();         // raw band reflectances
            $table->timestamps();

            $table->index(['crop_cycle_id', 'observation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ndvi_records');
    }
};
