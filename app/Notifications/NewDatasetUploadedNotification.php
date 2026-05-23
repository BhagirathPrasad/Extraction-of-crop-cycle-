<?php

namespace App\Notifications;

use App\Models\Dataset;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDatasetUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Dataset $dataset, public User $user) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'dataset_uploaded',
            'title'        => 'Dataset Uploaded',
            'message'      => "New crop dataset uploaded by {$this->user->name}",
            'dataset_name' => $this->dataset->name,
            'uploaded_by'  => $this->user->name,
            'upload_time'  => now()->toIso8601String(),
            'icon'         => 'cloud-arrow-up',
            'color'        => 'success',
        ];
    }
}
