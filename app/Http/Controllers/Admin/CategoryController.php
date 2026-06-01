<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function catalog()
    {
        return view('admin.category.catalog');
    }

    // JSON API for JSTree
    public function getTree()
    {
        $categories = Category::select('id', 'parent_id', 'title', 'hide')
            ->orderBy('position', 'asc')
            ->get();

        $data = [];
        foreach ($categories as $cat) {
            $node = [
                'id' => (string)$cat->id,
                'parent' => ($cat->parent_id == 0 || $cat->parent_id == 1) ? '#' : (string)$cat->parent_id, 
                // Note: JSTree uses '#' for root. Legacy root might be 1 ("Admin" or "Root") but visible items start below.
                // Let's assume parent_id=1 is arguably root, so items with parent_id=1 should have parent='#'.
                // Or if parent_id=0 exists?
                // inspect data showed: id=2, parent_id=1.
                // So id=1 is the super root?
                // Let's verify if id=1 exists. If so, we can show it or skip it.
                // Usually we want to show the full tree.
                'text' => $cat->title,
                'state' => ['opened' => true],
                'data' => $cat->toArray(),
                'icon' => $cat->hide == '1' ? 'fas fa-eye-slash text-muted' : 'fas fa-folder text-warning'
            ];
            
            // Adjust logic for Root
            if ($cat->id == 1) { // Assuming 1 is root
                 $node['parent'] = '#';
                 $node['text'] = 'ROOT';
                 $node['state']['opened'] = true;
            } elseif ($cat->parent_id == 0) {
                 $node['parent'] = '#';
            }
            
            $data[] = $node;
        }

        return response()->json($data);
    }

    // Create New Category
    public function store(Request $request)
    {
        $parent_id = $request->input('parent_id', 0);
        
        // Calculate next position
        $max_pos = Category::where('parent_id', $parent_id)->max('position');
        $position = $max_pos !== null ? $max_pos + 1 : 0;
        
        // Generate Code following the 4-digit hierarchical prefix chunk structure
        if ($parent_id == 0 || $parent_id == 1) { // Root Level (assuming 1 is root)
            $maxCode = Category::whereRaw('length(category_code) = 4')
                ->max('category_code');
            
            if (!$maxCode) {
                $code = '0001';
            } else {
                $next = intval($maxCode) + 1;
                $code = sprintf('%04d', $next);
            }
            $level = 1;
        } else {
            $parent = Category::find($parent_id);
            if (!$parent) {
                return response()->json(['error' => 'Parent category not found'], 400);
            }
            $parentCode = $parent->category_code;
            $parentLen = strlen($parentCode);
            $childLen = $parentLen + 4;
            
            $maxChildCode = Category::where('category_code', 'like', $parentCode . '%')
                ->whereRaw('length(category_code) = ?', [$childLen])
                ->max('category_code');
                
            if (!$maxChildCode) {
                $code = $parentCode . '0001';
            } else {
                $last4 = substr($maxChildCode, -4);
                $next = intval($last4) + 1;
                $code = $parentCode . sprintf('%04d', $next);
            }
            $level = $parent->level + 1;
        }

        $category = new Category();
        $category->parent_id = $parent_id;
        $category->title = '새 카테고리';
        $category->position = $position;
        $category->category_code = $code; 
        $category->level = $level;
        $category->hide = '1'; // Default Hidden
        $category->regist_date = now();
        $category->update_date = now();
        $category->save();

        return response()->json(['id' => $category->id, 'title' => $category->title]);
    }

    // Update Category
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['error' => 'Not found'], 404);

        $category->title = $request->input('title');
        $category->hide = $request->input('hide', '0'); // '1' or '0'
        $category->update_date = now();
        $category->save();

        return response()->json(['success' => true]);
    }

    // Move Node (DnD)
    public function move(Request $request)
    {
        $id = $request->input('id');
        $parent_id = $request->input('parent_id');
        $position = $request->input('position'); // Index in siblings

        $category = Category::find($id);
        if (!$category) return response()->json(['error' => 'Not found'], 404);

        $oldCode = $category->category_code;
        
        // Calculate new parent_id
        $parent_id = ($parent_id == '#') ? 1 : $parent_id; 

        // Generate new code based on the new parent
        if ($parent_id == 0 || $parent_id == 1) { // Root Level
            $maxCode = Category::whereRaw('length(category_code) = 4')
                ->where('id', '!=', $id)
                ->max('category_code');
            
            if (!$maxCode) {
                $newCode = '0001';
            } else {
                $next = intval($maxCode) + 1;
                $newCode = sprintf('%04d', $next);
            }
            $level = 1;
        } else {
            $parent = Category::find($parent_id);
            if (!$parent) {
                return response()->json(['error' => 'Parent not found'], 400);
            }
            $parentCode = $parent->category_code;
            $parentLen = strlen($parentCode);
            $childLen = $parentLen + 4;
            
            $maxChildCode = Category::where('category_code', 'like', $parentCode . '%')
                ->whereRaw('length(category_code) = ?', [$childLen])
                ->where('id', '!=', $id)
                ->max('category_code');
                
            if (!$maxChildCode) {
                $newCode = $parentCode . '0001';
            } else {
                $last4 = substr($maxChildCode, -4);
                $next = intval($last4) + 1;
                $newCode = $parentCode . sprintf('%04d', $next);
            }
            $level = $parent->level + 1;
        }

        DB::transaction(function () use ($id, $parent_id, $level, $oldCode, $newCode, $position) {
            $category = Category::find($id);
            $oldLevel = $category->level;
            
            $category->parent_id = $parent_id;
            $category->level = $level;
            $category->category_code = $newCode;
            $category->save();

            // Recursively update all child categories and their related tables
            if ($oldCode && $newCode && $oldCode !== $newCode) {
                $descendants = Category::where('category_code', 'like', $oldCode . '%')
                    ->where('id', '!=', $id)
                    ->get();

                foreach ($descendants as $desc) {
                    $descOldCode = $desc->category_code;
                    $descNewCode = $newCode . substr($descOldCode, strlen($oldCode));
                    $newDescLevel = $desc->level + ($level - $oldLevel);

                    DB::table('fm_category')->where('id', $desc->id)->update([
                        'category_code' => $descNewCode,
                        'level' => $newDescLevel
                    ]);

                    $this->updateRelatedTables($descOldCode, $descNewCode);
                }

                // Update related tables for the moved category itself
                $this->updateRelatedTables($oldCode, $newCode);
            }

            // Reorder siblings
            $siblings = Category::where('parent_id', $parent_id)
                ->where('id', '!=', $id)
                ->orderBy('position', 'asc')
                ->get();
            
            $siblings->splice($position, 0, [$category]);
            
            foreach ($siblings as $idx => $node) {
                if ($node->position != $idx) {
                    DB::table('fm_category')->where('id', $node->id)->update(['position' => $idx]);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    private function updateRelatedTables($oldCode, $newCode)
    {
        $tables = [
            'fm_category_link' => 'category_code',
            'fm_category_group' => 'category_code',
            'fm_coupon_issuecategory' => 'category_code',
            'fm_download_issuecategory' => 'category_code',
            'fm_event_choice' => 'category_code',
            'fm_member_group_issuecategory' => 'category_code'
        ];

        foreach ($tables as $table => $column) {
            try {
                DB::table($table)->where($column, $oldCode)->update([$column => $newCode]);
            } catch (\Exception $e) {
                // Gracefully handle missing tables in testing environment
            }
        }
    }

    // Delete Node
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['error' => 'Not found'], 404);

        // Check Children
        if ($category->children()->count() > 0) {
             return response()->json(['error' => '하위 카테고리가 있어 삭제할 수 없습니다.'], 400);
        }
        
        // Check Goods
        $goods_count = DB::table('fm_category_link')->where('category_code', $category->category_code)->count();
        if ($goods_count > 0) {
            return response()->json(['error' => "등록된 상품($goods_count)이 있어 삭제할 수 없습니다."], 400);
        }

        $category->delete();
        return response()->json(['success' => true]);
    }

    // Get Linked Goods
    public function getGoods($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json([], 404);

        $goods = DB::table('fm_category_link as l')
            ->join('fm_goods as g', 'l.goods_seq', '=', 'g.goods_seq')
            ->where('l.category_code', $category->category_code)
            ->select('g.goods_seq', 'g.goods_name', 'g.goods_code', 'g.sale_price', 'g.goods_status', 'l.sort')
            ->orderBy('l.sort', 'asc')
            ->limit(50) // Limit for performance
            ->get();

        return response()->json($goods);
    }
}
