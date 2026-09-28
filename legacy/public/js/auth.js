/**
 * auth.js – JavaScript cho module Xác thực
 * TimNhaDat.site | 2026
 */
(function () {
    'use strict';

    /* ═══════════════════════════════════════
       PASSWORD STRENGTH METER
    ═══════════════════════════════════════ */
    const PasswordStrength = {
        labels: ['Rất yếu', 'Yếu', 'Trung bình', 'Khá mạnh', 'Mạnh', 'Rất mạnh'],
        colors: ['#ef4444', '#ef4444', '#f59e0b', '#84cc16', '#10b981', '#10b981'],

        /** Kiểm tra từng tiêu chí */
        analyze(pass) {
            return {
                length:   pass.length >= 8,
                upper:    /[A-Z]/.test(pass),
                lower:    /[a-z]/.test(pass),
                digit:    /[0-9]/.test(pass),
                special:  /[^A-Za-z0-9]/.test(pass),
            };
        },

        score(pass) {
            if (!pass) return 0;
            const r = this.analyze(pass);
            return Object.values(r).filter(Boolean).length;
        },

        init(inputId, barId, labelId, checklistId) {
            const input     = document.getElementById(inputId);
            const bar       = document.getElementById(barId);
            const label     = document.getElementById(labelId);
            const checklist = document.getElementById(checklistId);
            if (!input || !bar) return;

            const fill = bar.querySelector('.pw-strength-fill') || bar;

            input.addEventListener('input', () => {
                const pass  = input.value;
                const sc    = this.score(pass);
                const pct   = pass.length ? (sc / 5) * 100 : 0;
                const r     = this.analyze(pass);

                // Update bar
                fill.style.width      = pct + '%';
                fill.style.background = this.colors[sc] || this.colors[0];
                fill.className        = `pw-strength-fill pw-s${sc}`;

                // Update label
                if (label) {
                    label.textContent = pass.length ? this.labels[sc] : '';
                    label.style.color = this.colors[sc] || '';
                }

                // Update checklist
                if (checklist) {
                    const items = {
                        'check-length':  r.length,
                        'check-upper':   r.upper,
                        'check-lower':   r.lower,
                        'check-digit':   r.digit,
                        'check-special': r.special,
                    };
                    for (const [id, ok] of Object.entries(items)) {
                        const li = checklist.querySelector(`[data-check="${id}"]`);
                        if (li) {
                            li.classList.toggle('ok', ok);
                            const icon = li.querySelector('i');
                            if (icon) icon.className = ok ? 'fas fa-check-circle' : 'fas fa-circle';
                        }
                    }
                }
            });
        },
    };

    /* ═══════════════════════════════════════
       TOGGLE PASSWORD VISIBILITY
    ═══════════════════════════════════════ */
    function initTogglePass() {
        document.querySelectorAll('.auth-toggle-pass').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.getElementById(btn.dataset.target);
                if (!target) return;
                const isPass = target.type === 'password';
                target.type = isPass ? 'text' : 'password';
                const icon  = btn.querySelector('i');
                if (icon) icon.className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        });
    }

    /* ═══════════════════════════════════════
       OTP INPUT – Auto-focus next digit
    ═══════════════════════════════════════ */
    function initOTPInput() {
        const digits = document.querySelectorAll('.otp-digit');
        if (!digits.length) return;

        digits.forEach((input, idx) => {
            input.addEventListener('input', e => {
                const val = e.target.value.replace(/\D/g, '');
                e.target.value = val.slice(-1);

                if (val && idx < digits.length - 1) {
                    digits[idx + 1].focus();
                }

                e.target.classList.toggle('filled', !!e.target.value);
                updateOTPHidden();
            });

            input.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    digits[idx - 1].focus();
                    digits[idx - 1].value = '';
                    digits[idx - 1].classList.remove('filled');
                }
                if (e.key === 'ArrowLeft' && idx > 0) digits[idx - 1].focus();
                if (e.key === 'ArrowRight' && idx < digits.length - 1) digits[idx + 1].focus();
            });

            // Handle paste
            input.addEventListener('paste', e => {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                [...text].slice(0, digits.length).forEach((ch, i) => {
                    if (digits[i]) {
                        digits[i].value = ch;
                        digits[i].classList.add('filled');
                    }
                });
                if (digits[Math.min(text.length, digits.length - 1)]) {
                    digits[Math.min(text.length, digits.length - 1)].focus();
                }
                updateOTPHidden();
            });
        });

        function updateOTPHidden() {
            const hidden = document.getElementById('otp_full');
            if (hidden) {
                hidden.value = [...digits].map(d => d.value).join('');
            }
        }
    }

    /* ═══════════════════════════════════════
       OTP RESEND COUNTDOWN
    ═══════════════════════════════════════ */
    function initOTPCountdown(seconds = 60) {
        const btn       = document.getElementById('resend-otp-btn');
        const countdown = document.getElementById('resend-countdown');
        if (!btn) return;

        let remaining = seconds;

        function tick() {
            if (remaining <= 0) {
                btn.disabled  = false;
                if (countdown) countdown.textContent = '';
                return;
            }
            btn.disabled = true;
            if (countdown) countdown.textContent = `(${remaining}s)`;
            remaining--;
            setTimeout(tick, 1000);
        }

        tick();

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            const purpose = document.getElementById('otp_purpose')?.value || 'reset_password';
            try {
                const res = await fetch(SITE_ROOT + '/nguoi-dung/resend-otp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `purpose=${encodeURIComponent(purpose)}&_csrf_token=${encodeURIComponent(CSRF_TOKEN)}`,
                });
                const data = await res.json();
                showToast(data.message || 'OTP đã được gửi lại.', data.success ? 'success' : 'error');
                if (data.success) {
                    remaining = data.cooldown || 60;
                    tick();
                } else {
                    btn.disabled = false;
                }
            } catch {
                btn.disabled = false;
                showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
            }
        });
    }

    /* ═══════════════════════════════════════
       SUBMIT LOADING STATE
    ═══════════════════════════════════════ */
    function initFormLoading() {
        document.querySelectorAll('form.auth-form').forEach(form => {
            form.addEventListener('submit', () => {
                const btn = form.querySelector('.auth-btn[type="submit"]');
                if (btn) {
                    btn.classList.add('loading');
                    btn.disabled = true;
                }
            });
        });
    }

    /* ═══════════════════════════════════════
       INLINE FORM VALIDATION (live)
    ═══════════════════════════════════════ */
    function initLiveValidation() {
        // Email format
        const emailInputs = document.querySelectorAll('input[type="email"][data-validate]');
        emailInputs.forEach(input => {
            input.addEventListener('blur', () => {
                const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
                input.classList.toggle('is-valid', valid && input.value.length > 0);
                input.classList.toggle('is-invalid', !valid && input.value.length > 0);
            });
        });

        // Password confirm match
        const passConfirm = document.getElementById('mat_khau_xac_nhan');
        const passMain    = document.getElementById('mat_khau');
        if (passConfirm && passMain) {
            passConfirm.addEventListener('input', () => {
                const match = passMain.value === passConfirm.value;
                passConfirm.classList.toggle('is-valid', match && passConfirm.value.length > 0);
                passConfirm.classList.toggle('is-invalid', !match && passConfirm.value.length > 0);
            });
        }

        // Phone format (VN)
        const phoneInputs = document.querySelectorAll('input[data-validate="phone"]');
        phoneInputs.forEach(input => {
            input.addEventListener('blur', () => {
                const valid = /^(\+?84|0)[3-9][0-9]{8}$/.test(input.value.replace(/[\s\-\.]/g, ''));
                input.classList.toggle('is-valid', valid && input.value.length > 0);
                input.classList.toggle('is-invalid', !valid && input.value.length > 0);
            });
        });
    }

    /* ═══════════════════════════════════════
       TOAST NOTIFICATION
    ═══════════════════════════════════════ */
    function showToast(message, type = 'info') {
        const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
        const colors = {
            success: '#dcfce7',
            error:   '#fee2e2',
            warning: '#fef3c7',
            info:    '#dbeafe',
        };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position:fixed;bottom:24px;right:24px;z-index:9999;
            background:${colors[type] || '#fff'};border-radius:10px;
            padding:14px 18px;font-size:.88rem;font-weight:500;
            box-shadow:0 8px 32px rgba(0,0,0,.12);
            display:flex;align-items:center;gap:8px;
            animation:slideIn .3s ease;max-width:340px;
        `;
        toast.innerHTML = `<span>${icons[type] || ''}</span><span>${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'slideOut .3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Toast keyframes (add once)
    if (!document.getElementById('auth-toast-styles')) {
        const style = document.createElement('style');
        style.id = 'auth-toast-styles';
        style.textContent = `
            @keyframes slideIn  { from { transform:translateX(120%); opacity:0; } to { transform:translateX(0); opacity:1; } }
            @keyframes slideOut { from { transform:translateX(0); opacity:1; } to { transform:translateX(120%); opacity:0; } }
        `;
        document.head.appendChild(style);
    }

    /* ═══════════════════════════════════════
       RESEND VERIFICATION EMAIL
    ═══════════════════════════════════════ */
    function initResendVerify() {
        const btn = document.getElementById('resend-verify-btn');
        if (!btn) return;
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.textContent = 'Đang gửi...';
            const identifier = document.getElementById('resend-identifier')?.value || '';
            try {
                const res = await fetch(SITE_ROOT + '/nguoi-dung/resend-verify', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `identifier=${encodeURIComponent(identifier)}&_csrf_token=${encodeURIComponent(CSRF_TOKEN)}`,
                });
                const data = await res.json();
                showToast(data.message, data.success ? 'success' : 'error');
                btn.textContent = 'Gửi lại';
                setTimeout(() => { btn.disabled = false; }, 60000);
            } catch {
                btn.disabled = false;
                btn.textContent = 'Gửi lại';
                showToast('Lỗi kết nối.', 'error');
            }
        });
    }

    function initCaptchaRefresh() {
        const button = document.getElementById('captcha-refresh');
        const display = document.getElementById('captcha-display');
        if (!button || !display) return;
        button.addEventListener('click', async () => {
            button.disabled = true;
            try {
                const response = await fetch(SITE_ROOT + '/nguoi-dung/refresh-captcha', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `_csrf_token=${encodeURIComponent(CSRF_TOKEN)}`,
                });
                const data = await response.json();
                if (data.success) {
                    display.textContent = data.captcha;
                    const input = document.querySelector('input[name="captcha"]');
                    if (input) {
                        input.value = '';
                        input.focus();
                    }
                } else {
                    showToast(data.message || 'Không thể tạo mã mới.', 'error');
                }
            } catch {
                showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
            } finally {
                button.disabled = false;
            }
        });
    }

    /* ═══════════════════════════════════════
       INIT
    ═══════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', () => {
        initTogglePass();
        initOTPInput();
        initFormLoading();
        initLiveValidation();
        initResendVerify();
        initCaptchaRefresh();

        // Password strength (register page)
        PasswordStrength.init('mat_khau', 'pw-strength-bar', 'pw-strength-label', 'pw-checklist');

        // OTP countdown (verify-otp page)
        const otpPage = document.getElementById('otp-section');
        if (otpPage) initOTPCountdown(60);
    });

    // Expose globally
    window.AUTH = { showToast, initOTPCountdown, PasswordStrength };

})();
