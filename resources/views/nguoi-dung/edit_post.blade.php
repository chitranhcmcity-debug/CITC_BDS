@include('layouts.header')

<style>
    .section-title { font-size: 1rem; color: #0d6efd; border-bottom: 1px dashed #ccc; padding-bottom: 8px; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; }
    .bg-light-gray { background-color: #f4f5f6; padding: 20px; border-radius: 4px; border: 1px solid #e9ecef; }
</style>

<div class="container py-4">
    <div class="row">
        @include('nguoi-dung.sidebar')

        <div class="col-lg-9 col-md-8">
            <h5 class="fw-bold mb-3 border-bottom pb-2">CHỈNH SỬA TIN ĐĂNG</h5>
            
            <?php if (Session::get('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Lỗi!</strong> <?= Session::get('error'); Session::delete('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php $p = $data['project']; ?>
            <?php if(!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $error): ?><li><?= htmlspecialchars((string)$error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

            <form action="<?= URL_ROOT ?>/nguoi-dung/editPost/<?= $p->id ?>" method="POST" enctype="multipart/form-data">
                <?= Csrf::field() ?>
                <input type="hidden" name="category" value="<?= (int)$p->ma_danh_muc ?>">
                <input type="hidden" name="property_type" value="<?= htmlspecialchars((string)$p->loai_bat_dong_san) ?>">
                <input type="hidden" name="transaction_type" value="<?= htmlspecialchars((string)($p->loai_giao_dich ?? 'ban')) ?>">
                <input type="hidden" name="contact_name" value="<?= htmlspecialchars((string)($p->nguoi_lien_he ?: Session::get('user_name'))) ?>">
                <input type="hidden" name="contact_phone" value="<?= htmlspecialchars((string)($p->so_dien_thoai_lien_he ?: '0900000000')) ?>">
                
                <div class="section-title">THÔNG TIN CƠ BẢN</div>
                <div class="bg-light-gray">
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Tiêu đề:</label>
                        <div class="col-md-7">
                            <input type="text" name="title" required class="form-control form-control-sm rounded-1" value="<?= htmlspecialchars($p->tieu_de) ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Giá:</label>
                        <div class="col-md-7">
                            <input type="text" name="gia" class="form-control form-control-sm rounded-1" value="<?= htmlspecialchars($p->gia) ?>">
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Diện tích(m²):</label>
                        <div class="col-md-4">
                            <input type="text" name="dien_tich" class="form-control form-control-sm rounded-1" value="<?= htmlspecialchars($p->dien_tich) ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Địa chỉ hiện tại:</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm rounded-1 bg-light text-muted" value="<?= htmlspecialchars($p->vi_tri) ?>" readonly>
                            <input type="hidden" name="vi_tri_old" value="<?= htmlspecialchars($p->vi_tri) ?>">
                            <small class="text-primary mt-1 d-block"><i class="fa-solid fa-circle-info"></i> Chỉ điền phần bên dưới nếu bạn muốn thay đổi địa chỉ.</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center mt-4 border-top pt-3">
                        <label class="col-md-3 col-form-label text-md-end">Tỉnh/Tp mới:</label>
                        <div class="col-md-5">
                            <select id="province" name="province" class="form-select form-select-sm rounded-1">
                                <option value="<?= htmlspecialchars((string)($p->tinh_thanh ?: $p->vi_tri)) ?>">-- Giữ nguyên --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Quận/Huyện mới:</label>
                        <div class="col-md-5">
                            <select id="district" name="district" required class="form-select form-select-sm rounded-1">
                                <option value="<?= htmlspecialchars((string)($p->quan_huyen ?: 'Đang cập nhật')) ?>">-- Giữ nguyên quận/huyện --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Xã/Phường mới:</label>
                        <div class="col-md-5">
                            <select id="ward" name="ward" required class="form-select form-select-sm rounded-1">
                                <option value="<?= htmlspecialchars((string)($p->phuong_xa ?: 'Đang cập nhật')) ?>">-- Giữ nguyên phường/xã --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Số nhà/Đường mới:</label>
                        <div class="col-md-7">
                            <input type="text" name="address" required value="<?= htmlspecialchars((string)($p->dia_chi ?: $p->vi_tri)) ?>" class="form-control form-control-sm rounded-1" placeholder="Nhập số nhà, tên đường...">
                        </div>
                    </div>
                </div>

                <div class="section-title">NỘI DUNG CHI TIẾT</div>
                <div class="bg-light-gray">
                    <textarea name="mo_ta" class="form-control rounded-1" rows="8" minlength="100" required><?= htmlspecialchars($p->mo_ta) ?></textarea>
                </div>

                <div class="section-title">HÌNH ẢNH MỚI (Tùy chọn)</div>
                <div class="bg-light-gray">
                    <?php $galleryImages = $data['images'] ?? []; ?>
                    <?php if (!empty($p->anh_thu_nho)): ?>
                        <div class="mb-3">
                            <label class="d-block mb-2 text-muted fw-bold">Ảnh hiện tại:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <img src="<?= URL_ROOT ?>/public/uploads/<?= $p->anh_thu_nho ?>" height="100" class="rounded border border-primary object-fit-cover" title="Ảnh đại diện">
                                <?php foreach($galleryImages as $img): ?>
                                    <?php if ($img->duong_dan_anh !== $p->anh_thu_nho): ?>
                                        <img src="<?= URL_ROOT ?>/public/uploads/<?= $img->duong_dan_anh ?>" height="100" class="rounded border object-fit-cover">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <p class="text-danger small fw-semibold mb-2"><i class="fa-solid fa-circle-info"></i> Lưu ý: Nếu tải lên ảnh mới, toàn bộ ảnh hiện tại sẽ bị xóa.</p>
                    
                    <input type="file" id="imageUploadInput" name="images[]" multiple accept="image/*" class="form-control form-control-sm shadow-sm">
                    
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted"><i class="fa-regular fa-image"></i> Tối đa 10 hình ảnh.</small>
                    </div>
                    
                    <!-- Thêm thư viện SweetAlert2 -->
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                        document.getElementById('imageUploadInput').addEventListener('change', function() {
                            if (this.files.length > 10) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Quá số lượng ảnh!',
                                    text: 'Bạn chỉ được tải lên tối đa 10 hình ảnh cùng lúc. Vui lòng chọn lại.',
                                    confirmButtonColor: '#dc3545',
                                    confirmButtonText: 'Đã hiểu'
                                });
                                this.value = ''; // Reset the input
                            }
                        });
                    </script>
                </div>

                <div class="text-center mt-4 border-top pt-4">
                    <button type="submit" class="btn btn-danger px-5 py-2 fw-bold"><i class="fa-solid fa-save me-2"></i> Lưu Thay Đổi</button>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/dashboard" class="btn btn-secondary px-4 py-2 ms-2">Hủy Bỏ</a>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.footer')

<!-- Script gọi API Tỉnh thành Việt Nam -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    let locationData = [];

    // Gọi API lấy danh sách Tỉnh/Thành phố
    $.ajax({
        url: 'https://provinces.open-api.vn/api/?depth=3',
        method: 'GET',
        success: function(data) {
            locationData = data;
            data.forEach(province => {
                $('#province').append(`<option value="${province.name}" data-id="${province.code}">${province.name}</option>`);
            });
        }
    });

    // Khi chọn Tỉnh/Thành phố
    $('#province').change(function() {
        const provinceCode = $(this).find(':selected').data('id');
        $('#district').html('<option value="">-- Quận/huyện --</option>').prop('disabled', true);
        $('#ward').html('<option value="">-- Xã/Phường --</option>').prop('disabled', true);

        if (provinceCode) {
            const province = locationData.find(p => p.code == provinceCode);
            if (province && province.districts) {
                province.districts.forEach(district => {
                    $('#district').append(`<option value="${district.name}" data-id="${district.code}">${district.name}</option>`);
                });
                $('#district').prop('disabled', false);
            }
        }
    });

    // Khi chọn Quận/Huyện
    $('#district').change(function() {
        const provinceCode = $('#province').find(':selected').data('id');
        const districtCode = $(this).find(':selected').data('id');
        $('#ward').html('<option value="">-- Xã/Phường --</option>').prop('disabled', true);

        if (provinceCode && districtCode) {
            const province = locationData.find(p => p.code == provinceCode);
            const district = province.districts.find(d => d.code == districtCode);
            
            if (district && district.wards) {
                district.wards.forEach(ward => {
                    $('#ward').append(`<option value="${ward.name}" data-id="${ward.code}">${ward.name}</option>`);
                });
                $('#ward').prop('disabled', false);
            }
        }
    });
});
</script>
