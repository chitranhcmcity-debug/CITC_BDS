<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấu hình Đăng nhập Mạng xã hội</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding: 30px 15px;
        }
        .error-card {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            overflow: hidden;
        }
        .header-banner {
            padding: 30px;
            text-align: center;
            background: #f1f3f9;
            border-bottom: 1px solid #e9ecef;
        }
        .body-content {
            padding: 30px;
        }
        .step-num {
            width: 24px;
            height: 24px;
            background: #0d6efd;
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
            font-weight: bold;
            margin-right: 8px;
        }
        pre {
            background: #f4f6f8;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<div class="error-card">
    <div class="header-banner">
        <?php if ($provider === 'google'): ?>
            <i class="fa-brands fa-google text-danger fs-1 mb-2"></i>
            <h4 class="fw-bold">Cần cấu hình API Google Client</h4>
        <?php else: ?>
            <i class="fa-brands fa-facebook text-primary fs-1 mb-2"></i>
            <h4 class="fw-bold">Cần cấu hình API Facebook Client</h4>
        <?php endif; ?>
        <p class="text-muted mb-0 small">Vui lòng thiết lập các tham số API thật trong dự án để hoạt động.</p>
    </div>

    <div class="body-content">
        <?php if ($provider === 'google'): ?>
            <h5 class="fw-bold text-dark mb-3">Các bước cài đặt Google Login:</h5>
            <ol class="list-unstyled">
                <li class="mb-3">
                    <span class="step-num">1</span> Truy cập <a href="https://console.cloud.google.com/" target="_blank" class="fw-semibold">Google Cloud Console</a> và tạo mới một dự án.
                </li>
                <li class="mb-3">
                    <span class="step-num">2</span> Đi tới mục **APIs & Services** > **Credentials**, nhấn chọn **Create Credentials** > **OAuth client ID**.
                </li>
                <li class="mb-3">
                    <span class="step-num">3</span> Điền URI chuyển hướng được ủy quyền (Authorized redirect URI):
                    <pre class="mt-2 mb-0"><?= URL_ROOT ?>/nguoi-dung/google-callback</pre>
                </li>
                <li class="mb-3">
                    <span class="step-num">4</span> Mở file <strong class="text-danger">.env</strong> ở thư mục gốc dự án và dán Client ID / Secret nhận được vào:
<pre class="mt-2 mb-0">GOOGLE_CLIENT_ID=Nhập_Google_Client_ID_của_bạn
GOOGLE_CLIENT_SECRET=Nhập_Google_Client_Secret_của_bạn</pre>
                </li>
            </ol>
        <?php else: ?>
            <h5 class="fw-bold text-dark mb-3">Các bước cài đặt Facebook Login:</h5>
            <ol class="list-unstyled">
                <li class="mb-3">
                    <span class="step-num">1</span> Truy cập <a href="https://developers.facebook.com/" target="_blank" class="fw-semibold">Facebook Developers</a> và tạo một App dành cho Consumer.
                </li>
                <li class="mb-3">
                    <span class="step-num">2</span> Thêm sản phẩm **Facebook Login** và cấu hình cài đặt.
                </li>
                <li class="mb-3">
                    <span class="step-num">3</span> Điền URI chuyển hướng OAuth hợp lệ (Valid OAuth Redirect URIs):
                    <pre class="mt-2 mb-0"><?= URL_ROOT ?>/nguoi-dung/facebook-callback</pre>
                </li>
                <li class="mb-3">
                    <span class="step-num">4</span> Mở file <strong class="text-danger">.env</strong> ở thư mục gốc dự án và dán App ID / App Secret vào:
<pre class="mt-2 mb-0">FACEBOOK_APP_ID=Nhập_Facebook_App_ID_của_bạn
FACEBOOK_APP_SECRET=Nhập_Facebook_App_Secret_của_bạn</pre>
                </li>
            </ol>
        <?php endif; ?>

        <div class="mt-4 pt-3 border-top text-center">
            <button class="btn btn-secondary px-4" onclick="window.close()">Đóng cửa sổ</button>
        </div>
    </div>
</div>

</body>
</html>
