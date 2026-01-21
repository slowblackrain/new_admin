<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seller; // Assuming Seller model represents providers

class ProviderController extends Controller
{
    public function catalog(Request $request)
    {
        $query = Seller::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('provider_id', 'like', "%{$keyword}%")
                  ->orWhere('provider_name', 'like', "%{$keyword}%");
            });
        }

        $providers = $query->orderBy('regdate', 'desc')->paginate(20);

        return view('admin.provider.catalog', compact('providers'));
    }
}
