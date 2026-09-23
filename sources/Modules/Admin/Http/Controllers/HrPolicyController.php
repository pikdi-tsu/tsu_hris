<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;

class HrPolicyController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('permission:admin:hr-policy:view');
    }

    /**
     * Halaman Rencana Strategis (Renstra) SDM
     */
    public function renstra()
    {
        return view('admin::hr-policy.renstra.index', [
            'title'    => 'Rencana Strategis (Renstra) SDM',
            'menu'     => 'hr-policy-renstra',
            'menuIcon' => 'fas fa-bullseye',
        ]);
    }

    /**
     * Halaman Peraturan Kepegawaian
     */
    public function peraturan()
    {
        return view('admin::hr-policy.peraturan.index', [
            'title'    => 'Peraturan Kepegawaian',
            'menu'     => 'hr-policy-peraturan',
            'menuIcon' => 'fas fa-gavel',
        ]);
    }

    /**
     * Halaman SOP SDM
     */
    public function sop()
    {
        return view('admin::hr-policy.sop.index', [
            'title'    => 'Standar Operasional Prosedur (SOP) SDM',
            'menu'     => 'hr-policy-sop',
            'menuIcon' => 'fas fa-clipboard-list',
        ]);
    }

    /**
     * Halaman Tugas Pokok dan Fungsi (Tupoksi)
     */
    public function tupoksi()
    {
        return view('admin::hr-policy.tupoksi.index', [
            'title'    => 'Tugas Pokok dan Fungsi (Tupoksi)',
            'menu'     => 'hr-policy-tupoksi',
            'menuIcon' => 'fas fa-id-badge',
        ]);
    }
}
