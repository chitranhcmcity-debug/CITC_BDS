<?php require_once '../app/views/layouts/header.php'; ?>

<!-- Header -->
<div class="bg-primary text-white py-5 text-center">
    <div class="container py-4">
        <h1 class="display-4 fw-bold">Contact Us</h1>
        <p class="lead">Get in touch with our expert real estate agents today.</p>
    </div>
</div>

<section class="py-5 bg-light">
    <div class="container">
        
        <?php Session::flash('contact_success'); ?>

        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4">Contact Information</h4>
                        
                        <div class="d-flex mb-4 align-items-start">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-location-dot fs-5"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold mb-1">Office Address</h6>
                                <p class="text-muted mb-0">123 Main St, City, Country</p>
                            </div>
                        </div>

                        <div class="d-flex mb-4 align-items-start">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-phone fs-5"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold mb-1">Phone Number</h6>
                                <p class="text-muted mb-0">Mr.Chí: 036 818 0923</p>
                            </div>
                        </div>

                        <div class="d-flex mb-4 align-items-start">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-envelope fs-5"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold mb-1">Email Address</h6>
                                <p class="text-muted mb-0">info@citc-bds.com</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4">Send us a message</h4>
                        <form action="<?= URL_ROOT ?>/contact" method="POST">
                            <?= Csrf::field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-lg bg-light border-0 <?= (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?= $data['name']; ?>">
                                    <div class="invalid-feedback"><?= $data['name_err']; ?></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control form-control-lg bg-light border-0 <?= (!empty($data['phone_err'])) ? 'is-invalid' : ''; ?>" value="<?= $data['phone']; ?>">
                                    <div class="invalid-feedback"><?= $data['phone_err']; ?></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control form-control-lg bg-light border-0" value="<?= $data['email']; ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control form-control-lg bg-light border-0" rows="5"><?= $data['message']; ?></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg fw-bold px-5">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../app/views/layouts/footer.php'; ?>
