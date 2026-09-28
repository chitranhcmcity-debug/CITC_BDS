<?php
/**
 * View: Security and Settings - Xác thực Email, Xác thực SĐT (OTP) và Cấu hình Riêng tư.
 * @var object $user Thông tin người dùng
 */
?>
<div class="card profile-card p-4 mb-4">
    <div class="card-body p-2">
        <h4 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-shield text-primary me-2"></i>Bảo mật tài khoản</h4>
        <p class="text-muted small mb-4">Xác thực các thông tin liên hệ của bạn để kích hoạt đầy đủ quyền đăng tin và bảo mật tài khoản.</p>

        <!-- Xác thực Email -->
        <div class="d-flex align-items-center justify-content-between verification-panel mb-3.5">
            <div class="d-flex align-items-center">
                <div class="verification-icon-box <?= !empty($user->email_verified_at) ? 'verified' : 'pending' ?> me-3">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark mb-0.5">Địa chỉ Email</div>
                    <div class="small text-muted"><?= htmlspecialchars($user->email) ?></div>
                </div>
            </div>
            <div>
                <?php if(!empty($user->email_verified_at)): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold">
                        <i class="fa-solid fa-circle-check me-1"></i>Đã xác thực
                    </span>
                <?php else: ?>
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3.5 fw-bold" id="btn-verify-email">
                        <i class="fa-solid fa-paper-plane me-1"></i>Xác thực ngay
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Xác thực Số điện thoại (OTP) -->
        <div class="d-flex align-items-center justify-content-between verification-panel">
            <div class="d-flex align-items-center">
                <div class="verification-icon-box <?= !empty($user->phone_verified) ? 'verified' : 'pending' ?> me-3">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark mb-0.5">Số điện thoại</div>
                    <div class="small text-muted"><?= htmlspecialchars($user->dien_thoai ?: 'Chưa cập nhật SĐT') ?></div>
                </div>
            </div>
            <div>
                <?php if(!empty($user->phone_verified)): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold">
                        <i class="fa-solid fa-circle-check me-1"></i>Đã xác thực
                    </span>
                <?php else: ?>
                    <?php if(empty($user->dien_thoai)): ?>
                        <span class="small text-danger fw-semibold">Hãy cập nhật SĐT trước</span>
                    <?php else: ?>
                        <button type="button" class="btn btn-warning btn-sm rounded-pill px-3.5 fw-bold" id="btn-verify-phone">
                            <i class="fa-solid fa-shield-halved me-1"></i>Xác thực ngay (OTP)
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Cài đặt tài khoản & Quyền riêng tư -->
<div class="card profile-card p-4">
    <div class="card-body p-2">
        <h4 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-gear text-primary me-2"></i>Thiết lập quyền riêng tư</h4>
        <p class="text-muted small mb-4">Tùy chỉnh thông tin liên hệ hiển thị trên tin đăng và các cấu hình thông báo hệ thống.</p>

        <form action="<?= URL_ROOT ?>/nguoi-dung/profile" method="POST">
            <?= Csrf::field() ?>
            <input type="hidden" name="action" value="settings">

            <div class="row g-4">
                <!-- Nhận email thông báo -->
                <div class="col-md-6 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark mb-0.5">Nhận email thông báo</div>
                        <div class="small text-muted">Nhận email gửi tự động về giao dịch và hỗ trợ.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input fs-5" type="checkbox" name="nhan_email" value="1" <?= (int)($user->nhan_email ?? 1) ? 'checked' : '' ?>>
                    </div>
                </div>

                <!-- Nhận Notification -->
                <div class="col-md-6 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark mb-0.5">Nhận thông báo hệ thống</div>
                        <div class="small text-muted">Hiển thị thông báo tức thì trên thanh tiện ích.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input fs-5" type="checkbox" name="nhan_notification" value="1" <?= (int)($user->nhan_notification ?? 1) ? 'checked' : '' ?>>
                    </div>
                </div>

                <div class="col-12"><hr class="my-1.5 opacity-10"></div>

                <!-- Ẩn số điện thoại -->
                <div class="col-md-6 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark mb-0.5">Ẩn số điện thoại liên hệ</div>
                        <div class="small text-muted">Khách xem tin chỉ nhắn tin qua Chat, ẩn SĐT trên trang.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input fs-5" type="checkbox" name="an_sdt" value="1" <?= (int)($user->an_sdt ?? 0) ? 'checked' : '' ?>>
                    </div>
                </div>

                <!-- Ẩn Email -->
                <div class="col-md-6 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark mb-0.5">Ẩn địa chỉ email công khai</div>
                        <div class="small text-muted">Giúp bạn tránh bị quét email hoặc spam ngoài ý muốn.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input fs-5" type="checkbox" name="an_email" value="1" <?= (int)($user->an_email ?? 0) ? 'checked' : '' ?>>
                    </div>
                </div>

                <div class="col-12"><hr class="my-1.5 opacity-10"></div>

                <!-- Cho phép Chat -->
                <div class="col-md-6 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark mb-0.5">Mở chat trực tuyến</div>
                        <div class="small text-muted">Cho phép người xem bắt đầu chat trực tiếp với bạn.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input fs-5" type="checkbox" name="cho_phep_chat" value="1" <?= (int)($user->cho_phep_chat ?? 1) ? 'checked' : '' ?>>
                    </div>
                </div>

                <!-- Cho phép gọi điện -->
                <div class="col-md-6 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark mb-0.5">Đồng ý nhận cuộc gọi tư vấn</div>
                        <div class="small text-muted">Đồng ý nhận các liên hệ chào mua/chào bán qua SĐT.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input fs-5" type="checkbox" name="cho_phep_goi" value="1" <?= (int)($user->cho_phep_goi ?? 1) ? 'checked' : '' ?>>
                    </div>
                </div>

                <!-- Nút lưu cài đặt -->
                <div class="col-12 text-end mt-4">
                    <button type="submit" class="btn btn-premium-primary">
                        <i class="fa-solid fa-shield"></i>Lưu cấu hình riêng tư
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nhập mã OTP SĐT -->
<div class="modal fade profile-otp-modal" id="otpModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="otpModalLabel"><i class="fa-solid fa-shield-halved me-2"></i>Xác minh mã OTP điện thoại</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted small">Mã OTP gồm 6 số được gửi bằng SMS đến số điện thoại<br><strong class="text-dark"><?= htmlspecialchars($user->dien_thoai ?? '') ?></strong>. Hãy nhập mã để hoàn tất xác thực.</p>
                
                <!-- Chỉ hiển thị khi backend xác nhận đang chạy môi trường local -->
                <div class="alert alert-info py-2 fs-7 mb-3 text-start d-none" id="otp-debug-box">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    <strong>Môi trường phát triển:</strong> Mã OTP là
                    <span class="badge bg-primary text-white font-monospace fs-6 px-2 py-1" id="debug-otp-container"></span>.
                </div>

                <div class="d-flex justify-content-center g-2 my-4">
                    <input type="text" class="form-control text-center fs-2 font-monospace px-0" id="otp-input" placeholder="000000" maxlength="6" style="width: 220px; letter-spacing: 5px;">
                </div>

                <p class="small text-muted mb-0">Không nhận được mã? <a href="javascript:void(0)" class="fw-bold" id="resend-otp-link">Gửi lại mã</a></p>
            </div>
            <div class="modal-footer bg-light py-2.5">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-xs" id="btn-submit-otp">Xác nhận mã</button>
            </div>
        </div>
    </div>
</div>

<style>
.profile-otp-modal { z-index: 2060 !important; }
body.profile-otp-modal-open .modal-backdrop { z-index: 2050 !important; }
.profile-otp-modal .modal-dialog { max-height: calc(100vh - 2rem); }
@media (max-width: 575.98px) {
    .profile-otp-modal .modal-dialog { margin: .75rem; max-height: calc(100vh - 1.5rem); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?= Csrf::token() ?>';

    async function readJsonResponse(response) {
        const raw = await response.text();
        let data = {};
        try {
            data = raw ? JSON.parse(raw) : {};
        } catch (error) {
            data = { message: raw || 'Phản hồi máy chủ không hợp lệ.' };
        }
        if (!response.ok) {
            throw new Error(data.message || 'Yêu cầu không thành công.');
        }
        return data;
    }

    // 1. Xác thực Email bằng AJAX
    const btnVerifyEmail = document.getElementById('btn-verify-email');
    if (btnVerifyEmail) {
        btnVerifyEmail.addEventListener('click', function() {
            btnVerifyEmail.disabled = true;
            btnVerifyEmail.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Đang gửi...';

            fetch('<?= URL_ROOT ?>/nguoi-dung/sendVerifyEmail', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: '_csrf_token=' + encodeURIComponent(csrfToken)
            })
            .then(readJsonResponse)
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Đã gửi thành công!',
                        text: data.message,
                        icon: 'success'
                    });
                } else {
                    Swal.fire({
                        title: 'Thất bại',
                        text: data.message,
                        icon: 'error'
                    });
                }
                btnVerifyEmail.disabled = false;
                btnVerifyEmail.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Xác thực ngay';
            })
            .catch((error) => {
                Swal.fire('Lỗi', error.message || 'Không thể kết nối máy chủ gửi thư.', 'error');
                btnVerifyEmail.disabled = false;
                btnVerifyEmail.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Xác thực ngay';
            });
        });
    }

    // 2. Gửi OTP Xác thực SĐT qua AJAX
    const btnVerifyPhone = document.getElementById('btn-verify-phone');
    const otpModalElement = document.getElementById('otpModal');
    if (otpModalElement && otpModalElement.parentElement !== document.body) {
        document.body.appendChild(otpModalElement);
    }
    otpModalElement?.addEventListener('show.bs.modal', () => document.body.classList.add('profile-otp-modal-open'));
    otpModalElement?.addEventListener('hidden.bs.modal', () => document.body.classList.remove('profile-otp-modal-open'));
    const otpModal = new bootstrap.Modal(otpModalElement);
    const otpDebugBox = document.getElementById('otp-debug-box');
    const debugOtpContainer = document.getElementById('debug-otp-container');

    function requestOTP() {
        if (btnVerifyPhone) {
            btnVerifyPhone.disabled = true;
        }
        
        fetch('<?= URL_ROOT ?>/nguoi-dung/sendOTP', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '_csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(readJsonResponse)
        .then(data => {
            if (data.success) {
                document.getElementById('otp-input').value = '';
                if (data.debug_otp) {
                    debugOtpContainer.textContent = data.debug_otp;
                    otpDebugBox.classList.remove('d-none');
                } else {
                    debugOtpContainer.textContent = '';
                    otpDebugBox.classList.add('d-none');
                }
                otpModal.show();
                Swal.fire({ title: data.delivery === 'sms' ? 'Đã gửi OTP qua SMS' : 'Mã OTP điện thoại (local)', text: data.message, icon: 'success', timer: 2600, showConfirmButton: false });
            } else {
                Swal.fire('Thất bại', data.message, 'error');
            }
            if (btnVerifyPhone) {
                btnVerifyPhone.disabled = false;
            }
        })
        .catch((error) => {
            Swal.fire('Lỗi gửi SMS', error.message || 'Không thể gửi mã OTP đến số điện thoại.', 'error');
            if (btnVerifyPhone) {
                btnVerifyPhone.disabled = false;
            }
        });
    }

    if (btnVerifyPhone) {
        btnVerifyPhone.addEventListener('click', requestOTP);
    }
    
    const resendOtpLink = document.getElementById('resend-otp-link');
    if (resendOtpLink) {
        resendOtpLink.addEventListener('click', function() {
            otpModal.hide();
            requestOTP();
        });
    }

    // 3. Xác minh OTP gửi lên qua AJAX
    const btnSubmitOtp = document.getElementById('btn-submit-otp');
    if (btnSubmitOtp) {
        btnSubmitOtp.addEventListener('click', function() {
            const otpCode = document.getElementById('otp-input').value.trim();
            if (otpCode.length !== 6) {
                Swal.fire('Lỗi nhập liệu', 'Vui lòng nhập đầy đủ mã OTP 6 chữ số.', 'warning');
                return;
            }

            btnSubmitOtp.disabled = true;
            btnSubmitOtp.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Đang xử lý...';

            fetch('<?= URL_ROOT ?>/nguoi-dung/verifyOTP', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: '_csrf_token=' + encodeURIComponent(csrfToken) + '&otp=' + encodeURIComponent(otpCode)
            })
            .then(readJsonResponse)
            .then(data => {
                if (data.success) {
                    otpModal.hide();
                    Swal.fire({
                        title: 'Chúc mừng!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Xác thực thất bại', data.message, 'error');
                }
                btnSubmitOtp.disabled = false;
                btnSubmitOtp.innerHTML = 'Xác nhận mã';
            })
            .catch((error) => {
                Swal.fire('Lỗi', error.message || 'Lỗi hệ thống trong lúc xác nhận mã OTP.', 'error');
                btnSubmitOtp.disabled = false;
                btnSubmitOtp.innerHTML = 'Xác nhận mã';
            });
        });
    }
});
</script>
