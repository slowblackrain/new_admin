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
        
        // Generate Code (Simple Logic: Random or Max+1)
        // Legacy seems to use 4-char chunks. For now simple unique ID or microtime
        $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8); 

        $category = new Category();
        $category->parent_id = $parent_id;
        $category->title = '새 카테고리';
        $category->position = $position;
        $category->category_code = $code; 
        $category->category_code = $code; 
        
        if ($parent_id == 0) {
            $category->level = 1;
        } else {
            $parent = Category::find($parent_id);
            if (!$parent) {
                // Determine fallback: Root(1) or if 1 is missing, error?
                // For now throw error or fallback to 1.
                // But verify_script sends 1. If 1 is missing, we have a problem.
                return response()->json(['error' => 'Parent category not found'], 400);
            }
            $category->level = $parent->level + 1;
        }
        
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

        // Update Parent
        $category->parent_id = ($parent_id == '#') ? 1 : $parent_id; 
        // Note: verify if '#' maps to 1 or 0 based on legacy root.
        // If legacy root is 1, and we move to top level, parent should be 1.
        
        // Determine Level
        if ($category->parent_id == 0) {
            $category->level = 1;
        } else {
             $p = Category::find($category->parent_id);
             $category->level = $p ? $p->level + 1 : 1;
        }

        $category->save();

        // Reorder Siblings
        // Get all siblings ordered by current position
        // This is complex because we inserted at a specific index. 
        // Re-sorting all siblings is safer.
        // But we only know the 'position' index provided by JSTree.
        // We should shift others.
        
        // Simplest: Get all siblings (excluding self if we hadn't saved yet, but we did).
        // Actually, we should set self to that position, and shift others.
        // Let's do a bulk update for siblings.
        
        $siblings = Category::where('parent_id', $category->parent_id)
            ->where('id', '!=', $id)
            ->orderBy('position', 'asc')
            ->get();
        
        $siblings->splice($position, 0, [$category]); // Insert self at new pos
        
        foreach ($siblings as $idx => $node) {
            if ($node->position != $idx) { // optimization
                DB::table('fm_category')->where('id', $node->id)->update(['position' => $idx]);
            }
        }

        return response()->json(['success' => true]);
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
