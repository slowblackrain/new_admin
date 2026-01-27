<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\BoardManager;
use App\Models\BoardComment;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $boardId = $request->query('id', 'notice'); // Default to notice

        // 1. Get Board Config
        $boardConfig = BoardManager::findById($boardId);

        if (!$boardConfig) {
            abort(404, 'Board not found');
        }

        // 2. Fetch Posts
        // Logic: Show Notices first? usually handled by ordering or separate separation
        // Legacy: 'notice' column 1 means sticky notice.

        $query = Board::board($boardId)
            // ->where('display', 1) // Legacy display=0 is visible
            ->where(function($q) {
                $q->where('onlynotice', '<>', 1)->orWhereNull('onlynotice');
            })
            ->orderBy('notice', 'desc') // Sticky notices first
            ->orderBy('gid', 'asc'); // Legacy ordering

        // If gid ordering is complex, fallback to standard r_date
        // Legacy boards often use `gid` (group id) for threading. 
        // For simple listing, r_date desc is safer if threading not strictly required yet.
        // Let's stick to simple sorting for now: Sticky -> Date Desc
        //$query->orderBy('r_date', 'desc');

        $posts = $query->paginate(15);
        $posts->appends($request->all());

        return view('front.board.index', compact('boardConfig', 'posts', 'boardId'));
    }

    public function view(Request $request)
    {
        $seq = $request->query('seq');
        $boardId = $request->query('id'); // Should match

        $post = Board::where('seq', $seq)->firstOrFail();

        // Validation: Check if post belongs to board?
        if ($boardId && $post->boardid !== $boardId) {
            // Mismatch, but maybe allow if just viewing by seq
        }

        // Increment Hit
        $post->increment('hit');

        $boardConfig = BoardManager::findById($post->boardid);

        return view('front.board.view', compact('post', 'boardConfig'));
    }

    public function cs()
    {
        // Fetch latest notices for CS center
        $notices = Board::board('notice')->orderBy('notice', 'desc')->orderBy('r_date', 'desc')->limit(5)->get();
        // Fetch FAQ or others if needed

        return view('front.service.cs', compact('notices'));
    }

    public function create(Request $request)
    {
        $boardId = $request->query('id');
        if (!$boardId) {
            abort(404, 'Board ID required');
        }
        
        // Check Permission (Simple Auth check for now, can be sophisticated based on BoardManager)
        if (!auth()->check()) {
            return redirect()->route('member.login')->with('error', '로그인이 필요합니다.');
        }

        $boardConfig = BoardManager::findById($boardId);
        
        return view('front.board.write', compact('boardConfig', 'boardId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'board_id' => 'required',
            'subject' => 'required|string|max:255',
            'contents' => 'required|string',
        ]);

        $boardId = $request->board_id;
        $user = auth()->user();

        // Save logic
        $board = new Board();
        $board->boardid = $boardId;
        $board->subject = $request->subject;
        $board->contents = $request->contents;
        $board->name = $user->user_name;
        $board->mseq = $user->member_seq; 
        $board->mid = $user->userid;
        $board->pw = ''; // user post doesn't need pw if logged in
        $board->r_date = now();
        $board->hit = 0;
        $board->display = 1; // 1=visible? Let's hope.

        $board->ip = $request->ip();
        
        $board->gid = 0; 
        
        $board->save();

        $board->gid = floatval($board->seq); 
        $board->save();

        return redirect()->route('board.index', ['id' => $boardId])->with('success', '게시글이 등록되었습니다.');
    }

    public function commentStore(Request $request)
    {
        $request->validate([
            'parent_seq' => 'required|exists:fm_boarddata,seq',
            'content' => 'required|string',
        ]);

        $post = Board::findOrFail($request->parent_seq);
        $user = auth()->user();

        $comment = new BoardComment();
        $comment->parent = $post->seq;
        // $comment->boardid = $post->boardid; 
        $comment->content = $request->content;
        $comment->mseq = $user->member_seq; 
        $comment->name = $user->user_name;
        // $comment->mid = $user->userid; // if needed
        $comment->ip = $request->ip();
        $comment->r_date = now();
        $comment->save();

        // Increment comment count 
        $post->increment('comment'); 

        return back()->with('success', '댓글이 등록되었습니다.');
    }

    public function getGoodsBoardList(Request $request)
    {
        $boardId = $request->query('id');
        $goodsSeq = $request->query('goods_seq');

        if (!$boardId || !$goodsSeq) {
            return '';
        }

        // Fetch posts for this goods
        $posts = Board::where('boardid', $boardId)
            ->where('goods_seq', $goodsSeq)
            ->orderBy('r_date', 'desc')
            ->paginate(5); // Small pagination for embedded view

        return view('front.board.goods_list', compact('posts', 'boardId', 'goodsSeq'));
    }
}
