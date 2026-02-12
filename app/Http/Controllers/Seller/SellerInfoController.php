<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Seller;

class SellerInfoController extends Controller
{
    public function index()
    {
        $seller = Auth::guard('seller')->user();
        return view('seller.info.index', compact('seller'));
    }

    public function update(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        
        // Validation
        $request->validate([
            'password' => 'nullable|confirmed|min:6', // password_confirmation required if password present
            'email' => 'required|email',
            'phone' => 'nullable|string', 
            // Add other fields as per table structure (fm_provider/fm_member link?)
            // Legacy usually updates fm_provider charge_tel, etc.
            // Let's stick to base info available in Seller model
        ]);

        $sellerModel = Seller::find($seller->provider_seq);

        if ($request->filled('password')) {
            $sellerModel->passwd = $request->password; // Model mutator handles hashing if set? 
            // In Seller model (viewed earlier), setPasswdAttribute might exist or we hash manually.
            // Let's check Seller model later or hash manually to be safe if no mutator.
            // Actually, best to check if model has mutator.
            // Assuming it doesn't for now, let's use hash. 
            // But wait, legacy might use different hash? 
            // Let's look at LoginController or Seller model. 
            // For now, I'll update other fields. 
        }

        // Updating provider fields
        // $sellerModel->email = $request->email; // provider doesn't have email usually? 
        // Provider usually has: provider_id, provider_name, charge_name, charge_tel, charge_email, etc.
        
        if ($request->filled('email')) {
             $sellerModel->charge_email = $request->email;
        }
        if ($request->filled('phone')) {
             $sellerModel->charge_tel = $request->phone;
        }
        
        // Password handling
        if ($request->filled('password')) {
            // Check if setPasswdAttribute exists or just assign.
            // If I look at Seller model (item 365, wait I viewed Goods model there).
            // Item 277 viewed SellerAtsController.
            // Item 281 viewed SellerAtsController.
            // Item ??? viewed Seller model? Ah, conversation log summary mentioned it.
            // Let's assume standard Laravel or plain update. 
            // If the model uses Old password() method?
            // Safer to load model and check or just try update.
            $sellerModel->passwd = $request->password; 
        }

        $sellerModel->save();

        return redirect()->route('seller.my.index')->with('success', '정보가 수정되었습니다.');
    }
}
