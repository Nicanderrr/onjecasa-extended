<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuditTrail
{
    public static function record(string $event, string $description, array $context = []): void
    {
        try {
            $request = request();
            $user = auth()->user();

            DB::table('pos_audit_trails')->insert([
                'user_id' => $user?->id,
                'branch_id' => BranchContext::activeId(),
                'user_name' => $user?->name,
                'user_role' => $user?->role,
                'event' => $event,
                'auditable_type' => $context['auditable_type'] ?? null,
                'auditable_id' => $context['auditable_id'] ?? null,
                'description' => $description,
                'properties' => isset($context['properties'])
                    ? json_encode(self::redact($context['properties']), JSON_THROW_ON_ERROR)
                    : null,
                'ip_address' => $request?->ip(),
                'user_agent' => Str::limit((string) $request?->userAgent(), 1000, ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Unable to write POS audit trail.', [
                'event' => $event,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private static function redact(array $properties): array
    {
        $sensitiveKeys = ['password', 'pincode', 'admin_pincode', 'pincode_hash', 'token'];

        foreach ($properties as $key => $value) {
            if (in_array((string) $key, $sensitiveKeys, true)) {
                $properties[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $properties[$key] = self::redact($value);
            }
        }

        return $properties;
    }
}
