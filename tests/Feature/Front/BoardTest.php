<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\Board;
use App\Models\BoardManager;

class BoardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure 'notice' config exists
        if (!BoardManager::where('id', 'notice')->exists()) {
            DB::table('fm_boardmanager')->insert([
                'id' => 'notice',
                'name' => '공지사항',
                'type' => 'B',
                'skin' => 'default01',
                // Add simplified required fields or defaults as per schema
                'auth_read_use' => 'Y',
                'auth_read' => '[all]',
                'auth_write_use' => 'Y', // For testing
                'auth_write' => '[admin]',
                'r_date' => now(),
                'm_date' => now(),
            ]);
        }

        // Ensure a test notice exists with UNIQUE Subject
        $uniqueSubject = 'TestingNotice_' . uniqid();
        
        // Use updateOrInsert with a known unique key if possible, OR just insert a new one if not exists
        // Let's delete old test ones first to avoid clutter
        DB::table('fm_boarddata')->where('subject', 'like', 'TestingNotice_%')->delete();

        $this->testSubject = $uniqueSubject;

        DB::table('fm_boarddata')->insert([
            'boardid' => 'notice',
            'gid' => -9999999, // Force top
            'depth' => 0,
            'parent' => 0,
            'display' => 0, // 0 is usually visible in legacy
            'notice' => 1, // Sticky
            'name' => '관리자',
            'subject' => $uniqueSubject,
            'contents' => '<p>테스트 내용입니다.</p>',
            'onlynotice' => null,  // Set to NULL to pass orWhereNull check reliably
            'r_date' => now(),
            'm_date' => now(),
        ]);
    }

    public function test_notice_board_list_loads()
    {
        $response = $this->get(route('board.index', ['id' => 'notice']));

        $response->assertStatus(200);
        $response->assertStatus(200);
        $response->assertSee('공지사항'); 
        
        // checking for subject we inserted
        $response->assertSee($this->testSubject);
    }

    public function test_notice_board_view_loads()
    {
        $post = Board::where('boardid', 'notice')->first();

        $response = $this->get(route('board.view', ['id' => 'notice', 'seq' => $post->seq]));

        $response->assertStatus(200);
        $response->assertSee($post->subject);
        $response->assertSee('테스트 내용입니다.');
    }

    public function test_cs_page_loads_latest_notices()
    {
        $response = $this->get(route('service.cs'));
        
        $response->assertStatus(200);
        // CS page often lists recent notices
        $response->assertSee('테스트 공지사항입니다.'); 
    }
}
