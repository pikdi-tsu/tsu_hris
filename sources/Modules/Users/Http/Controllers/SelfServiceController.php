<?php

namespace Modules\Users\Http\Controllers;

use Crypt;
use DB;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class SelfServiceController extends Controller
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
}
