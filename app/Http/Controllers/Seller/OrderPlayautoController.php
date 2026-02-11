<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Goods;

class OrderPlayautoController extends Controller
{
    protected $excelService;

    public function __construct(\App\Services\Order\OrderExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function catalog(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $providerSeq = $seller->provider_seq;

        $query = Order::query();

        // Join Items to filter by provider
        $query->whereHas('items', function($q) use ($providerSeq) {
            $q->where('provider_seq', $providerSeq);
        });

        // Basic Filters derived from legacy
        // Step > 15 (Paid/Deposited)
        // Usually list shows steps 25 (Payment Confirmed) to 85 (Purchase Confirmed)
        // Legacy: if param 'chk_step' is not set, it might default.
        // For now, let's show all relevant orders (step >= 25)
        // $query->where('step', '>=', 25); 
        
        $query->orderBy('regist_date', 'desc');

        // Search Filters
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('order_seq', 'like', "%{$keyword}%")
                  ->orWhere('order_user_name', 'like', "%{$keyword}%")
                  ->orWhere('order_email', 'like', "%{$keyword}%")
                  ->orWhere('recipient_user_name', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('regist_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('seller.order_playauto.catalog', compact('orders'));
    }

    public function excelupload()
    {
        return view('seller.order_playauto.excelupload');
    }

    public function excelupload_process(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        list($orderData, $result_error) = $this->excelService->excel_upload($path, 'check');

        return view('seller.order_playauto.excelupload', compact('orderData', 'result_error'));
    }

    public function excelupload_store(Request $request)
    {
        $orders = $request->input('orders');
        if (!$orders || !is_array($orders)) {
            return back()->with('error', '주문 데이터가 유효하지 않습니다.');
        }

        $seller = \Illuminate\Support\Facades\Auth::guard('seller')->user();
        $result = $this->excelService->create_orders($orders, $seller);

        if ($result['success']) {
            return redirect()->route('seller.order.catalog')->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }
}
