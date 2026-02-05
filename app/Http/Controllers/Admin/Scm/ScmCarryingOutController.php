<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Scm\ScmCarryingOutService;
use Exception;

class ScmCarryingOutController extends Controller
{
    protected $carryingOutService;

    public function __construct(ScmCarryingOutService $carryingOutService)
    {
        $this->carryingOutService = $carryingOutService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'wh_seq' => 'required|integer',
            'trader_seq' => 'required|integer',
            'items' => 'required|array',
            'items.*.goods_seq' => 'required|integer',
            'items.*.option_seq' => 'required|integer',
            'items.*.ea' => 'required|integer|min:1',
        ]);

        try {
            $croSeq = $this->carryingOutService->processCarryingOut(
                $request->wh_seq,
                $request->trader_seq,
                $request->items
            );

            return response()->json([
                'success' => true,
                'message' => 'Carrying Out processed successfully.',
                'cro_seq' => $croSeq
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
