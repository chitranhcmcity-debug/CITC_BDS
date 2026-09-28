<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteTest extends TestCase
{
    /**
     * Test public GET routes as Guest.
     */
    public function test_public_routes_as_guest(): void
    {
        $routes = [
            '/',
            '/bang-gia',
            '/lien-he',
            '/dang-nhap',
            '/dang-ky',
            '/nguoi-dung/verify-email-notice',
        ];

        foreach ($routes as $route) {
            try {
                $response = $this->get($route);
                $status = $response->getStatusCode();
                fwrite(STDERR, "Guest Route '$route': HTTP $status\n");

                // Assert it returns 200 OK
                $this->assertEquals(200, $status, "Route '$route' failed for guest with status $status");
            } catch (\Throwable $e) {
                fwrite(STDERR, "Guest Route '$route' THREW EXCEPTION: ".$e->getMessage()."\n".$e->getTraceAsString()."\n");
                throw $e;
            }
        }
    }

    public function test_email_verification_routes_are_registered(): void
    {
        $notice = Route::getRoutes()->match(Request::create('/nguoi-dung/verify-email-notice', 'GET'));
        $verify = Route::getRoutes()->match(Request::create('/verify-email?token=example', 'GET'));
        $legacyVerify = Route::getRoutes()->match(Request::create('/nguoi-dung/verify-email/example', 'GET'));
        $resend = Route::getRoutes()->match(Request::create('/nguoi-dung/resend-verify', 'POST'));

        $this->assertSame(AuthController::class.'@showVerifyEmailNotice', $notice->getActionName());
        $this->assertSame(AuthController::class.'@verifyEmail', $verify->getActionName());
        $this->assertSame(AuthController::class.'@verifyEmail', $legacyVerify->getActionName());
        $this->assertSame(AuthController::class.'@resendVerificationEmail', $resend->getActionName());
    }

    /**
     * Test legacy redirects (301 Permanent Redirect).
     */
    public function test_legacy_routes_redirect_to_new_ones(): void
    {
        $redirects = [
            '/trang-chu/pricing' => '/bang-gia',
            '/nguoi-dung/dang-nhap' => '/dang-nhap',
            '/nguoi-dung/dang-ky' => '/dang-ky',
            '/nguoi-dung/register' => '/dang-ky',
        ];

        foreach ($redirects as $legacy => $new) {
            $response = $this->get($legacy);
            $response->assertRedirect($new);
            $this->assertEquals(301, $response->getStatusCode(), "Legacy route '$legacy' did not return 301 Redirect");
        }

        // Test dashboard redirect as authenticated member
        $member = NguoiDung::where('ma_vai_tro', '!=', 1)->first();
        if ($member) {
            $response = $this->actingAs($member)->get('/nguoi-dung/dashboard');
            $response->assertRedirect('/trang-ca-nhan');
            $this->assertEquals(301, $response->getStatusCode(), 'Legacy dashboard route did not return 301 Redirect');
        }
    }

    /**
     * Legacy form actions must resolve to the controller instead of a redirect.
     */
    public function test_legacy_auth_post_routes_are_not_intercepted_by_redirects(): void
    {
        foreach ([
            '/nguoi-dung/dang-nhap',
            '/nguoi-dung/dang-ky',
            '/nguoi-dung/register',
        ] as $route) {
            $matched = Route::getRoutes()->match(Request::create($route, 'POST'));
            $method = $route === '/nguoi-dung/dang-nhap' ? 'login' : 'register';

            $this->assertSame(AuthController::class.'@'.$method, $matched->getActionName());
        }
    }

    /**
     * The registration form embedded in the homepage header submits with AJAX.
     */
    public function test_homepage_registration_returns_json_validation_errors(): void
    {
        $response = $this->post('/dang-ky', [
            'ajax' => '1',
            'ten' => '',
            'email' => '',
            'dien_thoai' => '',
            'mat_khau' => '',
            'mat_khau_xac_nhan' => '',
            'dong_y_dieu_khoan' => '',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure([
                'message',
                'errors' => ['ten', 'email', 'mat_khau', 'mat_khau_xac_nhan', 'dong_y_dieu_khoan'],
            ]);
    }

    public function test_homepage_uses_two_separate_auth_navigation_links(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('guest-register-link', false)
            ->assertSee('/dang-ky"', false)
            ->assertSee('guest-login-link', false)
            ->assertSee('/dang-nhap"', false)
            ->assertDontSee('id="header-login-form"', false)
            ->assertDontSee('id="header-register-form"', false);
    }

    /**
     * Test authenticated routes as Member.
     */
    public function test_member_routes_as_member(): void
    {
        // Find a member in database (ma_vai_tro != 1)
        $member = NguoiDung::where('ma_vai_tro', '!=', 1)->first();
        if (! $member) {
            fwrite(STDERR, "WARNING: No member found in database, skipping member test.\n");
            $this->assertTrue(true);

            return;
        }

        fwrite(STDERR, "Testing as Member: {$member->email} (ID: {$member->id})\n");

        $routes = [
            '/trang-ca-nhan',
        ];

        foreach ($routes as $route) {
            try {
                $response = $this->actingAs($member)->get($route);
                $status = $response->getStatusCode();
                fwrite(STDERR, "Member Route '$route': HTTP $status\n");

                $this->assertEquals(200, $status, "Route '$route' failed for member with status $status");
            } catch (\Throwable $e) {
                fwrite(STDERR, "Member Route '$route' THREW EXCEPTION: ".$e->getMessage()."\n");
                throw $e;
            }
        }
    }

    /**
     * Test admin routes as Admin.
     */
    public function test_admin_routes_as_admin(): void
    {
        // Find an admin in database (ma_vai_tro = 1)
        $admin = NguoiDung::where('ma_vai_tro', 1)->first();
        if (! $admin) {
            fwrite(STDERR, "WARNING: No admin found in database, skipping admin test.\n");
            $this->assertTrue(true);

            return;
        }

        fwrite(STDERR, "Testing as Admin: {$admin->email} (ID: {$admin->id})\n");

        $routes = [
            '/admin',
            '/admin/dashboard',
            '/admin/du-an',
            '/admin/danh-muc',
            '/admin/tin-tuc',
            '/admin/contact',
            '/admin/wallet',
            '/admin/live-chat',
            '/admin/nguoi-dung',
            '/admin/bang-gia',
            '/admin/bao-cao',
            '/admin/cai-dat',
            '/admin/roles',
            '/admin/system-log',
        ];

        foreach ($routes as $route) {
            try {
                $this->withoutExceptionHandling();
                $response = $this->actingAs($admin)->get($route);
                $status = $response->getStatusCode();
                fwrite(STDERR, "Admin Route '$route': HTTP $status\n");

                $this->assertEquals(200, $status, "Route '$route' failed for admin with status $status");
            } catch (\Throwable $e) {
                fwrite(STDERR, "Admin Route '$route' THREW EXCEPTION: ".$e->getMessage()."\n".$e->getTraceAsString()."\n");
                throw $e;
            }
        }
    }

    /**
     * Test dynamic detail pages using slugs from database.
     */
    public function test_detail_routes_with_slugs(): void
    {
        // 1. Project Detail
        $projectSlug = DB::table('du_an')
            ->where('trang_thai', 'xuat_ban')
            ->value('duong_dan');

        if ($projectSlug) {
            $route = "/du-an/{$projectSlug}";
            try {
                $this->withoutExceptionHandling();
                $response = $this->get($route);
                $status = $response->getStatusCode();
                fwrite(STDERR, "Project Detail Route '$route': HTTP $status\n");
                $this->assertEquals(200, $status);
            } catch (\Throwable $e) {
                fwrite(STDERR, "Project Detail Route '$route' THREW EXCEPTION: ".$e->getMessage()."\n".$e->getTraceAsString()."\n");
                throw $e;
            }
        } else {
            fwrite(STDERR, "WARNING: No published project found for detail route test.\n");
        }

        // 2. News Detail
        $newsSlug = DB::table('bai_viet')
            ->where('trang_thai', 'xuat_ban')
            ->value('duong_dan');

        if ($newsSlug) {
            $route = "/tin-tuc/{$newsSlug}";
            try {
                $this->withoutExceptionHandling();
                $response = $this->get($route);
                $status = $response->getStatusCode();
                fwrite(STDERR, "News Detail Route '$route': HTTP $status\n");
                $this->assertEquals(200, $status);
            } catch (\Throwable $e) {
                fwrite(STDERR, "News Detail Route '$route' THREW EXCEPTION: ".$e->getMessage()."\n".$e->getTraceAsString()."\n");
                throw $e;
            }
        } else {
            fwrite(STDERR, "WARNING: No published news post found for detail route test.\n");
        }
    }
}
