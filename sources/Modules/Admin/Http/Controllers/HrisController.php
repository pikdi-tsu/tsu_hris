<?php

namespace Modules\Admin\Http\Controllers;

use Crypt;
use DB;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class HrisController extends Controller
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
            'title' => 'Welcome to HRIS',
            'menu'  => 'dashboard',
        );
//         dd(session()->all());
        return view('admin::hris.dashboard', $data);
    }
}
