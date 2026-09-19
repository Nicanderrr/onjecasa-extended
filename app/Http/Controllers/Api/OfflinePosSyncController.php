<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\OfflinePosSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OfflinePosSyncController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $token = (string) config('offline_pos.sync_token');
        $provided = (string) $request->bearerToken();

        abort_if($token === '' || ! hash_equals($token, $provided), 401, 'Invalid POS sync token.');

        try {
            return response()->json(OfflinePosSync::importSale($request->all()));
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'failed',
                'errors' => $exception->errors(),
            ], 422);
        }
    }
}
