<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Order\InvoiceService;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function excel_index()
    {
        return view('admin.order.invoice.excel');
    }

    public function excel_upload(Request $request)
    {
        $request->validate([
            'export_excel_file' => 'required|file|mimes:csv,txt',
            'mode' => 'required|in:all,only,insert',
        ]);

        $file = $request->file('export_excel_file');
        $mode = $request->input('mode');

        $result = $this->invoiceService->processExcel($file, $mode);

        $msg = "처리 완료: 성공 {$result['success']}건, 실패 {$result['fail']}건";
        if (!empty($result['errors'])) {
            $msg .= "\\n[에러 내역]\\n" . implode("\\n", array_slice($result['errors'], 0, 5));
            if (count($result['errors']) > 5) $msg .= "\\n...외 " . (count($result['errors']) - 5) . "건";
        }

        return redirect()->back()->with('alert', $msg);
    }
}
