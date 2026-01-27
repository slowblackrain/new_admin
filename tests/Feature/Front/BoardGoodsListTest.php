<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use App\Models\Board;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BoardGoodsListTest extends TestCase
{
    // use RefreshDatabase; // Use existing DB

    public function test_can_fetch_goods_board_list_partial()
    {
        // 1. Setup Data
        $goodsSeq = 12345;
        $boardId = 'goods_review';

        // Create a dummy post
        $post = new Board();
        $post->boardid = $boardId;
        $post->goods_seq = $goodsSeq;
        $post->subject = 'Test Review Subject';
        $post->contents = 'Test Contents';
        $post->name = 'Tester';
        $post->r_date = now();
        $post->gid = 0;
        $post->save();

        // 2. Call Endpoint
        $response = $this->get(route('board.goods.list', [
            'id' => $boardId,
            'goods_seq' => $goodsSeq
        ]));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertViewIs('front.board.goods_list');
        $response->assertSee('Test Review Subject');
        $response->assertSee('Tester');
        
        // Cleanup
        $post->delete();
    }

    public function test_returns_empty_string_if_missing_params()
    {
        $response = $this->get(route('board.goods.list'));
        $response->assertStatus(200);
        $this->assertEquals('', $response->getContent());
    }
}
