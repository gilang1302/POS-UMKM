<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OwnerOnly
{
    // hanya owner yang boleh masuk, kasir ditolak
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role != 'owner') {
            abort(403, 'Halaman ini hanya untuk Owner.');
        }

        return $next($request);
    }
}
