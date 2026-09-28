@include('layouts.header')
<?php $d=$dashboard; ?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/member-dashboard.css">

<main class="container-fluid container-xl py-4 member-dashboard">
  <div class="row g-4">
    @include('nguoi-dung.sidebar')
    <section class="col-lg-9 col-md-8">
      <?php foreach (['success'=>'success','error'=>'danger'] as $flash=>$type): if(Session::get($flash)): ?>
        <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
          <?= htmlspecialchars((string)Session::get($flash),ENT_QUOTES,'UTF-8'); Session::delete($flash); ?>
          <button class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
      <?php endif; endforeach; ?>

      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Xin chào, <?= htmlspecialchars($d['user']->ten,ENT_QUOTES,'UTF-8') ?></h1><p class="text-muted mb-0">Tổng quan hiệu quả tài khoản và tin đăng của bạn.</p></div>
        <a href="<?= URL_ROOT ?>/nguoi-dung/post" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Đăng tin mới</a>
      </div>

      <?php require '../app/views/dashboard/components/statistics.php'; ?>
      <div class="row g-4 mb-4">
        <div class="col-xl-8"><?php require '../app/views/dashboard/components/chart.php'; ?></div>
        <div class="col-xl-4"><?php require '../app/views/dashboard/components/wallet.php'; ?></div>
      </div>
      <?php require '../app/views/dashboard/components/recent-post.php'; ?>
      <div class="row g-4 mt-1">
        <div class="col-xl-6"><?php require '../app/views/dashboard/components/notification.php'; ?></div>
        <div class="col-xl-6"><?php require '../app/views/dashboard/components/transaction.php'; ?></div>
      </div>
    </section>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>window.DASHBOARD_DATA=<?= json_encode(['period'=>$d['period'],'chart'=>$d['chart']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;window.DASHBOARD_ROOT=<?= json_encode(URL_ROOT) ?>;</script>
<script src="<?= URL_ROOT ?>/public/js/member-dashboard.js"></script>
@include('layouts.footer')
