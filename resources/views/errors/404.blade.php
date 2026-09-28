<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Không tìm thấy trang</title>
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f6f7f9;font-family:Arial,sans-serif;color:#1f2937}
        main{max-width:560px;padding:48px;text-align:center;background:#fff;border-radius:18px;box-shadow:0 15px 40px rgba(0,0,0,.08)}
        h1{font-size:72px;margin:0;color:#0d6efd} h2{margin:8px 0 12px} p{color:#6b7280;line-height:1.6}
        a{display:inline-block;margin-top:14px;padding:12px 22px;border-radius:8px;background:#0d6efd;color:#fff;text-decoration:none;font-weight:700}
    </style>
</head>
<body>
<main>
    <h1>404</h1>
    <h2>Không tìm thấy trang</h2>
    <p>Đường dẫn không tồn tại hoặc đã được thay đổi.</p>
    <a href="<?= htmlspecialchars(URL_ROOT) ?>/">Về trang chủ</a>
</main>
</body>
</html>
