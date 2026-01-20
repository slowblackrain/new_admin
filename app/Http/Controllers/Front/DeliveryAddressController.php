<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryAddress;
use Illuminate\Support\Facades\Auth;

class DeliveryAddressController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => '로그인이 필요합니다.'], 401);
        }

        $addresses = DeliveryAddress::currentUser()
            ->orderBy('default', 'desc') // Default address first
            ->orderBy('regist_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $addresses
        ]);
    }
}
