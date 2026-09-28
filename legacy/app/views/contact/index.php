<?php require_once '../app/views/layouts/header.php'; ?>

<!-- Breadcrumb / Header Section -->
<div class="py-5 text-white" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
    <div class="container py-3 text-center">
        <h1 class="display-5 fw-bold mb-2">Liên Hệ & Gửi Yêu Cầu</h1>
        <p class="lead mb-0 text-white-50">Kết nối với chúng tôi để nhận tư vấn mua bán, cho thuê và ký gửi bất động sản.</p>
    </div>
</div>

<section class="py-5 bg-light">
    <div class="container">
        
        <!-- Flash messages for error or success -->
        <?php if (Session::flash('contact_error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                <i class="fa-solid fa-circle-exclamation me-2"></i><?= Session::flash('contact_error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column: Company Info & Map -->
            <div class="col-lg-5 col-xl-4">
                <!-- Company Info Component -->
                <?php require APP_ROOT . '/app/views/contact/components/company.php'; ?>

                <!-- Google Maps Component -->
                <?php require APP_ROOT . '/app/views/contact/components/map.php'; ?>
            </div>

            <!-- Right Column: Contact Form -->
            <div class="col-lg-7 col-xl-8">
                <!-- Form Component -->
                <?php require APP_ROOT . '/app/views/contact/components/form.php'; ?>
            </div>
        </div>
    </div>
</section>

<!-- reCAPTCHA API Script if key is set -->
<?php if (!empty(getenv('RECAPTCHA_SITE_KEY'))): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const contactForm = document.getElementById('contact-form');
    const submitBtn = document.getElementById('contact-submit-btn');
    
    if (contactForm && submitBtn) {
        contactForm.addEventListener('submit', function() {
            // Hiển thị loading spinner trên nút submit
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            if (btnText && spinner) {
                btnText.classList.add('opacity-50');
                spinner.classList.remove('d-none');
            }
            submitBtn.disabled = true;
        });
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
