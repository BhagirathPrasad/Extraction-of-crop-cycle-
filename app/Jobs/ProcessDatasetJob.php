<?php

namespace App\Jobs;

use App\Models\Dataset;
use App\Notifications\DatasetProcessedNotification;
use App\Services\NdviProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessDatasetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public Dataset $dataset) {}

    public function handle(NdviProcessingService $service): void
    {
        try {
            $this->dataset->update(['status' => 'processing']);

            $cropCycle = $service->processDataset($this->dataset);

            $this->dataset->update([
                'status'       => 'processed',
                'processed_at' => now(),
            ]);

            // Notify the dataset owner
            $this->dataset->user->notify(new DatasetProcessedNotification($this->dataset, $cropCycle));

        } catch (Throwable $e) {
            $this->dataset->update([
                'status'           => 'failed',
                'processing_notes' => 'Error: ' . $e->getMessage(),
            ]);
            throw $e; // Re-throw so Laravel Queue retries
        }
    }

    public function failed(Throwable $e): void
    {
        $this->dataset->update([
            'status'           => 'failed',
            'processing_notes' => 'Job failed: ' . $e->getMessage(),
        ]);
    }
}
