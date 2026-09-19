<?php

namespace App\Console\Commands;

use App\Support\OfflinePosSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncOfflinePosSales extends Command
{
    protected $signature = 'pos:sync-offline-sales {--limit= : Maximum number of pending sales to sync.}';

    protected $description = 'Push local offline POS sales to the hosted ONJECASA installation.';

    public function handle(): int
    {
        if (config('offline_pos.mode') !== 'local') {
            $this->warn('OFFLINE_POS_MODE is not local, so no offline sales were pushed.');
            return self::SUCCESS;
        }

        $remoteUrl = (string) config('offline_pos.remote_url');
        $token = (string) config('offline_pos.sync_token');

        if ($remoteUrl === '' || str_contains($remoteUrl, 'your-hostinger-domain.com') || $token === '') {
            $this->error('Set OFFLINE_POS_REMOTE_URL to the real hosted domain and set OFFLINE_POS_SYNC_TOKEN before syncing.');
            return self::FAILURE;
        }

        $limit = (int) ($this->option('limit') ?: config('offline_pos.batch_size', 25));
        $endpoint = $remoteUrl . '/api/pos/offline-sales';

        $orders = DB::table('pos_orders')
            ->where(function ($query) {
                $query->whereNull('sync_status')->orWhereIn('sync_status', ['pending', 'failed']);
            })
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No pending offline POS sales to sync.');
            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($orders as $order) {
            if (empty($order->source_uuid)) {
                $order->source_uuid = (string) Str::uuid();
                DB::table('pos_orders')->where('id', $order->id)->update(['source_uuid' => $order->source_uuid]);
            }

            DB::table('pos_orders')->where('id', $order->id)->update([
                'sync_status' => 'syncing',
                'last_sync_attempt_at' => now(),
                'sync_error' => null,
                'updated_at' => now(),
            ]);

            try {
                $response = Http::withToken($token)
                    ->acceptJson()
                    ->timeout((int) config('offline_pos.timeout', 20))
                    ->post($endpoint, OfflinePosSync::salePayload($order));

                if ($response->successful() && in_array($response->json('status'), ['imported', 'duplicate'], true)) {
                    DB::table('pos_orders')->where('id', $order->id)->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_error' => null,
                        'updated_at' => now(),
                    ]);
                    $synced++;
                    $this->line('Synced sale ' . $order->code . '.');
                    continue;
                }

                $message = $response->body() ?: ('HTTP ' . $response->status());
                throw new \RuntimeException(Str::limit($message, 1000, ''));
            } catch (\Throwable $exception) {
                DB::table('pos_orders')->where('id', $order->id)->update([
                    'sync_status' => 'failed',
                    'sync_error' => Str::limit($exception->getMessage(), 1000, ''),
                    'updated_at' => now(),
                ]);
                $failed++;
                $this->warn('Failed sale ' . $order->code . ': ' . $exception->getMessage());
            }
        }

        $this->info("Offline POS sync finished. Synced: {$synced}. Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
