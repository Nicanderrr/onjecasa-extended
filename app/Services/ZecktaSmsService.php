<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZecktaSmsService
{
    public function sendReceipt(string $phone, string $receiptUrl, string $orderCode, string $amount): bool
    {
        $url = (string) config('services.zeckta.sms_url');
        $apiKey = (string) config('services.zeckta.api_key');
        if ($url === '' || $apiKey === '') {
            Log::warning('Receipt SMS skipped because Zeckta is not configured.', ['order' => $orderCode]);
            return false;
        }

        try {
            $request = Http::acceptJson()->asJson()->withToken($apiKey)->timeout((int) config('services.zeckta.timeout', 10));
            if ($secret = config('services.zeckta.api_secret')) {
                $request = $request->withHeaders(['X-API-Secret' => $secret]);
            }
            $response = $request->post($url, [
                'src' => (string) config('services.zeckta.sender_id', 'ONJECASA'),
                'dest' => $phone,
                'message' => sprintf('Thank you for shopping at ONJECASA. Receipt %s for GHS %s: %s', $orderCode, $amount, $receiptUrl),
                'priority' => 'normal',
                'type' => 'plain',
            ]);
            if ($response->successful() && $response->json('success') !== false) {
                return true;
            }
            Log::warning('Zeckta receipt SMS failed.', ['order' => $orderCode, 'status' => $response->status(), 'response' => $response->json() ?: $response->body()]);
        } catch (\Throwable $exception) {
            Log::warning('Zeckta receipt SMS exception.', ['order' => $orderCode, 'message' => $exception->getMessage()]);
        }
        return false;
    }
}
