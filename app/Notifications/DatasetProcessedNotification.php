<?php

namespace App\Notifications;

use App\Models\CropCycle;
use App\Models\Dataset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DatasetProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Dataset   $dataset,
        public CropCycle $cropCycle
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Dataset Processing Complete — ' . $this->dataset->name)
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('Your dataset **' . $this->dataset->name . '** has been processed successfully.')
            ->line('**Crop Type:** ' . ucfirst($this->cropCycle->crop_type))
            ->line('**Region:** ' . $this->cropCycle->region)
            ->line('**NDVI Peak:** ' . $this->cropCycle->ndvi_max)
            ->line('**Yield Prediction:** ' . number_format($this->cropCycle->yield_prediction, 0) . ' kg/ha')
            ->action('View Results', route('crop-cycles.show', $this->cropCycle))
            ->line('Thank you for using CropsCycle!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'dataset_processed',
            'title'            => 'Dataset Processing Complete',
            'message'          => "Dataset '{$this->dataset->name}' processed. Crop cycle extracted for {$this->cropCycle->crop_type} in {$this->cropCycle->region}.",
            'dataset_id'       => $this->dataset->id,
            'crop_cycle_id'    => $this->cropCycle->id,
            'yield_prediction' => $this->cropCycle->yield_prediction,
            'ndvi_max'         => $this->cropCycle->ndvi_max,
            'url'              => route('crop-cycles.show', $this->cropCycle),
            'icon'             => 'check-circle',
            'color'            => 'success',
        ];
    }
}
