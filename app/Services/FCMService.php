<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Contract\Messaging;
class FCMService
{
    private $messaging;
    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }
    public function sendNotification(string $deviceToken, string $title, string $body, array $data = [])
    {
        // $messaging = app('firebase.messaging');

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        $this->messaging->send($message);
    }
}
