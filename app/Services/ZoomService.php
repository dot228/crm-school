<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZoomService
{
    public function isConfigured(): bool
    {
        return !empty(Setting::get('zoom_account_id'))
            && !empty(Setting::get('zoom_client_id'))
            && !empty(Setting::get('zoom_client_secret'));
    }

    private function getAccessToken(): ?string
    {
        $accountId = Setting::get('zoom_account_id');
        $clientId = Setting::get('zoom_client_id');
        $clientSecret = Setting::get('zoom_client_secret');

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->withOptions(['verify' => false]) // TODO: remove on PROD env (costul)
            ->asForm()
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => $accountId,
            ]);
        if ($response->failed()) {
            Log::error('Zoom token error', $response->json());
            return null;
        }

        return $response->json('access_token');
    }

    public function createMeeting(string $topic, string $startTime, int $durationMinutes): ?string
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        $hostEmail = Setting::get('zoom_default_host');
        $userId = $hostEmail ?: 'me';

        $response = Http::withToken($token)
            ->withOptions(['verify' => false]) // TODO: remove on PROD env (costul)
            ->post("https://api.zoom.us/v2/users/{$userId}/meetings", [
                'topic' => $topic,
                'type' => 2,
                'start_time' => $startTime,
                'duration' => $durationMinutes,
                'settings' => [
                    'join_before_host' => true,
                    'waiting_room' => false,
                    'auto_recording' => 'none',
                ],
            ]);

        if ($response->failed()) {
            Log::error('Zoom create meeting error', $response->json());
            return null;
        }

        return $response->json('join_url');
    }

    public function deleteMeeting(string $zoomLink): void
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return;
        }

        if (!preg_match('/zoom\.us\/j\/(\d+)/', $zoomLink, $m)) {
            return;
        }

        $meetingId = $m[1];

        Http::withToken($token)
            ->withOptions(['verify' => false])
            ->delete("https://api.zoom.us/v2/meetings/{$meetingId}");
    }
}
