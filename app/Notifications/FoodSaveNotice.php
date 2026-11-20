<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifikasi dalam aplikasi (lonceng). Disimpan di tabel `notifications`,
 * tanpa email — hosting gratis tidak menjamin pengiriman SMTP.
 */
class FoodSaveNotice extends Notification
{
    /**
     * @param  string  $tone  primary|accent|info|danger
     */
    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public string $icon = 'bell',
        public string $tone = 'primary',
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => $this->icon,
            'tone' => $this->tone,
        ];
    }
}
