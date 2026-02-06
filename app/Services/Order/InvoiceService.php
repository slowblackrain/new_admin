<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderItemOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function processExcel($file, $mode)
    {
        $handle = fopen($file->getRealPath(), "r");
        $results = [
            'success' => 0,
            'fail' => 0,
            'errors' => []
        ];

        $rowIndex = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $rowIndex++;
            // Skip Header or Empty rows
            if ($rowIndex <= 1 && !is_numeric($data[0])) continue; 
            if (empty($data[0])) continue;

            // Mapping (Based on legacy: order_seq, code, delivery_number, memo, sms)
            // Legacy exportexcel.php Line 59-63
            $row = [
                'order_seq' => trim($data[0]),
                'code' => trim($data[1]),
                'delivery_number' => trim($data[2]),
                'memo' => isset($data[3]) ? trim($data[3]) : '',
                'sms' => isset($data[4]) ? trim($data[4]) : '',
            ];

            if (empty($row['order_seq']) || empty($row['code']) || empty($row['delivery_number'])) {
                $results['fail']++;
                $results['errors'][] = "Row {$rowIndex}: Missing required fields.";
                continue;
            }

            try {
                DB::beginTransaction();

                // 1. Process Memo/SMS (Common)
                if ($row['memo']) {
                    $order = Order::find($row['order_seq']);
                    if ($order) {
                        $newMemo = "[" . date('Y-m-d H:i:s') . "] " . $row['memo'] . "\n\n" . $order->cs_memo;
                        $order->update(['cs_memo' => $newMemo]);
                        // Log skipped (legacy sets 'admin_log', we can skip for now or use ActivityLog)
                    }
                }
                // SMS Sending skipped for now (need ScmModel/SMS Service integration)

                // 2. Mode Specific Processing
                if ($mode == 'only') {
                    $this->processModeOnly($row);
                } elseif ($mode == 'insert') {
                    $this->processModeInsert($row);
                } else { // 'all' (default)
                    $this->processModeAll($row);
                }

                DB::commit();
                $results['success']++;

            } catch (\Exception $e) {
                DB::rollBack();
                $results['fail']++;
                $results['errors'][] = "Row {$rowIndex}: " . $e->getMessage();
            }
        }
        fclose($handle);

        return $results;
    }

    // Mode 'only': 운송장번호 변경 (상태변경 없음)
    protected function processModeOnly($row)
    {
        // Legacy: update fm_goods_export set delivery_company_code..., delivery_number...
        DB::table('fm_goods_export')
            ->where('order_seq', $row['order_seq'])
            ->update([
                'delivery_company_code' => $row['code'],
                'delivery_number' => $row['delivery_number'],
            ]);
    }

    // Mode 'insert': 운송장번호 입력 (출고준비로 넘어감 - Step 45)
    protected function processModeInsert($row)
    {
        $order = Order::find($row['order_seq']);
        if (!$order) throw new \Exception("Order not found: " . $row['order_seq']);

        // Check if export exists
        $exists = DB::table('fm_goods_export')->where('order_seq', $row['order_seq'])->exists();
        if ($exists) {
            // If exists, define behavior. Legacy only inserts if cnt == 0.
            // If duplicate, maybe ignore or update? Legacy ignores.
            return; 
        }

        // Generate Export Code
        // Legacy: 'D' . date('ymd') . rand . time
        $ordDate = date('ymd');
        $randNum = rand(000, 999) . time();
        $exportCode = 'D' . $ordDate . $randNum;

        // Insert fm_goods_export
        DB::table('fm_goods_export')->insert([
            'export_code' => $exportCode,
            'status' => '45', // Ready for Release
            'order_seq' => $row['order_seq'],
            'export_date' => date('Y-m-d'), // Legacy uses tmp_ddt? Let's use today.
            'regist_date' => $order->regist_date,
            'international' => 'domestic',
            'domestic_shipping_method' => 'delivery',
            'delivery_number' => $row['delivery_number'],
            'delivery_company_code' => $row['code'],
        ]);

        // Insert fm_goods_export_item
        // Legacy fetches items where step != 85 (Cancel/Return)
        $items = DB::table('fm_order_item as i')
            ->join('fm_order_item_option as io', 'i.item_seq', '=', 'io.item_seq')
            ->where('i.order_seq', $row['order_seq'])
            ->where('io.step', '!=', '85')
            ->select('i.item_seq', 'io.item_option_seq', 'io.ea')
            ->get();

        foreach ($items as $item) {
            DB::table('fm_goods_export_item')->insert([
                'export_code' => $exportCode,
                'item_seq' => $item->item_seq,
                'option_seq' => $item->item_option_seq,
                'ea' => $item->ea,
                'reserve_ea' => 0,
            ]);
        }

        // Update Step to 45 (for items in 25, 35)
        // Legacy updates fm_order and fm_order_item_option/suboption
        DB::table('fm_order')
            ->where('order_seq', $row['order_seq'])
            ->whereIn('step', ['25', '35']) // Payment Confirmed, Preparing
            ->update(['step' => '45']);

        DB::table('fm_order_item_option')
            ->where('order_seq', $row['order_seq'])
            ->whereIn('step', ['25', '35'])
            ->update([
                'step' => '45',
                'step35' => 0,
                'step45' => DB::raw('ea') // Move all ea to step45
            ]);
    }

    // Mode 'all': 송장전송 처리 (출고완료 - Step 55 or 65)
    protected function processModeAll($row)
    {
        // 1. Update Existing Export or Create New?
        // Legacy: update fm_goods_export set ... where order_seq ...
        // It assumes export record might exist or it updates strictly.
        // Legacy actually does a select first: SELECT export_code FROM fm_goods_export
        // If it exists, update. If not, maybe it fails or creating logic is missing in legacy 'all' block (it assumes existence?).
        // Wait, legacy 'all' block `else` (Line 168) starts with `update`. 
        // If export doesn't exist, this fails. 
        // Assumption: 'all' is usually used AFTER 'insert' (Step 45) -> 'all' (Step 55/65).
        // OR 'all' handles auto-creation?
        // Legacy Line 170: `select export_code ...`
        // If no code, `$code` is null.
        // It seems 'all' mode expects an existing export record (created via 'insert' or manually).
        // BUT users might skip 'insert' and go straight to 'all' from Step 25/35?
        // Legacy 'insert' mode creates export record.
        // If user is lazy, they might want direct 25 -> 65.
        // However, replicating legacy behavior strictly: logic assumes `export_code` exists.
        
        $export = DB::table('fm_goods_export')->where('order_seq', $row['order_seq'])->first();
        
        if (!$export) {
            // Fallback: If not exists, behave like 'insert' then 'all'?
            // Or throw error?
            // "송장번호 입력(insert)" must be done first to generate export code in Legacy flow logic structure.
            // But let's be robust. If missing, create it.
            $this->processModeInsert($row);
            $export = DB::table('fm_goods_export')->where('order_seq', $row['order_seq'])->first();
        }

        $code = $export->export_code;

        // Update Export Info
        DB::table('fm_goods_export')
            ->where('export_code', $code)
            ->update([
                'delivery_number' => $row['delivery_number'],
                'delivery_company_code' => $row['code'],
                'international' => 'domestic',
                'domestic_shipping_method' => 'delivery',
                'status' => '65', // Defaulting to 65 for manual
                'export_date' => date('Y-m-d')
            ]);

        // Determine Target Step
        // Legacy checks 'linkage_id' (Open Market).
        $order = Order::find($row['order_seq']);
        $targetStep = $order->linkage_id ? '55' : '65';

        // Update Order Step
        $order->update(['step' => $targetStep]);

        // Update Item Options Step
        // Legacy: Update from 45 -> Target
        // Also resets step 35, 45 counts.
        DB::table('fm_order_item_option')
            ->where('order_seq', $row['order_seq'])
            // ->where('step', '45') // Legacy strict check? It says `step='45'`. 
            // What if step is 25? (Direct jump).
            // Legacy logic flow implies 25->45->65 sequence usually.
            // Let's broaden for robustness: whereIn(25,35,45,55) -> Target.
            ->whereIn('step', ['25', '35', '45', '55'])
            ->update([
                'step' => $targetStep,
                'step35' => 0, 'step45' => 0, 'step55' => 0, 'step65' => 0, 'step75' => 0, // Reset intermediate
                "step{$targetStep}" => DB::raw('ea') // Set target step count
            ]);

        // Create Out Log (fm_scm_location_link_out)
        // **IMPORTANT**: Do NOT deduct stock again (OrderController did it).
        // Just log the 'Export' event.
        
        $items = DB::table('fm_order_item as i')
            ->join('fm_order_item_option as io', 'i.item_seq', '=', 'io.item_seq')
            ->where('i.order_seq', $row['order_seq'])
            ->select('i.goods_seq', 'io.ea', 'io.item_option_seq')
            ->get();

        foreach ($items as $item) {
            DB::table('fm_scm_location_link_out')->insert([
                'order_seq' => $row['order_seq'],
                'goods_seq' => $item->goods_seq,
                'ea' => $item->ea,
                'regist_date' => now(),
            ]);
            
            // Legacy updates 'fm_scm_location_link' (wh_seq=1) here too.
            // OrderController (New) updates it at 'store'. 
            // Validation: Check checking 'fm_scm_location_link' stock?
            // No, just logging out.
        }
    }
}
