<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BoardBulkOrder;
use App\Models\BoardManager;

class BulkOrderController extends Controller
{
    public function index(Request $request)
    {
        $boardId = 'bulkorder';
        $boardConfig = BoardManager::findById($boardId);

        if (!$boardConfig) {
            abort(404, 'Board config not found');
        }

        $query = BoardBulkOrder::orderBy('gid', 'asc') // Legacy sort
                               ->orderBy('r_date', 'desc');

        // Filter: onlynotice != '1' (handled in legacy)
        $query->where(function($q) {
            $q->where('onlynotice', '<>', '1')->orWhereNull('onlynotice');
        });

        // Search logic if needed
        if ($request->has('search_text') && $request->search_text) {
             $query->where(function($q) use ($request) {
                 $q->where('subject', 'like', '%'.$request->search_text.'%')
                   ->orWhere('contents', 'like', '%'.$request->search_text.'%');
             });
        }

        $posts = $query->paginate(15);
        $posts->appends($request->all());

        return view('front.board.bulkorder.index', compact('boardConfig', 'posts', 'boardId'));
    }

    public function view(Request $request)
    {
        $seq = $request->query('seq');
        $boardId = 'bulkorder';
        
        $post = BoardBulkOrder::findOrFail($seq);
        $post->increment('hit');

        $boardConfig = BoardManager::findById($boardId);

        return view('front.board.bulkorder.view', compact('post', 'boardConfig', 'boardId'));
    }

    public function create(Request $request)
    {
        $boardId = 'bulkorder';
        $boardConfig = BoardManager::findById($boardId);

        // Simple auth check
        if (!auth()->check()) {
            return redirect()->route('member.login')->with('error', '로그인이 필요합니다.');
        }

        return view('front.board.bulkorder.write', compact('boardConfig', 'boardId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'contents' => 'required|string',
        ]);

        $user = auth()->user();

        $post = new BoardBulkOrder();
        $post->gid = 0;
        $post->subject = $request->subject;
        $post->contents = $request->contents;
        $post->name = $user->user_name;
        $post->mseq = $user->member_seq;
        $post->mid = $user->userid;
        
        $post->company = $request->company;
        $post->person_name = $request->person_name;
        $post->email = $request->email;
        // Simple tel handling
        $post->tel1 = $request->tel1 . '-' . $request->tel2 . '-' . $request->tel3;

        $post->r_date = now();
        $post->ip = $request->ip();
        
        $post->save();
        
        $post->gid = floatval($post->seq); 
        $post->save();

        return redirect()->route('board.index', ['id' => 'bulkorder'])->with('success', '견적 요청이 등록되었습니다.');
    }
}
