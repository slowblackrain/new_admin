<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    private function getMemberSeq($userid) {
        $member = DB::table('fm_member')->where('userid', $userid)->value('member_seq');
        if (!$member) {
            // Fallback or error? For now 0 or error.
            return 0;
        }
        return $member;
    }

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
        
        $query = DB::table('fm_boarddata')
            ->where('boardid', $id)
            ->select('seq', 'subject', 'name', 'r_date', 'hit', 'notice', 'comment', 're_status', 're_contents', 're_date')
            ->orderByDesc('notice')
            ->orderByDesc('gid');

        // Contextual Filtering
        if ($id === 'mbqna') {
            $mseq = $this->getMemberSeq($seller->userid);
            $query->where('mseq', $mseq);
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

    public function show(Request $request, $id, $seq)
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

        $post = DB::table('fm_boarddata')->where('seq', $seq)->first();

        if (!$post) {
            abort(404, '존재하지 않는 게시물입니다.');
        }

        // Permission Check for 1:1 Inquiry
        if ($id === 'mbqna') {
            $mseq = $this->getMemberSeq($seller->userid);
            if ($post->mseq != $mseq) {
                abort(403, '본인의 문의 내역만 확인할 수 있습니다.');
            }
        }
        
        // Increment Hit for Notices
        if ($id !== 'mbqna') {
            DB::table('fm_boarddata')->where('seq', $seq)->increment('hit');
        }

        return view('seller.board.view', [
            'post' => $post,
            'boardId' => $id,
            'boardName' => $boardName
        ]);
    }

    public function create(Request $request, $id)
    {
        if ($id !== 'mbqna') {
            abort(403, '공지사항은 관리자만 작성할 수 있습니다.');
        }

        return view('seller.board.write', [
            'boardId' => $id,
            'boardName' => '1:1 문의'
        ]);
    }

    public function store(Request $request, $id)
    {
        if ($id !== 'mbqna') {
            abort(403, '잘못된 접근입니다.');
        }

        $request->validate([
            'subject' => 'required|max:255',
            'contents' => 'required',
        ]);

        $seller = Auth::guard('seller')->user();
        $mseq = $this->getMemberSeq($seller->userid);

        // Safe Insert
        DB::table('fm_boarddata')->insert([
            'boardid' => $id,
            'mseq' => $mseq,
            'name' => $seller->provider_name, // Or specific manager name?
            'subject' => $request->subject,
            'contents' => $request->contents,
            'r_date' => now(),
            'ip' => $request->ip(),
            'notice' => '0',
            'hit' => 0,
            'comment' => 0,
            're_status' => 'n', // Default no reply
        ]);

        return redirect()->route('seller.board.index', $id)->with('success', '문의가 등록되었습니다.');
    }
}
