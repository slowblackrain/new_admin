<?php

namespace App\Http\Controllers\Admin\Goods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Admin\Goods\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class BrandController extends Controller
{
    public function index()
    {
        return view('admin.goods.brand.index');
    }

    // Handles JSTree AJAX Requests
    public function tree(Request $request)
    {
        $op = $request->input('operation');
        $id = $request->input('id');

        if ($op == 'get_children') {
            $parentId = ($id == '#' || $id == '1' || $id == '0') ? 0 : $id; // Legacy root might be 1 or 0
            
            // If root request, and no root exists, create root or return empty?
            // Legacy starts with level 2? 
            // brandmodel: select * from fm_brand where level >= 2
            
            // Let's assume parent_id mapping.
            // If id=1 (Legacy Root), return top-level brands (level 2).
            
            // Case 1: Root Request
            if ($id === 1 || $id === '1') {
                $brands = Brand::where('level', 2)->orderBy('position')->get();
            } else {
                $brands = Brand::where('parent_id', $id)->orderBy('position')->get();
            }

            $result = [];
            foreach ($brands as $brand) {
                // Check if it has children
                $hasChildren = Brand::where('parent_id', $brand->id)->exists();
                
                $result[] = [
                    'id' => $brand->id,
                    'text' => $brand->title,
                    'children' => $hasChildren,
                    'li_attr' => ['category' => $brand->category_code],
                ];
            }
            return response()->json($result);
        }

        if ($op == 'create_node') {
            $parentId = $request->input('id');
            $title = $request->input('title');
            $position = $request->input('position');

            // Find parent to generate code
            $parent = Brand::find($parentId);
            $parentCode = $parent ? $parent->category_code : '';
            $parentLevel = $parent ? $parent->level : 1; // Assuming root is level 1
            
            $newCode = Brand::generateNextCode($parentCode);
            
            $brand = new Brand();
            $brand->parent_id = $parentId ?: 1; // Default to root
            $brand->category_code = $newCode;
            $brand->title = $title;
            $brand->level = $parentLevel + 1;
            $brand->position = $position;
            $brand->save();

            return response()->json([
                'status' => 1, 
                'id' => $brand->id, 
                'category_code' => $brand->category_code
            ]);
        }

        if ($op == 'rename_node') {
            $id = $request->input('id');
            $title = $request->input('title');
            
            $brand = Brand::find($id);
            if ($brand) {
                $brand->title = $title;
                $brand->save();
                return response()->json(['status' => 1]);
            }
            return response()->json(['status' => 0]);
        }

        if ($op == 'remove_node') {
            $id = $request->input('id');
            $brand = Brand::find($id);
            if ($brand) {
                // Recursive delete is handled by database cascade or needs manual loop
                // For now, simple delete
                $brand->delete();
                return response()->json(['status' => 1]);
            }
            return response()->json(['status' => 0]);
        }
        
        // Move Node (Complex, postpone if not critical for initial display)
        if ($op == 'move_node') {
             // ... TBD
             return response()->json(['status' => 1]);
        }
    }

    public function show($categoryCode)
    {
        // Returns the partial view for the iframe/edit area
        $brand = Brand::where('category_code', $categoryCode)->firstOrFail();
        // Load legacy-style relations if any (country, etc) - mocking for now
        $brand->country = ['name' => 'USA', 'flagimg' => 'usa.gif']; 
        
        return view('admin.goods.brand.form', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);
        $brand->update($request->all());
        return redirect()->back()->with('success', 'Updated');
    }
}
