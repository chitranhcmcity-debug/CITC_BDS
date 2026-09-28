<?php require_once '../app/views/layouts/header.php'; ?>

<style>
    .form-label { font-weight: 500; font-size: 0.9rem; }
    .section-title { font-size: 1rem; color: #0d6efd; border-bottom: 1px dashed #ccc; padding-bottom: 8px; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; }
    .bg-light-gray { background-color: #f4f5f6; padding: 20px; border-radius: 4px; border: 1px solid #e9ecef; }
</style>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../app/views/nguoi-dung/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            <h5 class="fw-bold mb-3 border-bottom pb-2">ĐĂNG TIN RAO BÁN, CHO THUÊ NHÀ ĐẤT</h5>
            <?php if(!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $error): ?><li><?= htmlspecialchars((string)$error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            
            <?php if (Session::get('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><strong>Thành công!</strong> <?= Session::get('success'); Session::delete('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (Session::get('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Lỗi!</strong> <?= Session::get('error'); Session::delete('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form id="postForm" action="<?= URL_ROOT ?>/nguoi-dung/post" method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
                <?= Csrf::field() ?>

            <div class="alert bg-light border border-primary border-1" style="border-style: dashed !important;">
                <div class="text-center">
                    <p class="text-danger mb-2 font-italic fw-semibold">Tin không có ảnh sẽ KHÔNG được duyệt! Vui lòng Upload ảnh để thu hút khách hàng hơn.</p>
                    <input type="file" id="imageUploadInput" name="images[]" multiple accept="image/*" class="d-none">
                    <button type="button" class="btn btn-danger btn-sm px-4 py-2 fw-bold rounded-1" onclick="document.getElementById('imageUploadInput').click()">Click vào đây để upload ảnh</button>
                </div>
                <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mt-3 justify-content-center"></div>
            </div>
                
                <!-- Basic Info -->
                <div class="section-title">THÔNG TIN CƠ BẢN</div>
                <div class="bg-light-gray">
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Tiêu đề:</label>
                        <div class="col-md-7">
                            <input type="text" name="title" required maxlength="150" class="form-control form-control-sm rounded-1" placeholder="Ghi rõ đặc điểm BĐS, vị trí BĐS giúp khách hàng dễ tìm kiếm hơn">
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Loại hình:</label>
                        <div class="col-md-5">
                            <select name="type" required class="form-select form-select-sm rounded-1">
                                <option value="">-- Loại Hình --</option>
                                <option value="Nhà đất bán">Nhà đất bán</option>
                                <option value="Nhà đất cho thuê">Nhà đất cho thuê</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Danh mục:</label>
                        <div class="col-md-5">
                            <select name="category" required class="form-select form-select-sm rounded-1">
                                <option value="">-- Danh mục --</option>
                                <?php foreach (($data['categories'] ?? []) as $category): ?>
                                    <option value="<?= (int)$category->id ?>"><?= htmlspecialchars($category->ten) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Tỉnh/Tp:</label>
                        <div class="col-md-5">
                            <select id="province" name="province" required class="form-select form-select-sm rounded-1">
                                <option value="">-- Tỉnh/Tp --</option>
                            </select>
                            <input type="hidden" name="tinh_thanh" id="tinh_thanh_hidden">
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Quận/Huyện:</label>
                        <div class="col-md-5">
                            <select id="district" name="district" required class="form-select form-select-sm rounded-1" disabled>
                                <option value="">-- Quận/huyện --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Xã/Phường:</label>
                        <div class="col-md-5">
                            <select id="ward" name="ward" required class="form-select form-select-sm rounded-1" disabled>
                                <option value="">-- Xã/Phường --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Địa chỉ:</label>
                        <div class="col-md-7">
                            <input type="text" name="address" required class="form-control form-control-sm rounded-1">
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Giá:</label>
                        <div class="col-md-7">
                            <div class="row g-2">
                                <div class="col-md-7">
                                    <input type="text" name="gia" required class="form-control form-control-sm rounded-1" placeholder="Nhập giá">
                                </div>
                                <div class="col-md-5">
                                    <select name="don_vi_gia" class="form-select form-select-sm rounded-1">
                                        <option value="Thỏa thuận">Thỏa thuận</option>
                                        <option value="Triệu">Triệu</option>
                                        <option value="Tỷ">Tỷ</option>
                                        <option value="Triệu/m2">Triệu/m2</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Diện tích(m²):</label>
                        <div class="col-md-4">
                            <input type="number" name="dien_tich" min="1" step="0.01" required class="form-control form-control-sm rounded-1">
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Hướng nhà:</label>
                        <div class="col-md-4">
                            <select name="huong_nha" class="form-select form-select-sm rounded-1">
                                <option value="">-- Phương Hướng --</option>
                                <option value="Đông">Đông</option>
                                <option value="Tây">Tây</option>
                                <option value="Nam">Nam</option>
                                <option value="Bắc">Bắc</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Số phòng ngủ:</label>
                        <div class="col-md-4">
                            <input type="text" name="so_phong_ngu" class="form-control form-control-sm rounded-1">
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Số toilet:</label>
                        <div class="col-md-4">
                            <input type="text" name="so_phong_wc" class="form-control form-control-sm rounded-1">
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end text-primary">Link video youtube:</label>
                        <div class="col-md-5">
                            <input type="text" name="link_video" class="form-control form-control-sm rounded-1" placeholder="Copy link youtube paste vào đây">
                        </div>
                    </div>
                </div>

                <div class="section-title">THÔNG TIN MỞ RỘNG</div>
                <div class="bg-light-gray">
                    <div class="row g-2 mb-3"><div class="col-md-6"><input name="project_name" class="form-control form-control-sm" placeholder="Tên dự án"></div><div class="col-md-6"><input name="interior" class="form-control form-control-sm" placeholder="Nội thất"></div></div>
                    <div class="row g-2 mb-3"><div class="col-md-3"><input type="number" step=".01" min="0" name="width" class="form-control form-control-sm" placeholder="Chiều rộng"></div><div class="col-md-3"><input type="number" step=".01" min="0" name="length" class="form-control form-control-sm" placeholder="Chiều dài"></div><div class="col-md-3"><input type="number" min="0" name="floors" class="form-control form-control-sm" placeholder="Số tầng"></div><div class="col-md-3"><input type="number" min="1800" max="<?= date('Y')+5 ?>" name="construction_year" class="form-control form-control-sm" placeholder="Năm xây dựng"></div></div>
                    <div class="row g-2"><div class="col-md-4"><select name="phap_ly" class="form-select form-select-sm"><option value="">Pháp lý</option><option value="so_do">Sổ đỏ</option><option value="so_hong">Sổ hồng</option><option value="giay_tay">Giấy tờ khác</option><option value="cho_so">Đang chờ sổ</option></select></div><div class="col-md-4"><input type="url" name="link_360" class="form-control form-control-sm" placeholder="URL ảnh/tour 360"></div><div class="col-md-4"><input type="file" name="video_file" accept="video/mp4" class="form-control form-control-sm" title="Video MP4 tối đa 50 MB"></div></div>
                </div>

                <!-- Detailed Content -->
                <div class="section-title border-0 mb-0 mt-4 d-flex align-items-center">
                    <span class="bg-primary text-white px-3 py-1 me-3 rounded-1" style="font-size: 0.9rem;">Nội dung chi tiết</span>
                    <span class="text-dark text-lowercase" style="font-size: 0.9rem;">tối đa chỉ 3000 ký tự</span>
                </div>
                <div class="bg-light-gray">
                    <textarea name="mo_ta" class="form-control rounded-1" rows="8" minlength="100" maxlength="3000" required></textarea>
                </div>

                <!-- Map -->
                <div class="section-title">BẢN ĐỒ</div>
                <div class="bg-light-gray p-0 overflow-hidden" style="height: 400px; position: relative;">
                    <div class="p-3 position-absolute top-0 w-100 shadow-sm" style="background: rgba(255,255,255,0.9); z-index: 1000;">
                        <small class="font-italic fw-semibold text-dark">Nhấp vào bản đồ hoặc kéo <span class="text-danger"><i class="fa-solid fa-location-dot"></i> icon màu đỏ " Vị Trí BĐS "</span> tới vị trí BĐS của bạn. BĐS đăng đúng vị trí sẽ tạo được sự tin cậy và tin rao sẽ được nhiều người quan tâm hơn.</small>
                    </div>
                    <div id="map" class="w-100 h-100"></div>
                    <input type="hidden" name="latitude" id="latitude" value="10.769539">
                    <input type="hidden" name="longitude" id="longitude" value="106.631911">
                </div>

                <!-- Contact -->
                <div class="section-title">LIÊN HỆ</div>
                <div class="bg-light-gray">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-3 text-md-end">Tên người liên hệ:</div>
                        <div class="col-md-9"><input name="contact_name" class="form-control form-control-sm" required value="<?= htmlspecialchars((string)(Session::get('user_name') ?: '')) ?>"></div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-3 text-md-end">Điện thoại:</div>
                        <div class="col-md-9"><input name="contact_phone" type="tel" class="form-control form-control-sm" required pattern="(?:\+?84|0)[3-9](?:[\s.\-]?[0-9]){8}" title="Nhập số điện thoại Việt Nam hợp lệ, ví dụ 0901234567" placeholder="0901234567"></div>
                    </div>
                    <div class="row mb-1 align-items-center">
                        <div class="col-md-3 text-md-end">Điện thoại 02:</div>
                        <div class="col-md-9 fw-bold">0368180923</div>
                    </div>
                </div>

                <!-- VIP -->
                <div class="section-title">MUA TIN VIP / UP TIN</div>
                <div class="bg-light-gray position-relative">
                    <?php if ($data['discount'] > 0): ?>
                        <span class="position-absolute top-0 end-0 badge bg-danger m-2 px-3 py-2 fs-6">
                            <i class="fa-solid fa-gift"></i> Bạn được giảm <?= $data['discount'] ?>%
                        </span>
                    <?php endif; ?>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Chọn loại tin VIP:</label>
                        <div class="col-md-4">
                            <select name="vip_level" id="vip_level" class="form-select form-select-sm rounded-1">
                                <option value="0">-- Tin thường (Miễn phí) --</option>
                                <?php foreach ([5, 4, 3, 2, 1] as $vipLevel): ?>
                                    <option value="<?= $vipLevel ?>">VIP <?= $vipLevel ?> (<?= number_format((int)($data['vipPrices'][$vipLevel] ?? 0), 0, ',', '.') ?>đ/ngày)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Chọn số ngày:</label>
                        <div class="col-md-4">
                            <select name="days" id="days" class="form-select form-select-sm rounded-1">
                                <option value="0">-- Chọn số ngày --</option>
                                <option value="7">7 ngày</option>
                                <option value="15">15 ngày</option>
                                <option value="30">30 ngày</option>
                                <option value="90">90 ngày</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-md-3 col-form-label text-md-end">Mua gói UP tin (tùy chọn):</label>
                        <div class="col-md-4">
                            <select name="up_package" id="up_package" class="form-select form-select-sm rounded-1">
                                <option value="0">-- Không mua UP tin --</option>
                                <?php foreach (($data['upPackages'] ?? []) as $turns => $packagePrice): ?>
                                    <option value="<?= $packagePrice ?>"><?= number_format($turns) ?> lượt / 30 ngày (<?= number_format($packagePrice) ?>đ)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                        <div class="col-md-3 text-md-end">Tạm tính:</div>
                        <div class="col-md-9 fw-bold"><span id="base_price">0</span> đ</div>
                    </div>
                    <div class="row mb-1 align-items-center">
                        <div class="col-md-3 text-md-end">Giảm giá view (<?= $data['discount'] ?>%):</div>
                        <div class="col-md-9 fw-bold text-success">- <span id="discount_amount">0</span> đ</div>
                    </div>
                    <div class="row mb-1 align-items-center">
                        <div class="col-md-3 text-md-end">Tổng tiền phải thanh toán:</div>
                        <div class="col-md-9 fw-bold text-danger fs-5"><span id="final_price">0</span> đ</div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="bg-light-gray mt-4">
                    <div class="row mb-4 justify-content-center align-items-center">
                        <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Mã xác nhận</label>
                        <div class="col-md-5 d-flex align-items-center">
                            <input type="text" name="captcha" class="form-control rounded-1 me-3" style="width: 120px;" required>
                            <div class="bg-black text-white px-4 py-1 fw-bold tracking-wide rounded-1" style="letter-spacing: 2px;"><?= $data['captcha'] ?></div>
                        </div>
                    </div>
                    <div class="text-center d-flex justify-content-center gap-2" id="submit-action-area">
                        <button type="submit" name="submit_action" value="publish" id="btn-submit-post" class="btn btn-danger fw-bold px-4 py-2 rounded-1">
                            <i class="fa-solid fa-pen"></i> Đăng tin
                        </button>
                        <button type="submit" formaction="<?= URL_ROOT ?>/nguoi-dung/previewPost" formtarget="_blank" class="btn btn-outline-info fw-bold px-4 py-2"><i class="fa-solid fa-eye"></i> Xem trước</button>
                        <a href="#up_package" id="btn-buy-up-packages" class="btn btn-outline-primary fw-bold px-4 py-2 rounded-1 d-none" onclick="$('#up_package').focus();">
                            <i class="fa-solid fa-cart-shopping"></i> Chọn Mua Gói UP
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>

<!-- Leaflet Map CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- Leaflet Geocoder CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<!-- Script gọi API Tỉnh thành Việt Nam -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    const oldPostData = <?= json_encode(
        is_array($old ?? null) ? $old : [],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;
    const postForm = document.getElementById('postForm');

    // Restore submitted values when server-side validation returns errors.
    // Captcha must stay empty because a new code is generated for this page.
    postForm.querySelectorAll('input[name], select[name], textarea[name]').forEach(function(field) {
        const name = field.name;
        if (!Object.prototype.hasOwnProperty.call(oldPostData, name)
            || ['captcha', '_csrf_token', 'submit_action', 'province', 'district', 'ward'].includes(name)
            || field.type === 'file' || field.type === 'submit') {
            return;
        }

        const oldValue = oldPostData[name];
        if (field.type === 'checkbox' || field.type === 'radio') {
            const values = Array.isArray(oldValue) ? oldValue.map(String) : [String(oldValue)];
            field.checked = values.includes(String(field.value));
            return;
        }

        field.value = oldValue ?? '';
    });

    // Map Initialization
    let defaultLat = Number.parseFloat(oldPostData.latitude) || 10.769539; // 132 Lý Thánh Tông, Tân Phú
    let defaultLng = Number.parseFloat(oldPostData.longitude) || 106.631911;
    
    // Khởi tạo bản đồ
    let map = L.map('map').setView([defaultLat, defaultLng], 16);
    
    // Thêm tile layer (giao diện bản đồ từ OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Custom icon cho marker
    let customIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    // Thêm marker có thể kéo thả
    let marker = L.marker([defaultLat, defaultLng], {
        icon: customIcon,
        draggable: true
    }).addTo(map);

    // Thêm thanh tìm kiếm vị trí (Geocoder)
    let geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: "Tìm vị trí của bạn..."
    }).on('markgeocode', function(e) {
        let center = e.geocode.center;
        map.setView(center, map.getZoom());
        marker.setLatLng(center);
        $('#latitude').val(center.lat);
        $('#longitude').val(center.lng);
    }).addTo(map);
    
    // Cập nhật giá trị input ẩn khi kéo thả marker
    marker.on('dragend', function(e) {
        let position = marker.getLatLng();
        $('#latitude').val(position.lat);
        $('#longitude').val(position.lng);
    });

    // Cho phép click trên bản đồ để di chuyển marker
    map.on('click', function(e) {
        let position = e.latlng;
        marker.setLatLng(position);
        $('#latitude').val(position.lat);
        $('#longitude').val(position.lng);
    });
    let locationData = [];

    // Gọi API lấy danh sách Tỉnh/Thành phố
    $.ajax({
        url: 'https://provinces.open-api.vn/api/?depth=3',
        method: 'GET',
        success: function(data) {
            locationData = data;
            let provinceHtml = '<option value="">Tỉnh/Tp</option>';
            data.forEach(function(province) {
                provinceHtml += `<option value="${province.code}">${province.name}</option>`;
            });
            $('#province').html(provinceHtml);

            // Location options are loaded asynchronously; restore them in hierarchy order.
            if (oldPostData.province) {
                $('#province').val(String(oldPostData.province)).trigger('change');
                $('#district').val(String(oldPostData.district || '')).trigger('change');
                $('#ward').val(String(oldPostData.ward || ''));
            }
        }
    });

    // Khi chọn Tỉnh/Thành phố
    $('#province').change(function() {
        let provinceCode = $(this).val();
        let districtHtml = '<option value="">Quận / Huyện</option>';
        let wardHtml = '<option value="">Xã / Phường</option>';
        
        $('#district').prop('disabled', true).html(districtHtml);
        $('#ward').prop('disabled', true).html(wardHtml);

        if (provinceCode) {
            let province = locationData.find(p => p.code == provinceCode);
            if (province) {
                // Lưu tên tỉnh vào hidden input
                $('#tinh_thanh_hidden').val(province.name);
                if (province.districts) {
                    province.districts.forEach(function(district) {
                        districtHtml += `<option value="${district.code}">${district.name}</option>`;
                    });
                    $('#district').prop('disabled', false).html(districtHtml);
                }
            }
        } else {
            $('#tinh_thanh_hidden').val('');
        }
    });

    // Khi chọn Quận/Huyện
    $('#district').change(function() {
        let provinceCode = $('#province').val();
        let districtCode = $(this).val();
        let wardHtml = '<option value="">Xã / Phường</option>';
        
        $('#ward').prop('disabled', true).html(wardHtml);

        if (provinceCode && districtCode) {
            let province = locationData.find(p => p.code == provinceCode);
            if (province && province.districts) {
                let district = province.districts.find(d => d.code == districtCode);
                if (district && district.wards) {
                    district.wards.forEach(function(ward) {
                        wardHtml += `<option value="${ward.code}">${ward.name}</option>`;
                    });
                    $('#ward').prop('disabled', false).html(wardHtml);
                }
            }
        }
    });
    // VIP Pricing Calculation
    const discountPercent = <?= $data['discount'] ?? 0 ?>;
    const priceMap = <?= json_encode([0 => 0] + ($data['vipPrices'] ?? []), JSON_UNESCAPED_UNICODE) ?>;

    function calculatePrice() {
        let level = $('#vip_level').val();
        let days = parseInt($('#days').val()) || 0;
        let upPackagePrice = parseInt($('#up_package').val()) || 0;
        
        let pricePerDay = priceMap[level] || 0;
        let baseVIPPrice = pricePerDay * days;
        
        let basePrice = baseVIPPrice + upPackagePrice;
        let discountAmount = baseVIPPrice * (discountPercent / 100); // Khuyến mãi chỉ áp dụng cho gói VIP (hoặc cả hai tùy chỉnh)
        
        let finalPrice = basePrice - discountAmount;

        $('#base_price').text(new Intl.NumberFormat('vi-VN').format(basePrice));
        $('#discount_amount').text(new Intl.NumberFormat('vi-VN').format(discountAmount));
        $('#final_price').text(new Intl.NumberFormat('vi-VN').format(finalPrice));

        // Lượt UP chỉ dùng để đẩy tin sau khi đăng, không phải điều kiện tạo tin mới.
        let luotUpTin = <?= (int)($data['upTurns'] ?? 0) ?>;
        let submitBtn = $('#btn-submit-post');
        let confirmBtn = $('.btn-confirm-custom');

        submitBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger')
                 .html('<i class="fa-solid fa-pen"></i> Đăng tin');
        confirmBtn.prop('disabled', false).css({opacity: 1, cursor: 'pointer'});
        $('#confirm-final-price').text(new Intl.NumberFormat('vi-VN').format(finalPrice) + ' đ');

        if (luotUpTin < 1 && upPackagePrice === 0) {
            $('#btn-buy-up-packages').removeClass('d-none');
        } else {
            $('#btn-buy-up-packages').addClass('d-none');
        }
    }

    $('#vip_level, #days, #up_package').change(calculatePrice);
    
    // Call once on page load to initialize button state
    calculatePrice();

    // Image Upload Preview Logic
    window.selectedFiles = window.selectedFiles || [];
    $('#imageUploadInput').on('change', function(e) {
        let files = Array.from(e.target.files);
        let previewContainer = $('#imagePreviewContainer');
        
        files.forEach(file => {
            if (file.type.match('image.*')) {
                window.selectedFiles.push(file);
                
                let reader = new FileReader();
                reader.onload = function(e) {
                    let imgHtml = `
                        <div class="position-relative" style="width: 100px; height: 100px;">
                            <img src="${e.target.result}" class="img-thumbnail w-100 h-100 object-fit-cover" alt="Preview">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle remove-img-btn" style="transform: translate(30%, -30%); padding: 0.1rem 0.35rem; font-size: 0.7rem;" data-name="${file.name}">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    `;
                    previewContainer.append(imgHtml);
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Reset input so same file can be selected again if removed
        $(this).val('');
    });

    // Remove image preview
    $(document).on('click', '.remove-img-btn', function() {
        let fileName = $(this).data('name');
        window.selectedFiles = window.selectedFiles.filter(file => file.name !== fileName);
        $(this).parent().remove();
    });

    // Before submit, we need to append selectedFiles to formData or we can just keep them.
    // For a real upload, we would intercept form submit and use FormData.
});
</script>

<!-- ===== CUSTOM CONFIRM MODAL ===== -->
<style>
#confirmOverlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(10, 10, 30, 0.65);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    animation: overlayFadeIn 0.25s ease;
}
#confirmOverlay.show {
    display: flex;
}
@keyframes overlayFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

#confirmBox {
    background: linear-gradient(135deg, #1e1e3a 0%, #2a1f4e 100%);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 20px;
    box-shadow: 0 30px 80px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.1);
    padding: 40px 36px 32px;
    max-width: 420px;
    width: 90%;
    text-align: center;
    position: relative;
    animation: boxSlideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes boxSlideUp {
    from { opacity: 0; transform: translateY(40px) scale(0.92); }
    to   { opacity: 1; transform: translateY(0)  scale(1); }
}

#confirmBox .confirm-icon {
    width: 68px;
    height: 68px;
    background: linear-gradient(135deg, #f97316, #ef4444);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 0 0 10px rgba(239,68,68,0.15), 0 0 0 20px rgba(239,68,68,0.07);
    font-size: 28px;
    color: #fff;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 10px rgba(239,68,68,0.15), 0 0 0 20px rgba(239,68,68,0.07); }
    50%       { box-shadow: 0 0 0 14px rgba(239,68,68,0.20), 0 0 0 28px rgba(239,68,68,0.05); }
}

#confirmBox h5 {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 12px;
    letter-spacing: -0.3px;
}

#confirmBox p {
    color: rgba(255,255,255,0.72);
    font-size: 0.92rem;
    line-height: 1.65;
    margin-bottom: 28px;
}

#confirmBox .confirm-meta {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    padding: 10px 16px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fbbf24;
    font-size: 0.85rem;
    font-weight: 600;
}

#confirmBox .btn-cancel-custom {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.18);
    color: rgba(255,255,255,0.8);
    border-radius: 12px;
    padding: 11px 28px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
    flex: 1;
}
#confirmBox .btn-cancel-custom:hover {
    background: rgba(255,255,255,0.15);
    color: #fff;
    transform: translateY(-1px);
}

#confirmBox .btn-confirm-custom {
    background: linear-gradient(135deg, #f97316, #ef4444);
    border: none;
    color: #fff;
    border-radius: 12px;
    padding: 11px 28px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
    flex: 1;
    box-shadow: 0 6px 20px rgba(239,68,68,0.4);
}
#confirmBox .btn-confirm-custom:hover {
    background: linear-gradient(135deg, #fb923c, #f43f5e);
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(239,68,68,0.55);
}
#confirmBox .btn-confirm-custom:active {
    transform: translateY(0);
}

#confirmBox .btn-row {
    display: flex;
    gap: 12px;
}
</style>

<!-- Modal HTML -->
<div id="confirmOverlay">
    <div id="confirmBox">
        <div class="confirm-icon">
            <i class="fa-solid fa-paper-plane"></i>
        </div>
        <h5>Xác nhận đăng tin</h5>
        <p>Tin của bạn sẽ được gửi để chờ admin duyệt.</p>
        <div class="confirm-meta">
            <i class="fa-solid fa-coins"></i>
            Chi phí dịch vụ: <strong id="confirm-final-price" style="margin-left:4px;color:#fb923c;">0 đ</strong>
        </div>
        <div class="btn-row">
            <button class="btn-cancel-custom" onclick="closeConfirm()"><i class="fa-solid fa-xmark me-1"></i> Huỷ</button>
            <button class="btn-confirm-custom" onclick="submitConfirmed()"><i class="fa-solid fa-check me-1"></i> Đăng tin ngay</button>
        </div>
    </div>
</div>

<script>
function handleFormSubmit(event) {
    const submitter = event.submitter;
    const isPreview = submitter && submitter.hasAttribute('formtarget');

    if (isPreview) {
        return true;
    }

    event.preventDefault();
    document.getElementById('confirmOverlay').classList.add('show');
    return false;
}
function closeConfirm() {
    document.getElementById('confirmOverlay').classList.remove('show');
}
function submitConfirmed() {
    let dt = new DataTransfer();
    if (window.selectedFiles) {
        window.selectedFiles.forEach(file => dt.items.add(file));
    }
    document.getElementById('imageUploadInput').files = dt.files;

    document.getElementById('postForm').removeEventListener ? null : null;
    document.getElementById('postForm').onsubmit = null;
    document.getElementById('postForm').submit();
}
// Close when clicking backdrop
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});
</script>
