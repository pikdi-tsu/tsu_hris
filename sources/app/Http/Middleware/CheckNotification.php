<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

use App\Models\CutiKaryawan;
use App\Models\DataDosenTendik;

class CheckNotification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $getid = DataDosenTendik::where('user_id', Auth::id())->first('id');

            $notifatasan = CutiKaryawan::where('id_atasan', $getid->id)
                ->where('statusatasan', 'waiting')
                ->where('is_active', '1')
                ->count();

            $notifhrd = CutiKaryawan::where('id_hrd', $getid->id)
                ->where('statushrd', 'waiting')
                ->where('is_active', '1')
                ->count();

            session([
                'atasannotif' => $notifatasan,
                'hrdnotif'    => $notifhrd
            ]);
        }

        return $next($request);
    }
}
