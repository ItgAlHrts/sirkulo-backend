<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware penjaga akses berbasis peran untuk route API.
 *
 * Penggunaan di routes:
 *   Route::middleware('role:ADMIN,SUPER_ADMIN')->group(...)
 *   Route::middleware('role:SUPER_ADMIN')->group(...)
 *   Route::middleware('role:MITRA')->group(...)
 *   Route::middleware('role:NASABAH')->group(...)
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Unauthenticated: Token tidak valid atau sesi telah berakhir.',
            ], 401);
        }

        $userRole = strtoupper($user->peran ?? '');

        // Super Admin selalu tembus semua role check
        if ($userRole === 'SUPER_ADMIN') {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($userRole === strtoupper($role)) {
                return $next($request);
            }
        }

        return response()->json([
            'sukses' => false,
            'pesan'  => "Akses Ditolak: Endpoint ini hanya dapat diakses oleh peran " . implode(' atau ', $roles) . ".",
            'peran_anda' => $userRole,
        ], 403);
    }
}
