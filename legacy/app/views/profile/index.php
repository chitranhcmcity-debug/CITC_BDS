<?php
/**
 * View: Profile Index - Trang quản lý hồ sơ chính.
 * Hỗ trợ tab 'info' và tab 'security' để mang lại trải nghiệm liền mạch.
 * @var object $user Thông tin người dùng
 */
require_once APP_ROOT . '/app/views/layouts/header.php';
?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/premium-profile.css?v=<?= time() ?>">
<?php

$activeTab = $_GET['tab'] ?? 'info';
?>

<div class="container py-4 profile-animate-fade-in">
    <div class="row">
        <!-- Sidebar Menu (Left Column) -->
        <?php require_once APP_ROOT . '/app/views/nguoi-dung/sidebar.php'; ?>

        <!-- Main Content (Right Column) -->
        <div class="col-lg-9 col-md-8">
            
            <!-- Success/Error Messages -->
            <?php if (Session::get('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs" role="alert" style="border-radius: 12px;">
                    <i class="fa-solid fa-circle-check me-2"></i><strong>Thành công!</strong> <?= Session::get('success'); Session::delete('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (Session::get('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-xs" role="alert" style="border-radius: 12px;">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Lỗi!</strong> <?= Session::get('error'); Session::delete('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Sub Tab Navigation -->
            <div class="mb-4">
                <div class="capsule-tabs-container">
                    <div class="nav-item">
                        <a class="nav-link <?= $activeTab === 'info' ? 'active' : '' ?>" href="?tab=info" role="tab">
                            <i class="fa-solid fa-user-pen"></i>Thông tin cá nhân
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link <?= $activeTab === 'security' ? 'active' : '' ?>" href="?tab=security" role="tab">
                            <i class="fa-solid fa-user-shield"></i>Bảo mật & Xác thực
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content" id="profileTabContent">
                <?php if ($activeTab === 'info'): ?>
                    <!-- Tab: Personal Info -->
                    <div class="row">
                        <!-- Avatar Column (Left side) -->
                        <div class="col-lg-4 col-12 mb-4 mb-lg-0">
                            <?php require_once APP_ROOT . '/app/views/components/avatar.php'; ?>
                        </div>
                        <!-- Profile Form Column (Right side) -->
                        <div class="col-lg-8 col-12">
                            <div class="card profile-card profile-info-card">
                                <div class="card-body">
                                    <div class="profile-form-heading">
                                        <span class="profile-form-heading-icon"><i class="fa-solid fa-id-card"></i></span>
                                        <div>
                                            <h4>Thông tin tài khoản</h4>
                                            <p>Cập nhật thông tin cá nhân của bạn</p>
                                        </div>
                                    </div>
                                    <?php require_once APP_ROOT . '/app/views/components/profile-form.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($activeTab === 'security'): ?>
                    <!-- Tab: Security & Privacy -->
                    <?php require_once APP_ROOT . '/app/views/profile/security.php'; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
