<?php

namespace App\Http\Controllers\Admin\Goods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoodsSortcd;
use App\Models\Goods;
use Illuminate\Support\Facades\DB;

class SortcdCatalogController extends Controller
{
    /**
     * 진열번호 리스트 조회 (카탈로그 뷰)
     */
    public function index(Request $request)
    {
        $query = GoodsSortcd::query();

        // 필터: 올/유무 분기
        $gubun = $request->input('gubun', 'all');
        if ($gubun === 'y') {
            $query->whereNotNull('goods_scode')->where('goods_scode', '!=', '');
        } elseif ($gubun === 'n') {
            $query->where(function($q) {
                $q->whereNull('goods_scode')->orWhere('goods_scode', '');
            });
        }

        // 검색
        $keyword = $request->input('keyword');
        if ($keyword) {
            $keywords = explode(' ', $keyword);
            if (count($keywords) > 1) {
                $query->where(function($q) use ($keywords) {
                    $q->whereIn('goods_sortcd', $keywords)
                      ->orWhereIn('goods_scode', $keywords)
                      ->orWhereIn('goods_memo', $keywords);
                });
            } else {
                $query->where(function($q) use ($keyword) {
                    $q->where('goods_sortcd', 'like', "%{$keyword}%")
                      ->orWhere('goods_scode', 'like', "%{$keyword}%");
                });
            }
        }

        // 정렬
        $orderby = $request->input('orderby', 'asc@goods_sortcd');
        [$orderDir, $orderCol] = explode('@', $orderby);
        
        $allowedSortCols = ['goods_sortcd', 'goods_scode'];
        if (in_array($orderCol, $allowedSortCols)) {
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->orderBy('goods_sortcd', 'asc');
        }

        // 페이징
        $perpage = $request->input('perpage', 100);
        $memos = $query->paginate($perpage)->appends($request->all());

        return view('admin.goods.sortcd_catalog.index', compact('memos', 'gubun', 'keyword', 'orderby', 'perpage'));
    }

    /**
     * 단일 또는 일괄 등록
     */
    public function store(Request $request)
    {
        $mode = $request->input('mode');

        if ($mode === 'insert_list') {
            // 일괄 등록 모드
            $contentsRaw = $request->input('contents');
            if (empty($contentsRaw) || strlen($contentsRaw) < 1) {
                return response()->json(['success' => false, 'message' => '등록할 진열번호가 없습니다.', 'type' => 'Blank']);
            }

            $contentsRaw = str_replace(["\r\n", "\r", "\n"], ',', $contentsRaw);
            $contents = explode(',', $contentsRaw);

            $inserted = 0;
            $doubled = 0;

            foreach ($contents as $v) {
                $v = trim($v);
                if ($v && $v !== 'undefined') {
                    $exists = GoodsSortcd::where('goods_sortcd', $v)->exists();
                    if ($exists) {
                        $doubled++;
                    } else {
                        GoodsSortcd::create(['goods_sortcd' => $v, 'goods_memo' => '']);
                        $inserted++;
                    }
                }
            }

            if ($doubled > 0 && $inserted === 0) {
                 return response()->json(['success' => false, 'message' => '중복상품이 있습니다.', 'type' => 'Double']);
            }

            return response()->json(['success' => true, 'message' => "{$inserted}건 처리되었습니다. (중복 제외)"]);

        } else if ($mode === 'insert') {
            // 단일 등록 처리
            $sortcd = trim($request->input('sortcd'));
            $scode = trim($request->input('scode'));

            if (empty($sortcd)) {
                 return response()->json(['success' => false, 'message' => '공백은 입력이 안됩니다.', 'type' => 'Blank']);
            }

            $exists = GoodsSortcd::where('goods_sortcd', $sortcd)->exists();
            if ($exists) {
                 return response()->json(['success' => false, 'message' => '중복상품이 있습니다.', 'type' => 'Double']);
            }

            GoodsSortcd::create([
                'goods_sortcd' => $sortcd,
                'goods_scode' => $scode,
                'goods_memo' => ''
            ]);

            // 상품 테이블 동기화
            if ($scode) {
                Goods::where('goods_scode', $scode)->update(['goods_sortcd' => $sortcd]);
            }

            return response()->json(['success' => true, 'message' => '처리되었습니다.']);
        }

        return response()->json(['success' => false, 'message' => '잘못된 요청입니다.']);
    }

    /**
     * 단건 수정 및 복수 건 수정 처리
     */
    public function update(Request $request)
    {
        $mode = $request->input('mode');

        if ($mode === 'modify_multiple') {
            $seqArr = $request->input('seq_arr');
            
            if (is_array($seqArr) && count($seqArr) > 0) {
                foreach ($seqArr as $seq) {
                    $sortcd = trim($request->input("goods_sortcd_{$seq}"));
                    $scode = trim($request->input("goods_scode_{$seq}"));

                    GoodsSortcd::where('sortcd_seq', $seq)->update([
                        'goods_sortcd' => $sortcd,
                        'goods_scode' => $scode
                    ]);

                    if ($sortcd && $scode) {
                         Goods::where('goods_scode', $scode)->update(['goods_sortcd' => $sortcd]);
                    }
                }
                return response()->json(['success' => true, 'message' => '일괄 수정되었습니다.']);
            }
            return response()->json(['success' => false, 'message' => '변경할 내용이 없습니다.']);

        } else if ($mode === 'modify') {
            // 개별 수정
            $seq = $request->input('sortcd_seq');
            $sortcd = trim($request->input('sortcd'));
            $scode = trim($request->input('scode'));

            $exists = GoodsSortcd::where('goods_sortcd', $sortcd)->where('sortcd_seq', '!=', $seq)->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => '중복상품이 있습니다.', 'type' => 'Double']);
            }

            GoodsSortcd::where('sortcd_seq', $seq)->update([
                'goods_sortcd' => $sortcd,
                'goods_scode' => $scode
            ]);

            if ($sortcd && $scode) {
                Goods::where('goods_scode', $scode)->update(['goods_sortcd' => $sortcd]);
            }

            return response()->json(['success' => true, 'message' => '처리되었습니다.']);
        }

        return response()->json(['success' => false, 'message' => '잘못된 요청입니다.']);
    }

    /**
     * 단일 삭제
     */
    public function destroy(Request $request)
    {
         $seq = $request->input('sortcd_seq');
         if ($seq) {
             GoodsSortcd::where('sortcd_seq', $seq)->delete();
             return response()->json(['success' => true, 'message' => '삭제되었습니다.']);
         }
         return response()->json(['success' => false, 'message' => '삭제할 항목이 지정되지 않았습니다.']);
    }

    /**
     * 엑셀 다운로드 
     */
    public function excel(Request $request)
    {
        $query = GoodsSortcd::query();

        // 필터: 올/유무 분기
        $gubun = $request->input('gubun', 'all');
        if ($gubun === 'y') {
            $query->whereNotNull('goods_scode')->where('goods_scode', '!=', '');
        } elseif ($gubun === 'n') {
            $query->where(function($q) {
                $q->whereNull('goods_scode')->orWhere('goods_scode', '');
            });
        }

        $keyword = $request->input('keyword');
        if ($keyword) {
             $query->where(function($q) use ($keyword) {
                 $q->where('goods_sortcd', $keyword)
                   ->orWhere('goods_scode', $keyword);
             });
        }

        $orderby = $request->input('orderby', 'asc@goods_sortcd');
        [$orderDir, $orderCol] = explode('@', $orderby);
        
        $allowedSortCols = ['goods_sortcd', 'goods_scode'];
        if (in_array($orderCol, $allowedSortCols)) {
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->orderBy('goods_sortcd', 'asc');
        }

        $items = $query->get();

        $filename = "sortcd_list_" . date('YmdHis') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            
            // BOM for Excel Korean support
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, ['No', '진열번호', '상품코드', '메모']);

            $no = 1;
            foreach ($items as $item) {
                fputcsv($file, [
                    $no++,
                    $item->goods_sortcd,
                    $item->goods_scode,
                    $item->goods_memo
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
