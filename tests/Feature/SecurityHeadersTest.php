<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    /**
     * HTTPレスポンスにセキュリティヘッダーが含まれていることを検証
     */
    public function test_security_headers_are_present_in_responses(): void
    {
        // 明示的に HTTP URL でリクエストを送信
        $url = str_replace('https://', 'http://', route('login'));
        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()');
        $response->assertHeader('Content-Security-Policy');

        // CSPの具体的設定値が含まれていることを簡易検証
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' https://cdn.jsdelivr.net", $csp);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com", $csp);
        $this->assertStringContainsString("connect-src 'self' https://cdn.jsdelivr.net", $csp);

        // デフォルトのHTTP通信時は、HSTSは含まれていないことを確認
        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    /**
     * HTTPS通信時にStrict-Transport-Security (HSTS) が付与されることを検証
     */
    public function test_hsts_is_present_on_secure_requests(): void
    {
        // 明示的に HTTPS URL でリクエストを送信
        $url = str_replace('http://', 'https://', route('login'));
        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    }
}
