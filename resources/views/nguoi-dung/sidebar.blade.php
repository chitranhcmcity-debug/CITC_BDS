        <!-- Sidebar Menu (Left Column) -->
        <div class="col-lg-3 col-md-4">
            <!-- NguoiDung Info Box -->
            <div class="card shadow-sm mb-4 border-0 rounded-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode(Session::get('user_name') ?: 'NguoiDung') ?>&background=random" class="rounded-circle me-3" width="50" height="50" alt="Avatar">
                        <div>
                            <h6 class="fw-bold mb-0"><?= Session::get('user_name') ?: 'NguoiDung' ?></h6>
                            <small class="text-muted">Mã thành viên: <b><?= Session::get('user_id') ?: '0' ?></b></small>
                        </div>
                    </div>
                    <hr>
                    <?php
                        $currentUser = $data['layout']['currentUser'] ?? null;
                        $balance = (int)($currentUser->so_du ?? 0);
                        $luot_up_tin = (int)($currentUser->luot_up_tin ?? 0);
                        $current_uri = $_SERVER['REQUEST_URI'] ?? '';
                    ?>
                    <p class="mb-1 small">Số dư tài khoản: <b class="text-danger"><?= number_format($balance) ?> VNĐ</b></p>
                    <p class="mb-1 small">Số lượt UP tin: <b class="text-primary"><?= number_format($luot_up_tin) ?> lượt</b></p>
                    <a href="<?= URL_ROOT ?>/vi-dien-tu" class="text-decoration-none small" style="color: #0d6efd;"><em>Chi tiết ví & Lịch sử giao dịch</em></a>
                    <p class="mt-2 mb-3 small"><em>Khuyến mãi <span class="text-danger">20%</span> đến <span class="text-danger">200%</span> tiền nạp</em></p>
                    <a href="<?= URL_ROOT ?>/vi-dien-tu" class="btn btn-success w-100 fw-semibold rounded-1">Nạp tiền vào tài khoản</a>
                </div>
            </div>

            <!-- Menu Links -->
            <div class="card shadow-sm mb-4 border-0 rounded-3">
                <div class="list-group list-group-flush rounded-3">
                    <a href="<?= URL_ROOT ?>/nguoi-dung/post" class="list-group-item list-group-item-action <?= (strpos($current_uri, 'nguoi-dung/post') !== false) ? 'text-danger fw-bold' : 'fw-semibold' ?> py-2"><i class="fa-solid fa-pen-to-square me-1 text-muted"></i> Đăng tin BĐS</a>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/dashboard" class="list-group-item list-group-item-action <?= (strpos($current_uri, 'nguoi-dung/dashboard') !== false) ? 'text-danger fw-bold' : 'fw-semibold' ?> py-2"><i class="fa-solid fa-gauge-high me-1 text-muted"></i> Dashboard</a>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/dashboard#recent-posts" class="list-group-item list-group-item-action fw-semibold py-2"><i class="fa-solid fa-list me-1 text-muted"></i> Quản lý tin đăng</a>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/analytics" class="list-group-item list-group-item-action <?= (strpos($current_uri, 'nguoi-dung/analytics') !== false || strpos($current_uri, 'postAnalytics') !== false) ? 'text-danger fw-bold' : 'fw-semibold' ?> py-2"><i class="fa-solid fa-chart-line me-1 text-muted"></i> Thống kê</a>
                    <a href="<?= URL_ROOT ?>/vi-dien-tu" class="list-group-item list-group-item-action <?= (strpos($current_uri, 'vi-dien-tu') !== false || strpos($current_uri, 'wallet') !== false) ? 'text-danger fw-bold' : 'fw-semibold' ?> py-2"><i class="fa-solid fa-wallet me-1 text-muted"></i> Ví Điện Tử & Ưu đãi</a>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/daLuu" class="list-group-item list-group-item-action <?= (strpos($current_uri, 'nguoi-dung/daLuu') !== false) ? 'text-danger fw-bold' : 'fw-semibold' ?> py-2 d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-heart me-1 text-danger"></i> Tin BĐS đã lưu</span>
                        <?php
                            $savedCount = (int)($data['layout']['savedProjectsCount'] ?? 0);
                            if ($savedCount > 0):
                        ?>
                        <span class="badge bg-danger rounded-pill"><?= $savedCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/dashboard#dashboard-notifications" class="list-group-item list-group-item-action fw-semibold py-2"><i class="fa-solid fa-bell me-1 text-muted"></i> Thông báo</a>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/profile" class="list-group-item list-group-item-action <?= (strpos($current_uri, 'nguoi-dung/profile') !== false) ? 'text-danger fw-bold' : 'fw-semibold' ?> py-2"><i class="fa-solid fa-user me-1 text-muted"></i> Hồ sơ</a>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/change_password" class="list-group-item list-group-item-action <?= (strpos($current_uri, 'nguoi-dung/change_password') !== false) ? 'text-danger fw-bold' : 'fw-semibold' ?> py-2"><i class="fa-solid fa-lock me-1 text-muted"></i> Đổi mật khẩu</a>
                    <form action="<?= URL_ROOT ?>/nguoi-dung/logout" method="POST" class="m-0">
                        <?= Csrf::field() ?>
                        <button type="submit" class="list-group-item list-group-item-action fw-semibold py-2 text-secondary border-0 w-100 text-start"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</button>
                    </form>
                </div>
            </div>

            <!-- Referral Link -->
            <div class="card shadow-sm mb-4 border-0 rounded-3">
                <div class="card-header bg-white fw-bold small">
                    Tặng 100K khi giới thiệu bạn bè đăng ký tài khoản.
                </div>
                <div class="card-body p-3">
                    <p class="small mb-1"><em>Link giới thiệu gửi bạn bè đăng ký:</em></p>
                    <input type="text" class="form-control form-control-sm mb-2" id="referralLinkInput" value="<?= URL_ROOT ?>/dang-ky?friend_id=<?= Session::get('user_id') ?>" readonly>
                    <button class="btn btn-success btn-sm w-100 rounded-1" onclick="copyReferralLink(this)">Copy link chia sẻ</button>
                    <script>
                    function copyReferralLink(btn) {
                        var copyText = document.getElementById("referralLinkInput");
                        copyText.select();
                        copyText.setSelectionRange(0, 99999); // For mobile devices
                        navigator.clipboard.writeText(copyText.value).then(function() {
                            var originalText = btn.innerText;
                            btn.innerText = "Đã copy thành công!";
                            btn.classList.replace('btn-success', 'btn-secondary');
                            setTimeout(function() {
                                btn.innerText = originalText;
                                btn.classList.replace('btn-secondary', 'btn-success');
                            }, 2000);
                        });
                    }
                    </script>
                </div>
            </div>
            
            <!-- Ecosystem -->
            <div class="card shadow-sm mb-4 border-0 rounded-3">
                <div class="card-header bg-white fw-bold small text-center">
                    Hệ sinh thái website đăng tin hiệu quả.
                </div>
                <div class="card-body p-3 small">
                    <p class="mb-2"><em>Một số website đăng tin hiệu quả bạn có thể tham khảo:</em></p>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1"><a href="#" class="text-decoration-none text-dark">GiaodichNhadat.vn</a></li>
                        <li class="mb-1"><a href="#" class="text-decoration-none text-dark">NhadatToanquoc.com.vn</a></li>
                        <li class="mb-1"><a href="#" class="text-decoration-none text-dark">Batdongsan368.com.vn</a></li>
                        <li class="mb-1"><a href="#" class="text-decoration-none text-dark">Nhadatuytin.com.vn</a></li>
                        <li class="mb-1"><a href="#" class="text-decoration-none text-dark">Quangcaonhadat.com.vn</a></li>
                        <li><a href="#" class="text-decoration-none text-dark">Raovatnhadat.net</a></li>
                    </ul>
                </div>
            </div>
        </div>
