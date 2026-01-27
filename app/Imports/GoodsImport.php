<?php

namespace App\Imports;

use App\Models\Goods;
use App\Models\GoodsOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GoodsImport implements ToCollection, WithHeadingRow
{
    protected $mode; // 'regist' or 'update'

    public function __construct($mode = 'regist')
    {
        $this->mode = $mode;
    }

    public function collection(Collection $rows)
    {
        // Chunking handled by caller or default (imports whole file)
        foreach ($rows as $row) {
            if ($this->mode == 'update') {
                $this->updateGoods($row);
            } else {
                $this->registerGoods($row);
            }
        }
    }

    protected function registerGoods($row)
    {
        // Simple MVP Registration
        // Requires 'goods_name', 'price' etc.
        // Skipping complex logic for MVP. Focus on update first as per task.
    }

    protected function updateGoods($row)
    {
        // Mandate 'goods_code' or 'seq'
        $seq = $row['seq'] ?? null;
        $code = $row['goods_code'] ?? null; // using header names

        if (!$seq && !$code) return;

        $query = DB::table('fm_goods');
        if ($seq) $query->where('goods_seq', $seq);
        else $query->where('goods_code', $code);

        $goods = $query->first();
        if (!$goods) return;

        // Fields to Update
        $updateData = [];
        if (isset($row['price'])) $updateData['sale_price'] = $row['price'];
        if (isset($row['status'])) {
             // Map Status logic
             $statusMap = ['정상'=>'normal','품절'=>'runout','판매중지'=>'stop','판매종료'=>'unsold'];
             $st = $statusMap[$row['status']] ?? $row['status'];
             $updateData['goods_status'] = $st;
        }

        if (!empty($updateData)) {
            DB::table('fm_goods')->where('goods_seq', $goods->goods_seq)->update($updateData);
        }

        // Option Update (Price)
        if (isset($row['price']) || isset($row['supply_price'])) {
            $optUpdate = [];
            if (isset($row['price'])) {
                $optUpdate['price'] = $row['price'];
                $optUpdate['consumer_price'] = $row['price'] * 1.3; // Dummy logic if missing
            }
            if (isset($row['supply_price'])) $optUpdate['provider_price'] = $row['supply_price'];

            if (!empty($optUpdate)) {
                DB::table('fm_goods_option')
                    ->where('goods_seq', $goods->goods_seq)
                    ->where('default_option', 'y')
                    ->update($optUpdate);
            }
        }
        
        // Stock Update
        if (isset($row['stock'])) {
             DB::table('fm_goods_supply')
                ->where('goods_seq', $goods->goods_seq)
                ->update(['stock' => $row['stock'], 'total_stock' => $row['stock']]);
        }
    }
}
