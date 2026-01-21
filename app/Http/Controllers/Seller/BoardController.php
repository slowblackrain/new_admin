<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    public function index(Request $request, $id)
    {
        $seller = Auth::guard('seller')->user();
        
        // Define Board Types
        $boards = [
            'notice' => '공지사항',
            'gs_seller_notice' => '셀러 공지사항',
            'mbqna' => '1:1 문의',
        ];

        if (!array_key_exists($id, $boards)) {
            abort(404, '존재하지 않는 게시판입니다.');
        }

        $boardName = $boards[$id];
        
        // Query fm_boarddata (or legacy table depending on migration)
        // Legacy uses `fm_boarddata` or specific view.
        $query = DB::table('fm_boarddata')
            ->where('boardid', $id)
            ->select('seq', 'subject', 'name', 'r_date', 'hit', 'notice', 'comment', 're_status', 're_contents', 're_date')
            ->orderByDesc('notice') // Notice first
            ->orderByDesc('gid');

        // Contextual Filtering
        if ($id === 'mbqna') {
            // Filter by Provider's Member Seq for Inquiries
             $query->where('mseq', $seller->provider_member_seq);
        }

        // Search
        if ($request->filled('keyword')) {
             $query->where('subject', 'like', '%' . $request->keyword . '%');
        }

        $posts = $query->paginate(20);

        return view('seller.board.index', [
            'posts' => $posts,
            'boardId' => $id,
            'boardName' => $boardName
        ]);
    }
}
