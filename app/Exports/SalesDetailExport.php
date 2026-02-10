<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;

class SalesDetailExport implements FromView
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        $list = DB::table('fm_sales_detail as d')
            ->join('fm_sales as s', 'd.sales_seq', '=', 's.seq')
            ->join('fm_order as o', 's.order_seq', '=', 'o.order_seq')
            ->leftJoin('fm_order_refund as r', 's.order_seq', '=', 'r.order_seq')
            ->where('d.sales_id', $this->id)
            ->select(
                'd.*', 
                's.seq as sales_seq', 's.order_seq', 's.price', 's.supply', 's.surtax', 's.order_date', 
                'o.step', 
                'r.refund_code'
            )
            ->orderBy('d.state', 'desc')
            ->orderBy('s.order_seq', 'asc')
            ->get();

        $dstate_str = [
            1 => '연동포함',
            0 => '연동제외'
        ];

        $list->transform(function($item) use ($dstate_str) {
            $item->dstate_str = $dstate_str[$item->state] ?? '';
            return $item;
        });

        return view('admin.order.sales.excel_detail', [
            'list' => $list
        ]);
    }
}
