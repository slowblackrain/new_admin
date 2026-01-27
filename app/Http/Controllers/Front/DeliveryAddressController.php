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
            return redirect()->route('member.login');
        }

        $addresses = DeliveryAddress::currentUser()
            ->orderBy('default', 'desc')
            ->orderBy('regist_date', 'desc')
            ->get();

        return view('front.mypage.delivery_address', compact('addresses'));
    }

    public function create()
    {
        return view('front.mypage.delivery_address_register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_user_name' => 'required',
            'recipient_mobile' => 'required',
            'recipient_zipcode' => 'required',
            'recipient_address' => 'required',
        ]);

        $data = $request->all();
        $data['member_seq'] = Auth::id();
        $data['regist_date'] = now();
        $data['update_date'] = now();

        // Handle Default
        if ($request->has('default') && $request->default == 'Y') {
            DeliveryAddress::currentUser()->update(['default' => 'N']);
        } else {
            $data['default'] = 'N';
        }

        DeliveryAddress::create($data);

        return redirect()->route('mypage.delivery_address.index')->with('success', '배송지가 등록되었습니다.');
    }

    public function edit($id)
    {
        $address = DeliveryAddress::currentUser()->findOrFail($id);
        return view('front.mypage.delivery_address_register', compact('address'));
    }

    public function update(Request $request, $id)
    {
        $address = DeliveryAddress::currentUser()->findOrFail($id);

        $data = $request->all();
        $data['update_date'] = now();

        // Handle Default
        if ($request->has('default') && $request->default == 'Y') {
            DeliveryAddress::currentUser()->update(['default' => 'N']);
        } else {
            $data['default'] = 'N';
        }

        $address->update($data);

        return redirect()->route('mypage.delivery_address.index')->with('success', '배송지가 수정되었습니다.');
    }

    public function destroy($id)
    {
        $address = DeliveryAddress::currentUser()->findOrFail($id);
        $address->delete();

        return back()->with('success', '배송지가 삭제되었습니다.');
    }

    // JSON API for Order Form
    public function listJson()
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => '로그인이 필요합니다.'], 401);
        }

        $addresses = DeliveryAddress::currentUser()
            ->orderBy('default', 'desc')
            ->orderBy('regist_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $addresses
        ]);
    }
}
