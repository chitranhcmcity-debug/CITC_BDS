<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <?php require_once '../app/views/nguoi-dung/sidebar.php'; ?>

        <div class="col-lg-9 col-md-8">
            <h4 class="fw-bold mb-4 border-bottom pb-2">
                <i class="fa-solid fa-key me-2 text-danger"></i>Đổi mật khẩu
            </h4>

            <?php if (Session::get('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    <?= htmlspecialchars(Session::get('success')); Session::delete('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (Session::get('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <?= htmlspecialchars(Session::get('error')); Session::delete('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="<?= URL_ROOT ?>/nguoi-dung/change_password" method="POST" id="formDoiMK" novalidate>
                        <?= Csrf::field() ?>

                        <!-- Mật khẩu hiện tại -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label fw-bold text-md-end">
                                Mật khẩu hiện tại <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="password" id="current_password" name="current_password"
                                           class="form-control" required
                                           placeholder="Nhập mật khẩu hiện tại"
                                           autocomplete="current-password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="current_password" tabindex="-1">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Mật khẩu mới -->
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label fw-bold text-md-end">
                                Mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="password" id="new_password" name="new_password"
                                           class="form-control" required minlength="6"
                                           placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="new_password" tabindex="-1">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <!-- Thanh độ mạnh -->
                                <div class="mt-2" id="strengthWrap" style="display:none;">
                                    <div class="progress" style="height:5px;">
                                        <div id="strengthBar" class="progress-bar" style="width:0%;transition:width .3s,background .3s;"></div>
                                    </div>
                                    <small id="strengthLabel" class="text-muted"></small>
                                </div>
                                <small class="text-muted">Tối thiểu 6 ký tự.</small>
                            </div>
                        </div>

                        <!-- Xác nhận mật khẩu mới -->
                        <div class="row mb-4">
                            <label class="col-md-3 col-form-label fw-bold text-md-end">
                                Xác nhận mật khẩu <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="password" id="confirm_password" name="confirm_password"
                                           class="form-control" required
                                           placeholder="Nhập lại mật khẩu mới"
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="confirm_password" tabindex="-1">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <small id="matchMsg" class="d-none"></small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-7 offset-md-3">
                                <button type="submit" class="btn btn-danger px-4 fw-bold" id="btnSubmit">
                                    <i class="fa-solid fa-key me-1"></i> Đổi mật khẩu
                                </button>
                                <a href="<?= URL_ROOT ?>/nguoi-dung/profile" class="btn btn-outline-secondary ms-2">
                                    Huỷ
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ── Show / Hide password ──────────────────────────────────────────
document.querySelectorAll('.toggle-pw').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = document.getElementById(this.dataset.target);
        var icon  = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// ── Strength meter ────────────────────────────────────────────────
var newPw     = document.getElementById('new_password');
var bar       = document.getElementById('strengthBar');
var label     = document.getElementById('strengthLabel');
var wrap      = document.getElementById('strengthWrap');

newPw.addEventListener('input', function() {
    var v = this.value;
    wrap.style.display = v.length ? '' : 'none';

    var score = 0;
    if (v.length >= 6)  score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    var levels = [
        { pct: 20,  cls: 'bg-danger',  txt: 'Rất yếu' },
        { pct: 40,  cls: 'bg-warning', txt: 'Yếu' },
        { pct: 60,  cls: 'bg-info',    txt: 'Trung bình' },
        { pct: 80,  cls: 'bg-primary', txt: 'Mạnh' },
        { pct: 100, cls: 'bg-success', txt: 'Rất mạnh' },
    ];
    var lv = levels[Math.min(score, 4)];
    bar.style.width = lv.pct + '%';
    bar.className   = 'progress-bar ' + lv.cls;
    label.textContent = lv.txt;
    label.className   = 'small ' + lv.cls.replace('bg-','text-');

    checkMatch();
});

// ── Kiểm tra khớp xác nhận ───────────────────────────────────────
var confirmPw = document.getElementById('confirm_password');
var matchMsg  = document.getElementById('matchMsg');

function checkMatch() {
    if (!confirmPw.value) { matchMsg.className = 'd-none'; return; }
    if (confirmPw.value === newPw.value) {
        matchMsg.textContent = '✓ Mật khẩu khớp';
        matchMsg.className   = 'small text-success';
    } else {
        matchMsg.textContent = '✗ Mật khẩu không khớp';
        matchMsg.className   = 'small text-danger';
    }
}
confirmPw.addEventListener('input', checkMatch);

// ── Chặn submit nếu không khớp ───────────────────────────────────
document.getElementById('formDoiMK').addEventListener('submit', function(e) {
    if (newPw.value !== confirmPw.value) {
        e.preventDefault();
        confirmPw.focus();
        matchMsg.textContent = '✗ Mật khẩu xác nhận không khớp!';
        matchMsg.className   = 'small text-danger';
    }
    if (newPw.value.length < 6) {
        e.preventDefault();
        newPw.focus();
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
