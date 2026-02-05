<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Scm\ScmRevisionService;
use Exception;

class ScmRevisionController extends Controller
{
    protected $revisionService;

    public function __construct(ScmRevisionService $revisionService)
    {
        $this->revisionService = $revisionService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'wh_seq' => 'required|integer',
            'items' => 'required|array',
            'items.*.goods_seq' => 'required|integer',
            'items.*.option_seq' => 'required|integer',
            'items.*.ea' => 'required|integer|not_in:0', // Must be non-zero
        ]);

        try {
            $revSeq = $this->revisionService->processRevision(
                $request->wh_seq,
                $request->items,
                $request->input('admin_memo')
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock revision processed successfully.',
                'rev_seq' => $revSeq
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
