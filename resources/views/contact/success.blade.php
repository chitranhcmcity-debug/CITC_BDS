@include('layouts.header')

<section class="py-5 bg-light d-flex align-items-center" style="min-height: 70vh;">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6 col-xl-5">
                <div class="card border-0 shadow-sm p-4 p-md-5" style="border-radius: 16px;">
                    <div class="card-body">
                        <!-- Success Icon -->
                        <div class="text-success mb-4" style="font-size: 5rem;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        
                        <!-- Thank you message -->
                        <h2 class="fw-bold text-dark mb-3">Gửi Yêu Cầu Thành Công!</h2>
                        <p class="text-secondary mb-4 leading-relaxed">
                            <?= htmlspecialchars($data['message']) ?>
                        </p>
                        
                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <a href="<?= URL_ROOT ?>/trang-chu" class="btn btn-primary py-2.5 fw-bold" style="border-radius: 8px;">
                                <i class="fa-solid fa-house me-2"></i>Quay lại Trang chủ
                            </a>
                            
                            <?php if (Session::get('user_id')): ?>
                                <a href="<?= URL_ROOT ?>/contact/history" class="btn btn-outline-secondary py-2.5 fw-bold" style="border-radius: 8px;">
                                    <i class="fa-solid fa-clock-rotate-left me-2"></i>Xem Lịch sử yêu cầu
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('layouts.footer')
