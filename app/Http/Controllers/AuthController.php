<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use App\Models\The;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\AuthValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private AuthService $authSvc;

    public function __construct()
    {
        $this->authSvc = new AuthService;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $identifier = $request->input('identifier');
        $password = $request->input('mat_khau');
        $remember = $request->has('remember_me');

        $result = $this->authSvc->login($identifier, $password, $remember);

        if (! $result['success']) {
            if ($request->expectsJson() || $request->boolean('ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'captcha_needed' => $result['captcha_needed'] ?? false,
                    'unverified' => $result['unverified'] ?? false,
                ]);
            }

            return back()->withErrors([
                'general' => $result['message'],
            ])->with([
                'unverified' => $result['unverified'] ?? false,
                'identifier' => $identifier,
            ]);
        }

        // Authenticate standard Laravel Guard with the verified user
        $user = NguoiDung::find($result['user']->id);
        if ($user) {
            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Set sessions compatibility
            session([
                'user_id' => (int) $user->id,
                'user_email' => $user->email,
                'user_name' => $user->ten,
                'user_role_id' => (int) $user->ma_vai_tro,
                'user_email_verified' => ! empty($user->email_verified_at),
            ]);

            if ((int) $user->ma_vai_tro === 1) {
                $redirect = redirect()->intended(route('admin.dashboard'));
            } else {
                $redirect = redirect()->intended(route('member.dashboard'));
            }

            if ($request->expectsJson() || $request->boolean('ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect' => $redirect->getTargetUrl(),
                ]);
            }

            return $redirect;
        }

        if ($request->expectsJson() || $request->boolean('ajax')) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thiết lập phiên đăng nhập.',
            ], 500);
        }

        return back()->withErrors([
            'general' => 'Không thể thiết lập phiên đăng nhập.',
        ]);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $isAjax = $request->expectsJson() || $request->ajax() || $request->boolean('ajax');

        $input = [
            'ten' => trim($request->input('ten', '')),
            'email' => trim($request->input('email', '')),
            'dien_thoai' => trim($request->input('dien_thoai', '')),
            'mat_khau' => $request->input('mat_khau', ''),
            'mat_khau_xac_nhan' => $request->input('mat_khau_xac_nhan', ''),
            'dong_y_dieu_khoan' => $request->input('dong_y_dieu_khoan', ''),
        ];

        // Call validation repository
        $validator = new AuthValidation(new UserRepository);
        $errors = $validator->validateRegister($input);

        if ($errors) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu đăng ký chưa hợp lệ.',
                    'errors' => $errors,
                ], 422);
            }

            return back()->withErrors($errors)->withInput();
        }

        $result = $this->authSvc->register($input);

        if (! $result['success']) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => ['general' => $result['message']],
                ], 500);
            }

            return back()->withErrors(['general' => $result['message']])->withInput();
        }

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => route('login'),
            ], 201);
        }

        return redirect()->route('login')->with('success', $result['message']);
    }

    public function showVerifyEmailNotice(Request $request)
    {
        return view('auth.verify-email-notice', [
            'email' => trim((string) ($request->user()?->email ?? $request->query('email', ''))),
        ]);
    }

    public function verifyEmail(Request $request, string $token = '')
    {
        $token = $token !== '' ? $token : trim((string) $request->query('token', ''));

        if ($token === '') {
            return redirect()->route('verification.notice')
                ->with('verification_error', 'Liên kết xác thực không hợp lệ hoặc thiếu mã xác thực.');
        }

        $result = $this->authSvc->verifyEmail($token);

        return view('auth.verify-email', [
            'title' => 'Xác thực Email – '.SITE_NAME,
            'success' => $result['success'],
            'message' => $result['message'],
            'expired' => $result['expired'] ?? false,
        ]);
    }

    public function resendVerificationEmail(Request $request)
    {
        $identifier = trim((string) $request->input('identifier', ''));
        if ($identifier === '') {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập email.',
            ], 422);
        }

        return response()->json($this->authSvc->resendVerificationEmail($identifier));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clean sessions compatibility
        session()->forget(['user_id', 'user_email', 'user_name', 'user_role_id', 'user_email_verified']);

        return redirect('/');
    }
}
