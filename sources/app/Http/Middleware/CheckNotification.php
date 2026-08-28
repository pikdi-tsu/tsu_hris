<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

use App\Models\CutiKaryawan;
use App\Models\IzinKaryawan;
use App\Models\LemburKaryawan;
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
            $getid = DataDosenTendik::where('user_id', Auth::id())->first(['id', 'is_active']);

            if ($getid) {
                // Auto-Kick jika akun dinonaktifkan saat user sedang login
                if ($getid->is_active == 0) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'Sesi dihentikan. Akun kepegawaian Anda telah dinonaktifkan.');
                }

                $notifcutiatasan = CutiKaryawan::where('id_atasan', $getid->id)
                    ->where('statusatasan', 'waiting')
                    ->where('is_active', '1')
                    ->count();

                $notifizinatasan = IzinKaryawan::where('id_atasan', $getid->id)
                    ->where('statusatasan', 'waiting')
                    ->where('is_active', '1')
                    ->count();

                $notiflemburatasan = LemburKaryawan::where('id_atasan', $getid->id)
                    ->where('statusatasan', 'waiting')
                    ->where('is_active', '1')
                    ->count();

                $notifcutihrd = CutiKaryawan::where('id_hrd', $getid->id)
                    ->where('statushrd', 'waiting')
                    ->where('is_active', '1')
                    ->count();

                $notifizinhrd = IzinKaryawan::where('id_hrd', $getid->id)
                    ->where('statushrd', 'waiting')
                    ->where('is_active', '1')
                    ->count();

                $notiflemburhrd = LemburKaryawan::where('id_hrd', $getid->id)
                    ->where('statushrd', 'waiting')
                    ->where('is_active', '1')
                    ->count();

                session([
                    'notifcutiatasan' => $notifcutiatasan,
                    'notifcutihrd'    => $notifcutihrd,
                    'notifizinatasan' => $notifizinatasan,
                    'notifizinhrd'    => $notifizinhrd,
                    'notiflemburatasan' => $notiflemburatasan,
                    'notiflemburhrd'  => $notiflemburhrd
                ]);
            } else {
                session([
                    'notifcutiatasan' => 0,
                    'notifcutihrd'    => 0,
                    'notifizinatasan' => 0,
                    'notifizinhrd'    => 0,
                    'notiflemburatasan' => 0,
                    'notiflemburhrd'  => 0
                ]);
            }
        }

        return $next($request);
    }
}
