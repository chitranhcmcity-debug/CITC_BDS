@include('layouts.header')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0 rounded-0" style="background-color: #fafafa; border: 1px solid #e0e0e0 !important;">
                <div class="card-body p-5">
                    <h4 class="text-center fw-bold mb-4">ĐĂNG KÝ</h4>
                    <hr class="mb-4" style="border-top: 1px dashed #ccc; background: transparent;">
                    
                    <?php if (isset($data['error'])): ?>
                        <div class="alert alert-danger shadow-sm border-0 rounded-1">
                            <?= $data['error'] ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= URL_ROOT ?>/nguoi-dung/register" method="POST" enctype="multipart/form-data">
                        <?= Csrf::field() ?>
                        
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span>Họ và tên :</label>
                            <div class="col-md-9">
                                <input type="text" name="fullname" class="form-control rounded-1" required>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Số điện thoại 01 :</label>
                            <div class="col-md-9">
                                <input type="text" name="phone1" class="form-control rounded-1" required>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Tên tài khoản :</label>
                            <div class="col-md-9">
                                <input type="text" name="username" class="form-control rounded-1" required>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span> Mật khẩu :</label>
                            <div class="col-md-9">
                                <input type="password" name="password" class="form-control rounded-1" required>
                            </div>
                        </div>



                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span>Mô tả dịch vụ :</label>
                            <div class="col-md-9">
                                <input type="text" name="service_desc" class="form-control rounded-1" placeholder="Ví dụ: Chuyên phân phối chung cư Hà Nội" required>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <label class="col-md-3 col-form-label text-md-end">Thành viên giới thiệu :</label>
                            <div class="col-md-9">
                                <input type="text" name="referral" class="form-control rounded-1" placeholder="Mã thành viên giới thiệu nếu có">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-9 offset-md-3">
                                <p class="small text-muted mb-3 font-italic"><em>Upload ảnh đại diện của bạn. Ảnh đại diện đẹp sẽ tạo được sự tin tưởng với khách hàng. Một số dạng ảnh đại diện bạn có thể tham khảo.</em></p>
                                <div class="d-flex gap-2 mb-3">
                                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=100&h=120&fit=crop" class="border" alt="Avatar example 1">
                                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&h=120&fit=crop" class="border" alt="Avatar example 2">
                                    <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=100&h=120&fit=crop" class="border" alt="Avatar example 3">
                                    <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=100&h=120&fit=crop" class="border" alt="Avatar example 4">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <label class="col-md-3 col-form-label text-md-end">Ảnh đại diện (avatar) :</label>
                            <div class="col-md-9">
                                <input type="file" name="avatar" class="form-control rounded-1">
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <label class="col-md-3 col-form-label text-md-end"><span class="text-danger">*</span>Mã bảo mật :</label>
                            <div class="col-md-9 d-flex align-items-center">
                                <input type="text" name="captcha" class="form-control rounded-1 me-3" style="width: 150px;" placeholder="Mã xác nhận" required>
                                <div class="bg-black text-white px-4 py-2 fw-bold tracking-wide rounded-1" style="letter-spacing: 3px;"><?= Session::get('captcha') ?></div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-md-9 offset-md-3">
                                <button type="submit" class="btn btn-danger fw-bold px-5 rounded-1 py-2">Đăng ký</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
