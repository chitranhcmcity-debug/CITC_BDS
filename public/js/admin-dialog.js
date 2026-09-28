(function () {
  'use strict';

  function ensureModal() {
    let el = document.getElementById('adminUiDialog');
    if (el) return el;
    document.body.insertAdjacentHTML('beforeend', `
      <div class="modal fade" id="adminUiDialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered admin-dialog-width">
          <div class="modal-content border-0 shadow-lg admin-dialog-card">
            <div class="modal-body p-4 p-md-5 text-center">
              <div class="admin-dialog-icon mx-auto mb-3"><i class="fa-solid"></i></div>
              <h4 class="fw-bold text-dark mb-2 admin-dialog-title"></h4>
              <p class="text-muted mb-4 admin-dialog-message"></p>
              <div class="admin-dialog-input-wrap d-none mb-4 text-start">
                <label class="form-label small fw-semibold">Nội dung</label>
                <textarea class="form-control admin-dialog-input" rows="3"></textarea>
                <div class="invalid-feedback">Vui lòng nhập nội dung.</div>
              </div>
              <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-light border fw-semibold px-4 admin-dialog-cancel" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="button" class="btn fw-semibold px-4 admin-dialog-ok"></button>
              </div>
            </div>
          </div>
        </div>
      </div>`);
    return document.getElementById('adminUiDialog');
  }

  function show(options) {
    options = Object.assign({ title: 'Xác nhận thao tác', message: '', type: 'danger', confirmText: 'Xác nhận', cancelText: 'Hủy bỏ', input: false, inputValue: '' }, options || {});
    const el = ensureModal();
    const modal = bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static' });
    const iconBox = el.querySelector('.admin-dialog-icon');
    const icon = iconBox.querySelector('i');
    const ok = el.querySelector('.admin-dialog-ok');
    const cancel = el.querySelector('.admin-dialog-cancel');
    const inputWrap = el.querySelector('.admin-dialog-input-wrap');
    const input = el.querySelector('.admin-dialog-input');
    const styles = {
      danger: ['fa-trash-can', 'danger', 'btn-danger'],
      warning: ['fa-triangle-exclamation', 'warning', 'btn-warning'],
      success: ['fa-circle-check', 'success', 'btn-success'],
      info: ['fa-circle-info', 'info', 'btn-primary']
    };
    const style = styles[options.type] || styles.info;
    icon.className = 'fa-solid ' + style[0];
    iconBox.className = 'admin-dialog-icon mx-auto mb-3 is-' + style[1];
    el.querySelector('.admin-dialog-title').textContent = options.title;
    el.querySelector('.admin-dialog-message').textContent = options.message;
    ok.className = 'btn fw-semibold px-4 admin-dialog-ok ' + style[2];
    ok.textContent = options.confirmText;
    cancel.textContent = options.cancelText;
    cancel.classList.toggle('d-none', options.alertOnly === true);
    inputWrap.classList.toggle('d-none', !options.input);
    input.value = options.inputValue || '';
    input.classList.remove('is-invalid');

    return new Promise(resolve => {
      let settled = false;
      const finish = value => { if (settled) return; settled = true; resolve(value); };
      ok.onclick = function () {
        if (options.input && !input.value.trim()) { input.classList.add('is-invalid'); input.focus(); return; }
        finish(options.input ? input.value.trim() : true);
        modal.hide();
      };
      el.addEventListener('hidden.bs.modal', function hidden() {
        el.removeEventListener('hidden.bs.modal', hidden);
        finish(options.input ? null : false);
      });
      modal.show();
      if (options.input) setTimeout(() => { input.focus(); input.select(); }, 250);
    });
  }

  window.AdminDialog = {
    confirm: (message, options) => show(Object.assign({ message, title: 'Bạn chắc chắn chứ?', type: 'danger' }, options || {})),
    alert: (message, options) => show(Object.assign({ message, title: 'Thông báo', type: 'info', alertOnly: true, confirmText: 'Đã hiểu' }, options || {})),
    prompt: (message, value, options) => show(Object.assign({ message, title: 'Nhập thông tin', type: 'warning', input: true, inputValue: value || '', confirmText: 'Tiếp tục' }, options || {}))
  };

  window.alert = message => { window.AdminDialog.alert(String(message)); };

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
      const source = form.getAttribute('onsubmit') || '';
      const match = source.match(/confirm\(\s*(['"])(.*?)\1\s*\)/);
      if (!match) return;
      form.removeAttribute('onsubmit');
      form.addEventListener('submit', async function (event) {
        if (form.dataset.adminConfirmed === '1') return;
        event.preventDefault();
        const accepted = await window.AdminDialog.confirm(match[2]);
        if (accepted) { form.dataset.adminConfirmed = '1'; form.requestSubmit(); }
      });
    });

    document.querySelectorAll('form[onsubmit*="prompt("]').forEach(form => {
      const source = form.getAttribute('onsubmit') || '';
      const match = source.match(/prompt\(\s*(['"])(.*?)\1\s*,\s*(['"])(.*?)\3\s*\)/);
      if (!match) return;
      form.removeAttribute('onsubmit');
      form.addEventListener('submit', async function (event) {
        if (form.dataset.adminConfirmed === '1') return;
        event.preventDefault();
        const value = await window.AdminDialog.prompt(match[2], match[4]);
        if (value !== null) {
          const reason = form.querySelector('[name="reason"]');
          if (reason) reason.value = value;
          form.dataset.adminConfirmed = '1';
          form.requestSubmit();
        }
      });
    });
  });
})();
