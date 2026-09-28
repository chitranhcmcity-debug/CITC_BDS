<?php
/**
 * Component: Avatar - Hiển thị và xử lý cắt ảnh đại diện bằng CropperJS.
 * @var object $user Thông tin người dùng
 */

$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><linearGradient id="avatarGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#5645d4" /><stop offset="100%" stop-color="#a29bfe" /></linearGradient></defs><rect width="100%" height="100%" fill="url(#avatarGrad)" /><circle cx="50" cy="40" r="18" fill="#ffffff" opacity="0.95" /><path d="M50 62c-15 0-28 7-32 17h64c-4-10-17-17-32-17z" fill="#ffffff" opacity="0.95" /></svg>';
$defaultAvatar = 'data:image/svg+xml;base64,' . base64_encode($svg);

$avatarUrl = !empty($user->anh_dai_dien) ? URL_ROOT . '/public/uploads/' . $user->anh_dai_dien : $defaultAvatar;
if (str_starts_with($user->anh_dai_dien ?? '', 'http')) {
    $avatarUrl = $user->anh_dai_dien;
}
?>
<!-- Include CropperJS CSS & JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<!-- Include SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<div class="card profile-card text-center p-4 mb-4">
    <div class="card-body p-2">
        <h5 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-circle-user text-primary me-2"></i>Ảnh đại diện</h5>
        
        <!-- Avatar Wrapper -->
        <div class="avatar-container mb-4">
            <div class="avatar-image-wrapper">
                <img src="<?= $avatarUrl ?><?= !str_starts_with($avatarUrl, 'data:') ? '?v='.time() : '' ?>" 
                     id="avatar-preview" 
                     class="avatar-image" 
                     alt="Avatar"
                     onerror="this.src='<?= $defaultAvatar ?>'; this.onerror=null;">
                
                <!-- Hover Upload Overlay (Desktop) -->
                <label for="avatar-file-input" class="avatar-upload-overlay">
                    <i class="fa-solid fa-camera"></i>
                    <span>Tải ảnh lên</span>
                </label>
            </div>
            
            <!-- Floating Upload Badge -->
            <label for="avatar-file-input" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center cursor-pointer shadow-sm hover-scale" style="width: 36px; height: 36px; border: 3px solid #fff; z-index: 3;">
                <i class="fa-solid fa-camera fs-6"></i>
            </label>
        </div>

        <p class="small text-muted mb-2">Chấp nhận JPG, PNG hoặc WEBP (tối đa 2MB).</p>
        <p class="small text-secondary mb-0 bg-light py-1.5 px-2 rounded-3 border border-light-subtle" style="font-size: 0.78rem;">
            <i class="fa-solid fa-wand-magic-sparkles me-1 text-primary"></i>Hệ thống tự động tối ưu sang WebP
        </p>
        
        <!-- Form Upload ẩn -->
        <form action="<?= URL_ROOT ?>/nguoi-dung/uploadAvatar" method="POST" enctype="multipart/form-data" id="avatar-form">
            <?= Csrf::field() ?>
            <input type="file" id="avatar-file-input" name="avatar" class="d-none" accept="image/png, image/jpeg, image/webp">
            <!-- Dữ liệu ảnh sau khi cắt bằng CropperJS dưới dạng Base64 -->
            <input type="hidden" id="avatar-cropped-data" name="avatar_cropped" value="">
        </form>
    </div>
</div>

<!-- Modal Cắt Ảnh CropperJS -->
<div class="modal fade" id="cropperModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="cropperModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="cropperModalLabel"><i class="fa-solid fa-crop-simple me-2"></i>Cắt chỉnh ảnh đại diện</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mx-auto" style="max-width: 100%; height: 320px; background-color: #f7f7f7; border-radius: 8px; overflow: hidden;">
                    <img id="cropper-image" src="" style="display: block; max-width: 100%;" alt="Crop Area">
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-xs" id="btn-save-crop">Cắt & Lưu</button>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.hover-scale { transition: transform 0.2s ease; }
.hover-scale:hover { transform: scale(1.12); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('avatar-file-input');
    const modalEl = document.getElementById('cropperModal');
    if (modalEl) {
        document.body.appendChild(modalEl);
    }
    const cropperModal = new bootstrap.Modal(modalEl);
    const cropperImage = document.getElementById('cropper-image');
    let cropper = null;

    fileInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            
            // Check file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    title: 'Dung lượng quá lớn',
                    text: 'Dung lượng ảnh tối đa cho phép là 2MB.',
                    icon: 'warning'
                });
                fileInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(evt) {
                cropperImage.src = evt.target.result;
                
                // Mở modal và setup Cropper
                cropperModal.show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Sự kiện khi Modal hiện lên hoàn toàn để tạo Cropper
    document.getElementById('cropperModal').addEventListener('shown.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
        }
        cropper = new Cropper(cropperImage, {
            aspectRatio: 1, // Tỉ lệ 1:1 làm ảnh tròn đại diện
            viewMode: 1,
            autoCropArea: 0.8,
            responsive: true
        });
    });

    // Khi đóng modal mà hủy crop
    document.getElementById('cropperModal').addEventListener('hidden.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        fileInput.value = ''; // Reset file input
    });

    // Thực hiện cắt và lưu ảnh
    document.getElementById('btn-save-crop').addEventListener('click', function() {
        if (!cropper) return;

        // Trích xuất ảnh cropped dưới dạng Base64 WebP chất lượng 85
        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300
        });

        const croppedDataUrl = canvas.toDataURL('image/webp', 0.85);

        // Gán vào hidden field và submit form
        document.getElementById('avatar-cropped-data').value = croppedDataUrl;
        
        cropperModal.hide();
        
        // Hiển thị loading overlay trong lúc xử lý
        Swal.fire({
            title: 'Đang tải lên ảnh đại diện...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        document.getElementById('avatar-form').submit();
    });
});
</script>
