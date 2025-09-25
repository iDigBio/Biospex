<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/*
 * OpCache Management Routes
 *
 * Secure webhook endpoint for resetting OpCache after deployments
 * This provides a reliable alternative to CLI-based OpCache reset
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * OpCache Reset Webhook
 *
 * POST /admin/opcache/reset/{token}
 *
 * Security Features:
 * - Token-based authentication
 * - IP whitelist (optional)
 * - JSON response for monitoring
 */
Route::post('/opcache/reset/{token}', function (Request $request, $token) {
    // Security: Verify the webhook token
    if ($token !== config('app.opcache_webhook_token')) {
        abort(403, 'Invalid webhook token');
    }

    // Additional security: IP whitelist for deployment server
    $allowedIps = ['3.142.169.134']; // Biospex deployment server IP
    if (! in_array($request->ip(), $allowedIps)) {
        abort(403, 'IP not allowed');
    }

    // Reset OpCache
    if (function_exists('opcache_reset')) {
        opcache_reset();

        return response()->json([
            'success' => true,
            'message' => 'OpCache reset successfully',
            'timestamp' => now()->toISOString(),
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'OpCache is not available',
        ], 500);
    }
})->name('admin.opcache.webhook.reset');
