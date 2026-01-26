<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class MemberCatalogTest extends TestCase
{
    /** @test */
    public function can_search_members()
    {
        // 1. Create Group
        $groupSeq = DB::table('fm_member_group')->insertGetId([
            'group_name' => '테스트등급',
            'regist_date' => now()
        ]);

        // 2. Create Member
        $memberId = 'testuser_' . rand(1000,9999);
        $key = 'FirstMall';

        DB::statement("
            INSERT INTO fm_member (userid, user_name, email, group_seq, regist_date, status)
            VALUES (?, ?, AES_ENCRYPT(?, ?), ?, ?, 'done')
        ", [
            $memberId, 
            '테스트홍길동', 
            'test@example.com', 
            $key, 
            $groupSeq, 
            now()->format('Y-m-d')
        ]);
        
        $memberSeq = DB::getPdo()->lastInsertId();

        // 3. Search Logic
        $response = $this->withoutMiddleware()->get(route('admin.member.catalog', [
            'keyword' => $memberId
        ]));
        
        $response->assertStatus(200);
        $response->assertSee($memberId);
        $response->assertSee('테스트등급'); // Verify Join

        // 4. Date Filter Test
        $today = now()->format('Y-m-d');
        $responseDate = $this->withoutMiddleware()->get(route('admin.member.catalog', [
            'start_date' => $today,
            'end_date' => $today
        ]));
        $responseDate->assertSee($memberId);

        // Cleanup
        DB::table('fm_member')->where('member_seq', $memberSeq)->delete();
        DB::table('fm_member_group')->where('group_seq', $groupSeq)->delete();
    }
}
