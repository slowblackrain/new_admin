<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the affiliate dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('affiliate.dashboard');
    }
}
