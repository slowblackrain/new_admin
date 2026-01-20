<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $orderCount = \App\Models\Order::count();
        $memberCount = \App\Models\Member::count();

        return view('admin.dashboard', compact('orderCount', 'memberCount'));
    }
}
