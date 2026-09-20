<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        if (strtoupper(Session::get('admin_peran', '')) !== 'SUPER_ADMIN') {
            if ($request->expectsJson()) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => 'Akses ditolak: Fitur ini hanya dapat diakses oleh Super Administrator.',
                ], 403);
            }

            return redirect()->route('admin.dashboard')->with('error', 'Akses Ditolak: Fitur dan pengaturan ini hanya dapat diakses oleh Super Administrator.');
        }

        return $next($request);
    }
}
