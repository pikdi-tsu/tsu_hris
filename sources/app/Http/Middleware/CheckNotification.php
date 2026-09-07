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
use App\Models\PayrollPeriod;

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
                    ->where('statusatasan', 'approved')
                    ->where('statushrd', 'waiting')
                    ->where('is_active', '1')
                    ->count();

                $notifizinhrd = IzinKaryawan::where('id_hrd', $getid->id)
                    ->where('statusatasan', 'approved')
                    ->where('statushrd', 'waiting')
                    ->where('is_active', '1')
                    ->count();

                $notiflemburhrd = LemburKaryawan::where('id_hrd', $getid->id)
                    ->where('statusatasan', 'approved')
                    ->where('statushrd', 'waiting')
                    ->where('is_active', '1')
                    ->count();

                // Query Helper Approval Pending
                $basePendingQuery = function($type) use ($getid) {
                    return PayrollPeriod::where('tipe', $type)
                        ->where(function ($q) use ($getid) {
                            $q->where(function ($sub) use ($getid) {
                                $sub->where('validator_1_id', $getid->id)
                                    ->where('status', 'pending_val_1');
                            })->orWhere(function ($sub) use ($getid) {
                                $sub->where('validator_2_id', $getid->id)
                                    ->where('status', 'pending_val_2');
                            })->orWhere(function ($sub) use ($getid) {
                                $sub->where('approval_id', $getid->id)
                                    ->where('status', 'pending_approval');
                            });
                        })->latest()->get();
                };

                // 1. Notifikasi Khusus Payroll
                $pendingPayrolls = $basePendingQuery('payroll');
                $notifpayroll = $pendingPayrolls->count();
                $firstPayroll = $pendingPayrolls->first();
                $notifpayroll_url = $firstPayroll
                    ? route('admin.payroll.show', $firstPayroll->id)
                    : route('admin.payroll.index');

                // 2. Notifikasi Khusus Honorarium Dosen
                $pendingHonorariums = $basePendingQuery('honorarium');
                $notifhonorarium = $pendingHonorariums->count();
                $firstHonorarium = $pendingHonorariums->first();
                $notifhonorarium_url = $firstHonorarium
                    ? route('admin.honorarium.show', $firstHonorarium->id)
                    : route('admin.honorarium.index');

                session([
                    'notifcutiatasan'    => $notifcutiatasan,
                    'notifcutihrd'       => $notifcutihrd,
                    'notifizinatasan'    => $notifizinatasan,
                    'notifizinhrd'       => $notifizinhrd,
                    'notiflemburatasan'  => $notiflemburatasan,
                    'notiflemburhrd'     => $notiflemburhrd,
                    'notifpayroll'       => $notifpayroll,
                    'notifpayroll_url'   => $notifpayroll_url,
                    'notifhonorarium'    => $notifhonorarium,
                    'notifhonorarium_url'=> $notifhonorarium_url,
                ]);
            } else {
                session([
                    'notifcutiatasan'    => 0,
                    'notifcutihrd'       => 0,
                    'notifizinatasan'    => 0,
                    'notifizinhrd'       => 0,
                    'notiflemburatasan'  => 0,
                    'notiflemburhrd'     => 0,
                    'notifpayroll'       => 0,
                    'notifpayroll_url'   => route('admin.payroll.index'),
                    'notifhonorarium'    => 0,
                    'notifhonorarium_url'=> route('admin.honorarium.index'),
                ]);
            }
        }

        return $next($request);
    }
}
