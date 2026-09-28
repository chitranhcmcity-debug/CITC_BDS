@include('admin.layouts.header')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Cài Đặt Hệ Thống</h1>
    <button class="btn btn-sm btn-success"><i class="fa-solid fa-floppy-disk me-1"></i> Lưu Cài Đặt</button>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
        <h5 class="mb-0 text-primary"><i class="fa-solid fa-globe me-2"></i> Thông Tin Website</h5>
    </div>
    <div class="card-body">
        <form action="<?= URL_ROOT ?>/admin/cai-dat/save" method="POST">
            <?= Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label fw-bold">Tên Website</label>
                <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($data['settings']['site_name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Địa Chỉ Giao Dịch</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($data['settings']['address'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Số Hotline Liên Hệ</label>
                <input type="text" name="hotline" class="form-control" value="<?= htmlspecialchars($data['settings']['hotline'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Email Nhận Thông Báo</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['settings']['email'] ?? '') ?>">
            </div>
            
            <hr class="my-4">
            
            <h5 class="mb-3 text-primary"><i class="fa-solid fa-share-nodes me-2"></i> Mạng Xã Hội</h5>
            <div class="mb-3">
                <label class="form-label fw-bold">Link Facebook</label>
                <input type="url" name="facebook" class="form-control" value="<?= htmlspecialchars($data['settings']['facebook'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Link Zalo (VD: https://zalo.me/sdt)</label>
                <input type="url" name="zalo" class="form-control" value="<?= htmlspecialchars($data['settings']['zalo'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Link YouTube</label>
                <input type="url" name="youtube" class="form-control" value="<?= htmlspecialchars($data['settings']['youtube'] ?? '') ?>">
            </div>

            <hr class="my-4">
            
            <h5 class="mb-3 text-primary"><i class="fa-solid fa-envelope me-2"></i> Cấu Hình Email (SMTP)</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">SMTP Server (Host)</label>
                    <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($data['settings']['smtp_host'] ?? '') ?>" placeholder="Ví dụ: smtp.gmail.com">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">SMTP Port</label>
                    <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($data['settings']['smtp_port'] ?? '587') ?>" placeholder="587 hoặc 465">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Mã Hóa (Encryption)</label>
                    <select name="smtp_secure" class="form-select">
                        <option value="none" <?= ($data['settings']['smtp_secure'] ?? '') === 'none' ? 'selected' : '' ?>>Không mã hóa (None)</option>
                        <option value="tls" <?= ($data['settings']['smtp_secure'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Đề xuất)</option>
                        <option value="ssl" <?= ($data['settings']['smtp_secure'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Xác Thực SMTP (SMTP Auth)</label>
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" name="smtp_auth" class="form-check-input" id="smtp_auth" value="1" <?= ($data['settings']['smtp_auth'] ?? '') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="smtp_auth">Yêu cầu xác thực tài khoản</label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Tài Khoản SMTP (Username)</label>
                    <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($data['settings']['smtp_user'] ?? '') ?>" placeholder="Tài khoản email gửi thư">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Mật Khẩu SMTP (Password)</label>
                    <input type="password" name="smtp_pass" class="form-control" value="" autocomplete="new-password" placeholder="Để trống nếu không thay đổi mật khẩu ứng dụng">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Email Người Gửi (From Email)</label>
                    <input type="email" name="smtp_from_email" class="form-control" value="<?= htmlspecialchars($data['settings']['smtp_from_email'] ?? '') ?>" placeholder="Để trống nếu giống Tài Khoản SMTP">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tên Người Gửi (From Name)</label>
                    <input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($data['settings']['smtp_from_name'] ?? '') ?>" placeholder="Ví dụ: TimNhaDat.site">
                </div>
            </div>

            <!-- Gửi Thử Email Card/Block -->
            <div class="bg-light p-3 rounded-3 mb-3 border">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <label class="form-label fw-bold mb-1"><i class="fa-solid fa-vial-circle-check text-primary me-1"></i> Kiểm tra kết nối gửi thư (SMTP Test)</label>
                        <div class="input-group">
                            <input type="email" id="test_email" class="form-control" placeholder="Nhập địa chỉ email nhận...">
                            <button type="button" class="btn btn-primary" id="btn-test-email"><i class="fa-solid fa-paper-plane me-1"></i> Gửi Thử</button>
                        </div>
                        <div class="form-text text-muted">Vui lòng nhấn <strong>Lưu Cài Đặt</strong> trước khi thực hiện gửi thử để áp dụng cấu hình mới nhất.</div>
                    </div>
                    <div class="col-md-4">
                        <div id="test-email-status" class="mt-2 mt-md-0 fw-bold" style="display:none; font-size: 0.9rem;"></div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-1"></i> Lưu Cài Đặt</button>
            </div>
        </form>
    </div>
</div>

<form action="<?= URL_ROOT ?>/admin/cai-dat/save" method="POST" class="card border-0 shadow-sm mb-4">
<?= Csrf::field() ?><div class="card-header bg-white fw-bold"><i class="fa-solid fa-sliders me-2 text-primary"></i>Cấu hình tích hợp</div><div class="card-body"><div class="accordion" id="extendedSettings">
<?php $groups=[
'seo'=>['SEO & đo lường',['meta_title'=>'Tiêu đề mặc định','meta_description'=>'Mô tả mặc định','keywords'=>'Từ khóa mặc định','google_analytics'=>'Mã Google Analytics','facebook_pixel'=>'Mã Facebook Pixel']],
'payment'=>['Thanh toán & bản đồ',['payos_client_id'=>'Mã khách hàng PayOS','payos_api_key'=>'Khóa API PayOS','payos_checksum_key'=>'Khóa kiểm tra PayOS','payos_return_url'=>'Đường dẫn trả về','payos_webhook_url'=>'Đường dẫn Webhook','google_maps_key'=>'Khóa API Google Maps']],
'upload'=>['Tải lên',['upload_max_mb'=>'Dung lượng tối đa (MB)','image_extensions'=>'Định dạng ảnh cho phép']]
]; foreach($groups as$key=>$g):?><div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button <?=$key==='seo'?'':'collapsed'?>" type="button" data-bs-toggle="collapse" data-bs-target="#set-<?=$key?>"><?=$g[0]?></button></h2><div id="set-<?=$key?>" class="accordion-collapse collapse <?=$key==='seo'?'show':''?>" data-bs-parent="#extendedSettings"><div class="accordion-body"><div class="row g-3"><?php foreach($g[1] as$name=>$label):$secret=str_contains($name,'key');$saved=$secret&&!empty($data['settings'][$name]);?><div class="col-md-6"><label class="form-label fw-semibold"><?=$label?><?php if($saved):?><span class="badge text-bg-success ms-2">Đã lưu ••••<?=htmlspecialchars(substr((string)$data['settings'][$name],-4))?></span><?php endif;?></label><?php if($name==='meta_description'):?><textarea name="<?=$name?>" class="form-control" rows="2"><?=htmlspecialchars($data['settings'][$name]??'')?></textarea><?php else:?><input type="<?=$secret?'password':($name==='upload_max_mb'?'number':'text')?>" name="<?=$name?>" class="form-control" value="<?=$secret?'':htmlspecialchars($data['settings'][$name]??'')?>" placeholder="<?=$secret?($saved?'Đã lưu - để trống nếu không thay đổi':'Nhập khóa API'):''?>"><?php endif;?></div><?php endforeach;?></div><?php if($key==='payment'):?><div class="mt-3"><button type="button" class="btn btn-outline-primary" id="btn-test-payos"><i class="fa-solid fa-plug me-1"></i>Kiểm tra kết nối PayOS</button><span id="payos-test-status" class="small ms-2"></span><div class="form-text">Hãy lưu cấu hình trước khi kiểm tra.</div></div><?php endif;?></div></div></div><?php endforeach;?></div><div class="text-end mt-3"><button class="btn btn-success px-4"><i class="fa-solid fa-floppy-disk me-1"></i>Lưu cấu hình</button></div></div></form>

<div class="row g-3 mb-4">
  <div class="col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white fw-bold"><i class="fa-solid fa-broom me-2 text-warning"></i>Quản lý Cache</div><div class="card-body d-flex flex-wrap gap-2"><?php foreach(['all'=>'Tất cả','config'=>'Cấu hình','route'=>'Route','view'=>'Giao diện'] as$type=>$label):?><form action="<?=URL_ROOT?>/admin/cai-dat/cache-clear" method="POST"><?=Csrf::field()?><input type="hidden" name="type" value="<?=$type?>"><button class="btn btn-outline-warning btn-sm" onclick="return confirm('Xóa cache <?=$label?>?')"><?=$label?></button></form><?php endforeach;?></div></div></div>
  <div class="col-lg-8"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2"><span class="fw-bold"><i class="fa-solid fa-database me-2 text-success"></i>Sao lưu hệ thống</span><div class="d-flex flex-wrap gap-1"><?php foreach(['database'=>'Cơ sở dữ liệu','uploads'=>'Tệp tải lên','source'=>'Mã nguồn','full'=>'Toàn bộ'] as$type=>$label):?><form action="<?=URL_ROOT?>/admin/cai-dat/backup" method="POST"><?=Csrf::field()?><input type="hidden" name="type" value="<?=$type?>"><button class="btn btn-success btn-sm" onclick="return confirm('Tạo bản sao lưu <?=$label?>?')"><?=$label?></button></form><?php endforeach;?></div></div></div><div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>File</th><th>Loại</th><th>Dung lượng</th><th>Người tạo</th><th>Thời gian</th><th class="text-end">Thao tác</th></tr></thead><tbody><?php foreach(($data['backups']??[]) as$b):?><tr><td class="small"><?=htmlspecialchars($b->file_name)?></td><td><span class="badge bg-light text-dark border"><?=htmlspecialchars($b->type)?></span></td><td><?=number_format($b->file_size/1024,1)?> KB</td><td><?=htmlspecialchars($b->creator??'Quản trị viên')?></td><td><?=date('d/m/Y H:i',strtotime($b->created_at))?></td><td class="text-end"><a class="btn btn-outline-primary btn-sm" href="<?=URL_ROOT?>/admin/cai-dat/download-backup/<?=$b->id?>" title="Tải xuống"><i class="fa-solid fa-download"></i></a><form class="d-inline" method="POST" action="<?=URL_ROOT?>/admin/cai-dat/delete-backup/<?=$b->id?>" onsubmit="return confirm('Xóa bản sao lưu này?')"><?=Csrf::field()?><button class="btn btn-outline-danger btn-sm" title="Xóa"><i class="fa-solid fa-trash"></i></button></form></td></tr><?php endforeach;?><?php if(empty($data['backups'])):?><tr><td colspan="6" class="text-center text-muted py-3">Chưa có bản sao lưu</td></tr><?php endif;?></tbody></table></div></div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTest = document.getElementById('btn-test-email');
    const inputEmail = document.getElementById('test_email');
    const statusDiv = document.getElementById('test-email-status');

    if (btnTest) {
        btnTest.addEventListener('click', function() {
            const emailVal = inputEmail.value.trim();
            if (!emailVal) {
                alert('Vui lòng nhập địa chỉ email nhận để gửi thử!');
                return;
            }

            // Simple email validation regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailVal)) {
                alert('Vui lòng nhập một địa chỉ email hợp lệ!');
                return;
            }

            btnTest.disabled = true;
            const originalText = btnTest.innerHTML;
            btnTest.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang gửi...';
            statusDiv.style.display = 'block';
            statusDiv.className = 'mt-2 mt-md-0 fw-bold text-warning';
            statusDiv.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang kết nối SMTP và gửi thư...';

            const formData = new FormData();
            formData.append('test_email', emailVal);
            formData.append('_csrf_token', '<?= Csrf::token() ?>');

            fetch('<?= URL_ROOT ?>/admin/cai-dat/testEmail', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Lỗi phản hồi từ máy chủ.');
                }
                return response.json();
            })
            .then(data => {
                btnTest.disabled = false;
                btnTest.innerHTML = originalText;
                if (data.success) {
                    statusDiv.className = 'mt-2 mt-md-0 fw-bold text-success';
                    statusDiv.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + data.message;
                } else {
                    statusDiv.className = 'mt-2 mt-md-0 fw-bold text-danger';
                    statusDiv.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> ' + data.message;
                }
            })
            .catch(error => {
                btnTest.disabled = false;
                btnTest.innerHTML = originalText;
                statusDiv.className = 'mt-2 mt-md-0 fw-bold text-danger';
                statusDiv.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> Lỗi hệ thống: ' + error.message;
                console.error(error);
            });
        });
    }

    const payosButton = document.getElementById('btn-test-payos');
    const payosStatus = document.getElementById('payos-test-status');
    if (payosButton) payosButton.addEventListener('click', function () {
        payosButton.disabled = true;
        payosStatus.className = 'small ms-2 text-warning';
        payosStatus.textContent = 'Đang kiểm tra...';
        const body = new FormData(); body.append('_csrf_token', '<?= Csrf::token() ?>');
        fetch('<?= URL_ROOT ?>/admin/cai-dat/test-payos', {method:'POST', body})
          .then(response => response.json())
          .then(data => { payosStatus.className = 'small ms-2 ' + (data.success ? 'text-success' : 'text-danger'); payosStatus.textContent = data.message; })
          .catch(() => { payosStatus.className = 'small ms-2 text-danger'; payosStatus.textContent = 'Không thể kết nối máy chủ.'; })
          .finally(() => { payosButton.disabled = false; });
    });
});
</script>

@include('admin.layouts.footer')
