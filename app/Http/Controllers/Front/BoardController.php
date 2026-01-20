<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\BoardManager;

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
            ->where('onlynotice', '<>', 1) // Exclude duplicate notices in main list if needed
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
}
