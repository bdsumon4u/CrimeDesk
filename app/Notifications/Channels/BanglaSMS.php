<?php

namespace App\Notifications\Channels;

use App\Notifications\SendOtpCode;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BanglaSMS
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @return void
     */
    public function send($notifiable, SendOtpCode $notification)
    {
        $phone = preg_replace('/[^\d]/', '', $notifiable->phone);
        $phone = Str::startsWith($phone, '0')
            ? '88'.$phone
            : Str::replaceFirst('+', '', $phone);

        return $this->send_sms(config('services.smsbangla.url'), array_merge([
            'type' => 'text',
            'contacts' => $phone,
            'label' => 'transactional',
            'api_key' => config('services.smsbangla.apikey'),
            'senderid' => config('services.smsbangla.sender'),
        ], $notification->toArray($notifiable)));
    }

    private function send_sms($url, $data)
    {
        Log::info('sending sms:', $data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        Log::info('sms response:'.$response);

        return $response;
    }
}
