<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    // Basic Environment Settings (Dashboard or Index)
    public function index()
    {
        return view('admin.setting.index');
    }

    // Shop Basic Info (Company, CEO, Etc)
    public function basic()
    {
        // Legacy: fm_config table usually stores this.
        // We might need to fetch existing config.
        $config = DB::table('fm_config')->first(); 
        return view('admin.setting.basic', compact('config'));
    }

    // Operating/Sales Policy
    public function operating()
    {
        return view('admin.setting.operating');
    }

    // PG/Shipping Settings
    public function pg()
    {
        return view('admin.setting.pg');
    }

    // Member/Points Settings
    public function member()
    {
        return view('admin.setting.member');
    }

    // Security/Admin Settings
    public function protect()
    {
        return view('admin.setting.protect');
    }

    // Save Basic Info
    public function save_basic(Request $request)
    {
        // Implementation for saving basic info
        // Not implementing deep logic yet, just redirection for now.
        return back()->with('success', '기본 정보가 저장되었습니다. (구현 예정)');
    }
}
