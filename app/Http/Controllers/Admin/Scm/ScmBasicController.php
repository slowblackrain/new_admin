<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scm\ScmTrader;
use Illuminate\Support\Facades\DB;

class ScmBasicController extends Controller
{
    // 기본설정 (Inventory Basics / Config)
    public function config()
    {
        // Load SCM Config
        $scmCfg = \App\Models\Common\FmConfig::find('scm');
        $scmData = $scmCfg ? json_decode($scmCfg->value, true) : [];
        if (!$scmData && $scmCfg) {
             // Try unserialize if json fails (Legacy might be serialized)
             $scmData = @unserialize($scmCfg->value);
        }

        // Load Exchange Config
        $exchangeCfg = \App\Models\Common\FmConfig::find('exchange');
        $exchangeData = $exchangeCfg ? json_decode($exchangeCfg->value, true) : [];
         if (!$exchangeData && $exchangeCfg) {
             $exchangeData = @unserialize($exchangeCfg->value);
        }

        return view('admin.scm.basic.config', compact('scmData', 'exchangeData'));
    }

    public function save_config(Request $request)
    {
        // Save SCM Config (Truncation, Base Date)
        $scmCfg = \App\Models\Common\FmConfig::firstOrNew(['codecd' => 'scm']);
        $currentScmData = $scmCfg->exists ? (json_decode($scmCfg->value, true) ?: unserialize($scmCfg->value)) : [];
        
        $newScmData = array_merge($currentScmData ?: [], [
            'set_default_date' => $request->scm_setting_default_date,
            'set_account_date' => $request->scm_setting_account_date,
            'truncation_unit' => $request->truncation_unit, // 10, 100
        ]);
        
        $scmCfg->groupcd = 'config'; // Default group
        $scmCfg->value = json_encode($newScmData);
        $scmCfg->save();

        // Save Exchange Config
        $exchangeCfg = \App\Models\Common\FmConfig::firstOrNew(['codecd' => 'exchange']);
        
        $exchangeData = $request->exchange ?: [];
        $exchangeCfg->groupcd = 'config';
        $exchangeCfg->value = json_encode($exchangeData);
        $exchangeCfg->save();

        return redirect()->back()->with('success', '설정이 저장되었습니다.');
    }

    // 기본단가요율설정 (Goods Interest Rate Setting)
    public function goods_int_set()
    {
        $cfg = \App\Models\Common\FmConfig::find('goods_int_set');
        // Legacy Config is Pipe-Delimited String, not JSON.
        // Format: val1|val2^val1|val2...
        $rawData = $cfg ? $cfg->value : '';
        $data = [];

        if ($rawData) {
            // Check if it's serialized (some legacy configs are)
            $maybeArr = @unserialize($rawData);
            if ($maybeArr !== false) {
                $data = $maybeArr;
            } else {
                // Determine if it is legacy array-like config or the Goods Int Set specific format
                // Legacy goods_int_set is actually a serialized array containing keys like 'x_list', 'g_list', etc.
                // Let's look at legacy lines 76-78: $this->cfg_goods_int_set = config_load('goods_int_set');
                
                // If config_load returns an array, then the DB value is serialized.
                // Let's assume it's serialized for the specific keys, and valid strings inside.
                $data = @unserialize($rawData); // Should return array of 'x_list', 'g_list' etc.
            }
        }

        // Parse Lists from Data
        $xt_list = [];
        if (!empty($data['x_list'])) {
            foreach (explode('^', $data['x_list']) as $v) {
                $p = explode('|', $v);
                $xt_list[] = [
                    'xs' => $p[0] ?? 0, 'xe' => $p[1] ?? 0,
                    'xt_one_ea' => $p[2] ?? 1, 'xt_two_ea' => $p[3] ?? 1,
                    'xt_three_ea' => $p[4] ?? 1, 'xt_four_ea' => $p[5] ?? 1,
                ];
            }
        }

        $gtx_list = []; // GTS, GKS
        if (!empty($data['g_list'])) {
            foreach (explode('^', $data['g_list']) as $v) {
                $p = explode('|', $v);
                $gtx_list[] = [
                    's1' => $p[0] ?? 0, 'e1' => $p[1] ?? 0,
                    'one_ea' => $p[2] ?? 1, 'two_ea' => $p[3] ?? 1,
                    'three_ea' => $p[4] ?? 1, 'four_ea' => $p[5] ?? 1,
                ];
            }
        }

        $at_list = []; // ATS
        if (!empty($data['at_list'])) {
            foreach (explode('^', $data['at_list']) as $v) {
                $p = explode('|', $v);
                $at_list[] = [
                    'as' => $p[0] ?? 0, 'ae' => $p[1] ?? 0,
                    'at_dc' => $p[2] ?? 0,
                ];
            }
        }

        $gt_list = []; // GT
        if (!empty($data['gt_list'])) {
            foreach (explode('^', $data['gt_list']) as $v) {
                $p = explode('|', $v);
                $gt_list[] = [
                    'gs' => $p[0] ?? 0, 'ge' => $p[1] ?? 0,
                    'gt_dc1' => $p[2] ?? 0, 'gt_dc2' => $p[3] ?? 0, 'gt_dc3' => $p[4] ?? 0,
                ];
            }
        }

        return view('admin.scm.basic.goods_int_set', compact('data', 'xt_list', 'gtx_list', 'at_list', 'gt_list'));
    }

    public function save_goods_int_set(Request $request)
    {
        // Reconstruct logic to match legacy format (pipe-delimited implementation)
        $input = $request->all();

        // 1. XT List
        $x_list_arr = [];
        if (isset($input['xs'])) {
            foreach ($input['xs'] as $k => $v) {
                $x_list_arr[] = implode('|', [
                    $v, $input['xe'][$k],
                    $input['xt_one_ea'][$k], $input['xt_two_ea'][$k],
                    $input['xt_three_ea'][$k], $input['xt_four_ea'][$k]
                ]);
            }
        }
        $x_list_str = implode('^', $x_list_arr);

        // 2. GTX List (g_list)
        $g_list_arr = [];
        if (isset($input['s1'])) {
            foreach ($input['s1'] as $k => $v) {
                $g_list_arr[] = implode('|', [
                    $v, $input['e1'][$k],
                    $input['one_ea'][$k], $input['two_ea'][$k],
                    $input['three_ea'][$k], $input['four_ea'][$k]
                ]);
            }
        }
        $g_list_str = implode('^', $g_list_arr);

        // 3. AT List
        $at_list_arr = [];
        if (isset($input['as'])) {
            foreach ($input['as'] as $k => $v) {
                $at_list_arr[] = implode('|', [
                    $v, $input['ae'][$k], $input['at_dc'][$k]
                ]);
            }
        }
        $at_list_str = implode('^', $at_list_arr);

        // 4. GT List
        $gt_list_arr = [];
        if (isset($input['gs'])) {
            foreach ($input['gs'] as $k => $v) {
                $gt_list_arr[] = implode('|', [
                    $v, $input['ge'][$k],
                    $input['gt_dc1'][$k], $input['gt_dc2'][$k], $input['gt_dc3'][$k]
                ]);
            }
        }
        $gt_list_str = implode('^', $gt_list_arr);

        // Construct Data Array
        $data = [
            'price' => $input['price'] ?? 0,
            'order_cnt' => $input['order_cnt'] ?? 0,
            'order_ea' => $input['order_ea'] ?? 0,
            'view' => $input['view'] ?? 0,
            'exchange' => $input['exchange'] ?? 0,
            'transport' => $input['transport'] ?? 0,
            'customs' => $input['customs'] ?? 0,
            'incidental' => $input['incidental'] ?? 0,
            // Step Rates
            'step_one' => $input['step_one'] ?? 0, 'gtk_step_one' => $input['gtk_step_one'] ?? 0, 'xt_step_one' => $input['xt_step_one'] ?? 0,
            'step_two' => $input['step_two'] ?? 0, 'gtk_step_two' => $input['gtk_step_two'] ?? 0, 'xt_step_two' => $input['xt_step_two'] ?? 0,
            'step_three' => $input['step_three'] ?? 0, 'gtk_step_three' => $input['gtk_step_three'] ?? 0, 'xt_step_three' => $input['xt_step_three'] ?? 0,
            'step_four' => $input['step_four'] ?? 0, 'gtk_step_four' => $input['gtk_step_four'] ?? 0, 'xt_step_four' => $input['xt_step_four'] ?? 0,
            'step_ad' => $input['step_ad'] ?? 0,
            // Lists
            'x_list' => $x_list_str,
            'g_list' => $g_list_str,
            'at_list' => $at_list_str,
            'gt_list' => $gt_list_str,
        ];

        $cfg = \App\Models\Common\FmConfig::firstOrNew(['codecd' => 'goods_int_set']);
        $cfg->groupcd = 'config';
        $cfg->value = serialize($data); // Legacy uses serialize for the main array
        $cfg->save();

        return redirect()->back()->with('success', '요율 설정이 저장되었습니다.');
    }

    public function warehouse_list(Request $request)
    {
        $query = \App\Models\Scm\ScmWarehouse::query();

        if ($request->keyword) {
            $query->where('wh_name', 'like', "%{$request->keyword}%");
        }
        if ($request->wh_group) {
            $query->where('wh_group', $request->wh_group);
        }

        $warehouses = $query->orderBy('wh_seq', 'desc')->paginate(20);
        $whGroup = \App\Models\Scm\ScmWarehouse::whereNotNull('wh_group')->groupBy('wh_group')->pluck('wh_group');

        return view('admin.scm.basic.warehouse.list', compact('warehouses', 'whGroup'));
    }

    public function warehouse_form(Request $request)
    {
        $warehouse = null;
        if ($request->seq) {
            $warehouse = \App\Models\Scm\ScmWarehouse::find($request->seq);
        }
        $whGroup = \App\Models\Scm\ScmWarehouse::whereNotNull('wh_group')->groupBy('wh_group')->pluck('wh_group');
        return view('admin.scm.basic.warehouse.form', compact('warehouse', 'whGroup'));
    }

    public function warehouse_save(Request $request)
    {
        $data = $request->except('_token', 'wh_seq', 'manager');
        
        // Location logic simplified for Migration First: Just saving basic info
        // Legacy created locations (fm_scm_location) based on width/length/height
        // We will implement full location generation later or if requested.
        
        if ($request->wh_seq) {
            $data['wh_modify_date'] = now();
            \App\Models\Scm\ScmWarehouse::where('wh_seq', $request->wh_seq)->update($data);
        } else {
            $data['wh_regist_date'] = now();
            \App\Models\Scm\ScmWarehouse::create($data);
        }

        return redirect()->route('admin.scm_basic.warehouse')->with('success', '창고 정보가 저장되었습니다.');
    }

    public function store_list(Request $request)
    {
        $query = \App\Models\Scm\ScmStore::query();

        if ($request->keyword) {
            $query->where('store_name', 'like', "%{$request->keyword}%");
        }
        if ($request->store_type) {
            $query->where('store_type', $request->store_type);
        }

        $stores = $query->orderBy('store_seq', 'desc')->paginate(20);

        return view('admin.scm.basic.store.list', compact('stores'));
    }

    public function store_form(Request $request)
    {
        $store = null;
        if ($request->seq) {
            $store = \App\Models\Scm\ScmStore::find($request->seq);
        }
        // In legacy, store_regist also loads Warehouses to link them (fm_scm_store_warehouse)
        // For Migration First (Parity), we should list warehouses available to link.
        // We will pass the list for now. Linking logic implementation can be refined.
        $warehouses = \App\Models\Scm\ScmWarehouse::all();
        
        return view('admin.scm.basic.store.form', compact('store', 'warehouses'));
    }

    public function store_save(Request $request)
    {
        $data = $request->except('_token', 'store_seq', 'manager', 'wh_seq');
        
        if ($request->store_seq) {
            $data['modify_date'] = now();
            \App\Models\Scm\ScmStore::where('store_seq', $request->store_seq)->update($data);
            $storeSeq = $request->store_seq;
        } else {
            $data['regist_date'] = now();
            $store = \App\Models\Scm\ScmStore::create($data);
            $storeSeq = $store->store_seq;
        }

        return redirect()->route('admin.scm_basic.store')->with('success', '매장 정보가 저장되었습니다.');
    }

    public function trader_list(Request $request)
    {
        $query = ScmTrader::query();

        if ($request->keyword) {
            $query->where('trader_name', 'like', "%{$request->keyword}%");
        }

        if ($request->trader_group) {
            $query->where('trader_group', $request->trader_group);
        }

        $traders = $query->orderBy('trader_seq', 'desc')->paginate(20);

        return view('admin.scm.trader.list', compact('traders'));
    }

    public function trader_form(Request $request)
    {
        $trader = null;
        if ($request->seq) {
            $trader = ScmTrader::find($request->seq);
        }
        return view('admin.scm.trader.form', compact('trader'));
    }

    public function trader_save(Request $request)
    {
        $data = $request->except('_token', 'trader_seq');
        
        if ($request->trader_seq) {
            $data['modify_date'] = now();
            ScmTrader::where('trader_seq', $request->trader_seq)->update($data);
        } else {
            $data['regist_date'] = now();
            ScmTrader::create($data);
        }

        return redirect()->route('admin.scm_basic.trader')->with('success', '거래처 정보가 저장되었습니다.');
    }
}
