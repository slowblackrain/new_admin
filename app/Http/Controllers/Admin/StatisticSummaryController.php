<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticSummaryController extends Controller
{
    // Sales Summary (Main)
    public function index(Request $request)
    {
        // Simple Sales Summary by Date
        // Legacy table: fm_order or fm_order_item
        // We'll aggregate from fm_order for now.

        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-7 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $query = DB::table('fm_order')
            ->select(
                DB::raw('DATE(regist_date) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(settleprice) as total_sales')
            )
            ->whereBetween('regist_date', [$startDate . " 00:00:00", $endDate . " 23:59:59"])
            ->groupBy('date')
            ->orderBy('date', 'desc');

        $sales = $query->get();

        return view('admin.statistic.summary.index', compact('sales', 'startDate', 'endDate'));
    }

    // Visitor Statistics
    public function visitor()
    {
        // Legacy often uses `fm_visitor_log` or similar.
        // Let's assume a simple placeholder or query if table exists.
        // Checking for fm_visitor?
        
        return view('admin.statistic.visitor.index');
    }
}
