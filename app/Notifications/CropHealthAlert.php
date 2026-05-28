<?php

namespace App\Notifications;

use App\Models\CropCycle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CropHealthAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CropCycle $cropCycle,
        protected string $alertType,    // 'low_ndvi' | 'drought_risk' | 'high_yield' | 'harvest_ready'
        protected string $message,
        protected float $ndviValue,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->alert_email_enabled ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $icon = match ($this->alertType) {
            'low_ndvi'      => '🌿',
            'drought_risk'  => '☀️',
            'harvest_ready' => '🌾',
            'high_yield'    => '📈',
            default         => '🌱',
        };

        $subject = match ($this->alertType) {
            'low_ndvi'      => 'Crop Health Alert — Low NDVI Detected',
            'drought_risk'  => 'Drought Risk Alert for Your Farm Field',
            'harvest_ready' => 'Harvest Window Alert — Crop is Ready!',
            'high_yield'    => 'Excellent Crop Yield Predicted',
            default         => 'CropsCycle Crop Alert',
        };

        return (new MailMessage)
            ->subject("{$icon} {$subject}")
            ->greeting("Hello, {$notifiable->name}!")
            ->line($this->message)
            ->line("**Crop Type:** {$this->cropCycle->crop_type}")
            ->line("**Region:** {$this->cropCycle->region}")
            ->line("**Current NDVI:** {$this->ndviValue}")
            ->line("**Season:** {$this->cropCycle->season} {$this->cropCycle->season_year}")
            ->action('View Crop Cycle →', route('crop-cycles.show', $this->cropCycle))
            ->line('Thank you for using CropsCycle — Smart Agriculture Analytics.')
            ->salutation('Regards, CropsCycle AI Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'         => "Crop Alert: {$this->cropCycle->crop_type}",
            'message'       => $this->message,
            'alert_type'    => $this->alertType,
            'ndvi_value'    => $this->ndviValue,
            'crop_cycle_id' => (string) $this->cropCycle->id,
            'icon'          => 'bar-chart-fill',
            'color'         => match ($this->alertType) {
                'harvest_ready', 'high_yield' => 'success',
                'drought_risk'                => 'warning',
                'low_ndvi'                    => 'danger',
                default                       => 'info',
            },
            'url' => route('crop-cycles.show', $this->cropCycle),
        ];
    }
}
