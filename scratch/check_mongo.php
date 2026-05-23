<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Checking MongoDB connection...\n";
    $connection = DB::connection('mongodb');
    $db = $connection->getMongoDB();

    $driverName = Schema::getConnection()->getDriverName();
    echo "Schema connection driver name: " . $driverName . "\n";

    echo "\nIndexes on 'users' collection:\n";
    $usersCollection = $db->selectCollection('users');
    foreach ($usersCollection->listIndexes() as $index) {
        echo "Index Name: " . $index->getName() . "\n";
        echo "Key: " . json_encode($index->getKey()) . "\n";
        echo "Unique: " . ($index->isUnique() ? 'yes' : 'no') . "\n\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
