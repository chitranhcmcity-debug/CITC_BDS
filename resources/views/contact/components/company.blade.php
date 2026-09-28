<?php
/**
 * Component hiển thị thông tin liên hệ công ty.
 */
?>
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-building me-2"></i>Thông Tin Liên Hệ</h4>
        <p class="text-secondary small mb-4">Mọi thắc mắc hoặc cần giải đáp, hỗ trợ nhanh chóng vui lòng liên hệ với chúng tôi qua các kênh dưới đây hoặc điền vào form tư vấn.</p>

        <!-- Address -->
        <div class="d-flex align-items-start mb-3">
            <span class="text-primary me-3 fs-5 mt-1"><i class="fa-solid fa-location-dot"></i></span>
            <div>
                <h6 class="fw-semibold mb-0 text-dark" style="font-size: 0.95rem;">Địa chỉ trụ sở</h6>
                <p class="text-secondary small mb-0"><?= htmlspecialchars($data['company']['contact_address']) ?></p>
            </div>
        </div>

        <!-- Phone & Hotline -->
        <div class="d-flex align-items-start mb-3">
            <span class="text-success me-3 fs-5 mt-1"><i class="fa-solid fa-phone"></i></span>
            <div>
                <h6 class="fw-semibold mb-0 text-dark" style="font-size: 0.95rem;">Hotline hỗ trợ</h6>
                <p class="text-secondary small mb-0">
                    <a href="tel:<?= $data['company']['contact_phone'] ?>" class="text-decoration-none text-secondary">
                        <?= htmlspecialchars($data['company']['contact_phone']) ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Email -->
        <div class="d-flex align-items-start mb-3">
            <span class="text-danger me-3 fs-5 mt-1"><i class="fa-solid fa-envelope"></i></span>
            <div>
                <h6 class="fw-semibold mb-0 text-dark" style="font-size: 0.95rem;">Email tiếp nhận</h6>
                <p class="text-secondary small mb-0">
                    <a href="mailto:<?= $data['company']['contact_email'] ?>" class="text-decoration-none text-secondary">
                        <?= htmlspecialchars($data['company']['contact_email']) ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Working hours -->
        <div class="d-flex align-items-start mb-4">
            <span class="text-warning me-3 fs-5 mt-1"><i class="fa-solid fa-clock"></i></span>
            <div>
                <h6 class="fw-semibold mb-0 text-dark" style="font-size: 0.95rem;">Giờ làm việc</h6>
                <p class="text-secondary small mb-0"><?= htmlspecialchars($data['company']['working_hours']) ?></p>
            </div>
        </div>

        <!-- Social networks connection -->
        <h5 class="fw-bold mb-3 text-dark pt-3 border-top" style="font-size: 1rem;">Kết nối với chúng tôi</h5>
        <div class="d-flex gap-2">
            <!-- Zalo -->
            <?php if (!empty($data['company']['zalo_number'])): ?>
            <a href="https://zalo.me/<?= $data['company']['zalo_number'] ?>" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center p-0" style="width: 38px; height: 38px; border-radius: 50%;">
                <span class="fw-bold small">Zalo</span>
            </a>
            <?php endif; ?>

            <!-- Facebook -->
            <?php if (!empty($data['company']['facebook_url']) && $data['company']['facebook_url'] !== '#'): ?>
            <a href="<?= $data['company']['facebook_url'] ?>" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center p-0" style="width: 38px; height: 38px; border-radius: 50%;">
                <i class="fa-brands fa-facebook-f"></i>
            </a>
            <?php endif; ?>

            <!-- Messenger -->
            <?php if (!empty($data['company']['zalo_number'])): ?>
            <a href="https://m.me/citcbds" target="_blank" class="btn btn-outline-info d-inline-flex align-items-center justify-content-center p-0" style="width: 38px; height: 38px; border-radius: 50%;">
                <i class="fa-brands fa-facebook-messenger"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
