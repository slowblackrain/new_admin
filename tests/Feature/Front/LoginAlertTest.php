<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoginAlertTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function test_login_page_renders_successfully()
    {
        $response = $this->get(route('member.login'));
        $response->assertStatus(200);
        $response->assertSee('로 그 인');
    }

    /** @test */
    public function test_login_validation_errors_trigger_javascript_alerts()
    {
        // 1. Missing both userid and password
        $response = $this->post(route('member.login_process'), [
            'userid' => '',
            'password' => ''
        ]);

        $response->assertSessionHasErrors(['userid', 'password']);
        
        // Follow redirect to login page
        $redirectResponse = $this->get(route('member.login'));
        $redirectResponse->assertSee('alert("아이디를 입력해 주세요.");', false);
    }

    /** @test */
    public function test_login_failed_with_wrong_credentials_triggers_javascript_alert()
    {
        // 2. Wrong credentials
        $response = $this->post(route('member.login_process'), [
            'userid' => 'non_existent_user_9999',
            'password' => 'wrong_password_9999'
        ]);

        $response->assertSessionHasErrors(['userid']);
        
        // Follow redirect to login page
        $redirectResponse = $this->get(route('member.login'));
        $redirectResponse->assertSee('alert("아이디 또는 비밀번호가 일치하지 않습니다.");', false);
    }
}
