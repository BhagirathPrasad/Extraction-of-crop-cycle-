a<?php

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\DatasetController;

// Create a dummy uploaded file from the sample dataset
$filePath = public_path('sample_dataset.csv');
$file = new UploadedFile(
    $filePath,
    'sample_dataset.csv',
    'text/csv',
    null,
    true // test mode
);

$request = Request::create('/datasets', 'POST', [
    'name' => 'Test Dataset',
    'type' => 'CSV',
    'crop_type' => 'Wheat',
    'region' => 'Punjab',
], [], ['file' => $file]);

// We need to act as an authenticated user
\Auth::loginUsingId(1);

$controller = app(DatasetController::class);

try {
    // Validate request through FormRequest manually because it's tricky to inject
    $formRequest = \App\Http\Requests\StoreDatasetRequest::createFrom($request);
    $formRequest->setContainer(app());
    $formRequest->validateResolved();

    $response = $controller->store($formRequest);
    echo "SUCCESS: " . get_class($response) . "\n";
    echo "Dataset Count: " . \App\Models\Dataset::count() . "\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "VALIDATION ERROR:\n";
    print_r($e->errors());
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
