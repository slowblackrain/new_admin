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
        $data = $cfg ? (json_decode($cfg->value, true) ?: unserialize($cfg->value)) : [];
        
        return view('admin.scm.basic.goods_int_set', compact('data'));
    }

    public function save_goods_int_set(Request $request)
    {
        $cfg = \App\Models\Common\FmConfig::firstOrNew(['codecd' => 'goods_int_set']);
        $data = $request->except('_token');
        $cfg->groupcd = 'config';
        $cfg->value = json_encode($data);
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
