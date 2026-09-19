<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function snapshot(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'unread_count' => 0,
                'latest_unread_id' => null,
                'latest_type' => null,
            ]);
        }

        $latest = $user->unreadNotifications()->latest()->first();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'latest_unread_id' => $latest?->id,
            'latest_type' => $latest?->data['type'] ?? null,
            'latest_title' => $latest?->data['title'] ?? null,
        ]);
    }
}
