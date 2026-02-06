<?php

namespace App\Services\Goods;

use App\Models\GoodsSet;
use App\Models\Goods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GoodsSetService
{
    /**
     * Get list of Main Sets (main_seq = 0)
     */
    public function getMainSets($keyword = null)
    {
        $query = GoodsSet::with(['goods.defaultOption', 'goods.images'])
            ->where('main_seq', 0);

        if ($keyword) {
            $query->whereHas('goods', function ($q) use ($keyword) {
                $q->where('goods_scode', 'like', "%{$keyword}%")
                  ->orWhere('goods_name', 'like', "%{$keyword}%");
            });
        }

        // Calculate child count for each set
        $query->withCount(['goods as child_count' => function ($q) {
             // This is tricky. We need to count records in fm_goods_set where main_seq = THIS goods_seq
             // But 'goods' relationship points to fm_goods.
        }]);
        
        // Actually, let's just do a join or subquery for count.
        // Laravel's self-join relation approach is cleaner.
        
        $sets = $query->orderBy('set_seq', 'desc')->paginate(20);

        // Populate child counts manually or via relation if we define it properly.
        foreach ($sets as $set) {
            $set->child_count = GoodsSet::where('main_seq', $set->goods_seq)->count();
        }

        return $sets;
    }

    /**
     * Get children of a specific Set
     */
    public function getSetChildren($parentGoodsSeq)
    {
        return GoodsSet::with(['goods.defaultOption', 'goods.images'])
            ->where('main_seq', $parentGoodsSeq)
            ->get();
    }

    /**
     * Add a Set (Main) or Component (Child)
     */
    public function add($mainSeq, $goodsSeq, $ea = 1)
    {
        // Duplicate Check
        $exists = GoodsSet::where('main_seq', $mainSeq)
            ->where('goods_seq', $goodsSeq)
            ->exists();

        if ($exists) {
            return ['success' => false, 'message' => 'Double'];
        }

        $calc = ($mainSeq == 0) ? 'y' : 'n';

        GoodsSet::create([
            'main_seq' => $mainSeq,
            'goods_seq' => $goodsSeq,
            'goods_ea' => $ea,
            'ea_calc' => $calc,
            'manager' => Auth::guard('admin')->user()->mname ?? 'system', // Replace with actual manager name logic
            'regdate' => now(),
        ]);

        return ['success' => true, 'message' => 'OK'];
    }

    /**
     * Remove a Set Item
     */
    public function remove($setSeq)
    {
        $set = GoodsSet::find($setSeq);
        if (!$set) {
            return ['success' => false, 'message' => 'NO'];
        }

        $set->delete();
        return ['success' => true, 'message' => 'OK'];
    }
}
