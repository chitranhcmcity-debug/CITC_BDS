<?php
/**
 * Component Form gửi yêu cầu tư vấn / liên hệ.
 */
$recaptchaSiteKey = getenv('RECAPTCHA_SITE_KEY');
?>
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-paper-plane me-2"></i>Gửi Yêu Cầu Tư Vấn</h4>
        <p class="text-secondary small mb-4">Vui lòng cung cấp đầy đủ thông tin bên dưới, chuyên viên của chúng tôi sẽ tiếp nhận và liên hệ tư vấn trong 15 phút.</p>

        <form id="contact-form" action="<?= URL_ROOT ?>/contact/store" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">

            <!-- Fullname -->
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Họ và tên <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="fullname" class="form-control text-dark <?= isset($data['errors']['fullname']) ? 'is-invalid' : '' ?>" placeholder="Nhập họ và tên của bạn" value="<?= htmlspecialchars($data['fullname']) ?>" required>
                    <?php if (isset($data['errors']['fullname'])): ?>
                        <div class="invalid-feedback"><?= $data['errors']['fullname'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Phone & Email Grid -->
            <div class="row">
                <!-- Phone -->
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Số điện thoại <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-phone"></i></span>
                        <input type="tel" name="phone" class="form-control text-dark <?= isset($data['errors']['phone']) ? 'is-invalid' : '' ?>" placeholder="Ví dụ: 0901234567" value="<?= htmlspecialchars($data['phone']) ?>" required>
                        <?php if (isset($data['errors']['phone'])): ?>
                            <div class="invalid-feedback"><?= $data['errors']['phone'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Địa chỉ Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control text-dark <?= isset($data['errors']['email']) ? 'is-invalid' : '' ?>" placeholder="Ví dụ: name@example.com" value="<?= htmlspecialchars($data['email']) ?>">
                        <?php if (isset($data['errors']['email'])): ?>
                            <div class="invalid-feedback"><?= $data['errors']['email'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Request Type & Subject Grid -->
            <div class="row">
                <!-- Request Type -->
                <div class="col-md-5 mb-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Loại nhu cầu</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-list"></i></span>
                        <select name="type" class="form-select text-dark <?= isset($data['errors']['type']) ? 'is-invalid' : '' ?>">
                            <option value="khac" <?= $data['type'] === 'khac' ? 'selected' : '' ?>>Khác</option>
                            <option value="mua_nha" <?= $data['type'] === 'mua_nha' ? 'selected' : '' ?>>Mua nhà</option>
                            <option value="thue_nha" <?= $data['type'] === 'thue_nha' ? 'selected' : '' ?>>Thuê nhà</option>
                            <option value="dang_ban" <?= $data['type'] === 'dang_ban' ? 'selected' : '' ?>>Đăng bán</option>
                            <option value="dang_cho_thue" <?= $data['type'] === 'dang_cho_thue' ? 'selected' : '' ?>>Đăng cho thuê</option>
                            <option value="hop_tac" <?= $data['type'] === 'hop_tac' ? 'selected' : '' ?>>Hợp tác kinh doanh</option>
                            <option value="khieu_nai" <?= $data['type'] === 'khieu_nai' ? 'selected' : '' ?>>Khiếu nại</option>
                        </select>
                        <?php if (isset($data['errors']['type'])): ?>
                            <div class="invalid-feedback"><?= $data['errors']['type'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Subject -->
                <div class="col-md-7 mb-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Tiêu đề yêu cầu</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-pen"></i></span>
                        <input type="text" name="subject" class="form-control text-dark <?= isset($data['errors']['subject']) ? 'is-invalid' : '' ?>" placeholder="Nhập tóm tắt tiêu đề yêu cầu" value="<?= htmlspecialchars($data['subject']) ?>">
                        <?php if (isset($data['errors']['subject'])): ?>
                            <div class="invalid-feedback"><?= $data['errors']['subject'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Nội dung chi tiết</label>
                <textarea name="content" rows="4" class="form-control text-dark" placeholder="Ghi rõ nội dung câu hỏi, thông số bất động sản cần ký gửi, mua bán, thuê hoặc khiếu nại..."><?= htmlspecialchars($data['content']) ?></textarea>
            </div>

            <!-- Attachment uploads -->
            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary mb-1"><i class="fa-solid fa-paperclip me-1"></i>Tải lên tài liệu đính kèm (Tối đa 10MB)</label>
                <input type="file" name="attachments[]" class="form-control text-dark" multiple accept=".pdf,.docx,.png,.jpg,.jpeg">
                <div class="form-text text-muted" style="font-size: 0.75rem;">Định dạng cho phép: .PDF, .DOCX, .PNG, .JPG, .JPEG. Tối đa 10MB/file.</div>
            </div>

            <!-- reCAPTCHA if config keys present -->
            <?php if (!empty($recaptchaSiteKey)): ?>
                <div class="mb-4">
                    <div class="g-recaptcha" data-sitekey="<?= $recaptchaSiteKey ?>"></div>
                    <?php if (isset($data['errors']['recaptcha'])): ?>
                        <div class="text-danger small mt-1"><?= $data['errors']['recaptcha'] ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold d-flex justify-content-center align-items-center" id="contact-submit-btn" style="border-radius: 8px;">
                <span class="btn-text"><i class="fa-solid fa-paper-plane me-2"></i>Gửi Yêu Cầu</span>
                <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>
