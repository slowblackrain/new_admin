<?php

namespace Tests\Feature\Front;

use App\Models\Board;
use App\Models\BoardManager;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BoardWriteTest extends TestCase
{
    // use RefreshDatabase; // Use transaction rollback if possible, or manual cleanup

    public function test_user_can_write_post_and_comment()
    {
        // 1. Setup User
        $user = Member::where('userid', 'testboarduser')->first();
        if (!$user) {
            $user = new Member();
            $user->userid = 'testboarduser';
            $user->user_name = 'Test Board User';
            $user->email = 'testboard@example.com';
            $user->password = bcrypt('password');
            $user->status = 'active';
            $user->save();
        }
        
        // 2. Setup Board Config (if needed by FK or logic)
        // Usually seeded, but let's check if exists or create dummy
        // Assuming 'notice' board exists or create one
        $boardId = 'notice';
        if (!BoardManager::where('id', $boardId)->exists()) {
            BoardManager::create(['id' => $boardId, 'title' => 'Notice Board']);
        }

        // 3. Access Write Form
        $response = $this->actingAs($user)->get(route('board.write', ['id' => $boardId]));
        $response->assertStatus(200);
        $response->assertSee('글쓰기');

        // 4. Submit Post
        $response = $this->actingAs($user)->post(route('board.store'), [
            'board_id' => $boardId,
            'subject' => 'Test Post Subject',
            'contents' => 'Test Post Contents',
        ]);
        
        $response->assertRedirect(route('board.index', ['id' => $boardId]));
        
        // Check DB
        $this->assertDatabaseHas('fm_boarddata', [
            'subject' => 'Test Post Subject',
            'boardid' => $boardId,
            'mseq' => $user->member_seq,
        ]);

        $post = Board::where('subject', 'Test Post Subject')->first();
        $this->assertNotNull($post);

        // 5. Submit Comment
        $response = $this->actingAs($user)->post(route('board.comment.store'), [
            'parent_seq' => $post->seq,
            'content' => 'Test Comment Contents',
        ]);
        
        $response->assertSessionHas('success');
        
        // Check DB for Comment
        $this->assertDatabaseHas('fm_board_comment', [
            'parent' => $post->seq,
            'content' => 'Test Comment Contents',
            'mseq' => $user->member_seq,
        ]);

        // Cleanup
        $post->delete();
        // Comment deletion handled by cascade? or manual
        // BoardComment::where('parent', $post->seq)->delete();
        $user->delete();
    }
}
