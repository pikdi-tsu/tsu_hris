<?php

namespace Modules\Users\Http\Controllers\SelfService;

use Crypt;
use DB;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Admin\Entities\MasterHariLibur;

class DashboardController extends Controller
{
    public function __construct()
    {
//        $this->middleware('checklogin');
        $this->middleware('auth');
//        $this->middleware('verified');
    }

    public function index(){

        if(Session::has('tmp')){
            Session::forget('tmp');
        }

        $data = array(
            'title' => 'Welcome to Self Service',
            'menu'  => 'dashboard',
        );

//         dd(session()->all());
        return view('users::selfservice.dashboard', $data);
    }

    /**
     * API Internal FullCalendar
     */
    public function getHolidays(Request $request)
    {
        // FullCalendar otomatis mengirim parameter 'start' dan 'end' setiap kali bulan berganti
        $start = $request->start;
        $end = $request->end;

        // Tarik data yang aktif dan sesuai rentang bulan
        $dataLibur = MasterHariLibur::where('isactive', 'Y')
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('tanggal', [$start, $end]);
            })
            ->get();

        $events = [];

        foreach ($dataLibur as $libur) {
            $backgroundColor = '#17a2b8'; // Default: Info (Biru) untuk Institusi
            $textColor = '#ffffff';

            if ($libur->status_libur === 'Nasional') {
                $backgroundColor = '#dc3545'; // Danger (Merah)
            } elseif ($libur->status_libur === 'Cuti Bersama') {
                $backgroundColor = '#ffc107'; // Warning (Kuning)
                $textColor = '#343a40';
            }

            $events[] = [
                'title'           => $libur->keterangan,
                'start'           => $libur->tanggal,
                'backgroundColor' => $backgroundColor,
                'borderColor'     => $backgroundColor,
                'textColor'       => $textColor,
                'allDay'          => true, // Full day event
            ];
        }

        return response()->json($events);
    }
}
