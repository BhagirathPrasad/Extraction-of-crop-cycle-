<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dataset;
use App\Models\User;
use App\Services\NdviProcessingService;
use App\Jobs\ProcessDatasetJob;

try {
    $user = User::first();
    if (!$user) {
        throw new \Exception("No user found in the database. Please register/login a user first.");
    }
    
    echo "Using User: " . $user->name . " (ID: " . $user->id . ")\n";

    // Simulate dataset upload
    $filePath = 'datasets/test_sample_' . time() . '.csv';
    $sourceFile = __DIR__ . '/../public/sample_dataset.csv';
    
    // Copy the sample file to the private storage
    $targetPath = storage_path('app/private/' . $filePath);
    @mkdir(dirname($targetPath), 0777, true);
    copy($sourceFile, $targetPath);
    echo "Copied sample dataset file to: $targetPath\n";

    $dataset = Dataset::create([
        'user_id'            => $user->id,
        'name'               => 'Test Auto Uploaded Dataset',
        'description'        => 'This is a test dataset created by test_job.php',
        'type'               => 'CSV',
        'file_path'          => $filePath,
        'original_filename'  => 'sample_dataset.csv',
        'file_size'          => filesize($sourceFile),
        'crop_type'          => 'wheat',
        'region'             => 'Punjab',
        'country'            => 'India',
        'latitude'           => 30.7333,
        'longitude'          => 76.7794,
        'data_start_date'    => '2024-06-15',
        'data_end_date'      => '2024-10-05',
        'status'             => 'pending',
    ]);

    echo "Dataset created in DB with ID: " . $dataset->id . "\n";
    echo "Processing dataset synchronously via ProcessDatasetJob...\n";
    
    $job = new ProcessDatasetJob($dataset);
    app()->call([$job, 'handle']);
    
    $dataset->refresh();
    echo "Processing finished! Dataset status: " . $dataset->status . "\n";
    echo "Processing notes: " . $dataset->processing_notes . "\n";
    echo "Total record count: " . $dataset->record_count . "\n";
    
    $cropCyclesCount = $dataset->cropCycles()->count();
    echo "Generated Crop Cycles count: " . $cropCyclesCount . "\n";
    
    if ($cropCyclesCount > 0) {
        $cycle = $dataset->cropCycles()->first();
        echo "Crop Cycle ID: " . $cycle->id . "\n";
        echo "NDVI Max: " . $cycle->ndvi_max . "\n";
        echo "NDVI Min: " . $cycle->ndvi_min . "\n";
        echo "NDVI Mean: " . $cycle->ndvi_mean . "\n";
        echo "NDVI Records count: " . $cycle->ndviRecords()->count() . "\n";
    }

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
