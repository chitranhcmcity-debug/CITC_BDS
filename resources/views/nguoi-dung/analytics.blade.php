@include('layouts.header')
<?php $a = $data['analytics']; $s = $a['summary']; $r = $a['range']; ?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/analytics.css?v=<?= filemtime(APP_ROOT . '/public/css/analytics.css') ?>">

<div class="container py-4 analytics-shell">
  <div class="row">
    @include('nguoi-dung.sidebar')
    <main class="col-lg-9 col-md-8">
      <section class="analytics-header p-4 mb-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div><div class="small opacity-75 mb-1">Hiệu quả tin đăng</div><h2 class="h4 fw-bold mb-1">Dashboard Analytics</h2><p class="mb-0 small opacity-75"><?= htmlspecialchars($r['label']) ?> · <?= date('d/m/Y', strtotime($r['from'])) ?> – <?= date('d/m/Y', strtotime($r['to'])) ?></p></div>
          <a href="<?= URL_ROOT ?>/nguoi-dung/dashboard#danh-sach-tin" class="btn btn-light btn-sm fw-semibold"><i class="fa-solid fa-list me-1"></i> Quản lý tin</a>
        </div>
      </section>

      <form class="analytics-filter p-3 mb-4" method="GET">
        <div class="row g-2 align-items-end">
          <div class="col-lg-3 col-sm-6"><label class="form-label small fw-bold">Khoảng thời gian</label><select name="period" class="form-select form-select-sm" id="analyticsPeriod">
            <?php foreach (['today'=>'Hôm nay','yesterday'=>'Hôm qua','7'=>'7 ngày','30'=>'30 ngày','90'=>'90 ngày','year'=>'Năm nay','custom'=>'Tùy chọn'] as $key=>$label): ?>
              <option value="<?= $key ?>" <?= $r['period']===$key?'selected':'' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-lg-2 col-sm-6"><label class="form-label small fw-bold">Từ ngày</label><input type="date" name="from" value="<?= $r['from'] ?>" class="form-control form-control-sm"></div>
          <div class="col-lg-2 col-sm-6"><label class="form-label small fw-bold">Đến ngày</label><input type="date" name="to" value="<?= $r['to'] ?>" class="form-control form-control-sm"></div>
          <div class="col-lg-3 col-sm-6"><label class="form-label small fw-bold">Loại tin</label><select name="post_filter" class="form-select form-select-sm">
            <option value="">Tất cả tin</option><option value="vip" <?= $a['post_filter']==='vip'?'selected':'' ?>>Tin VIP</option><option value="normal" <?= $a['post_filter']==='normal'?'selected':'' ?>>Tin thường</option><option value="expired" <?= $a['post_filter']==='expired'?'selected':'' ?>>VIP đã hết hạn</option>
          </select></div>
          <div class="col-lg-2"><button class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-filter me-1"></i> Áp dụng</button></div>
        </div>
      </form>

      <?php
      $cards = [
        ['views','Lượt xem','fa-eye','primary'], ['contacts','Liên hệ','fa-headset','success'],
        ['calls','Gọi điện','fa-phone','danger'], ['chats','Chat','fa-comments','info'],
        ['saves','Lưu tin','fa-heart','warning'], ['shares','Chia sẻ','fa-share-nodes','primary'],
        ['phones','Xem số điện thoại','fa-address-card','secondary'], ['zalos','Xem Zalo','fa-comment-dots','success'],
      ]; ?>
      <div class="row g-3 mb-4">
        <?php foreach ($cards as [$key,$label,$icon,$color]): ?>
          <div class="col-xl-3 col-sm-6"><div class="card analytics-card p-3"><div class="d-flex align-items-center gap-3"><span class="metric-icon bg-<?= $color ?> bg-opacity-10 text-<?= $color ?>"><i class="fa-solid <?= $icon ?>"></i></span><div><div class="analytics-value"><?= number_format($s[$key]) ?></div><div class="analytics-label mt-1"><?= $label ?></div></div></div></div></div>
        <?php endforeach; ?>
        <div class="col-sm-6"><div class="card analytics-card p-3"><div class="analytics-label">CTR liên hệ / View</div><div class="analytics-value mt-2 text-primary"><?= number_format($s['ctr'],2) ?>%</div></div></div>
        <div class="col-sm-6"><div class="card analytics-card p-3"><div class="analytics-label">Conversion (Call + Chat) / View</div><div class="analytics-value mt-2 text-success"><?= number_format($s['conversion_rate'],2) ?>%</div></div></div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-6"><div class="card analytics-card analytics-best p-3"><div class="small text-muted">Tin nhiều lượt xem nhất</div><?php if($a['most_viewed']): ?><a class="fw-bold text-decoration-none mt-1" href="<?= URL_ROOT ?>/nguoi-dung/postAnalytics/<?= $a['most_viewed']->id ?>"><?= htmlspecialchars($a['most_viewed']->tieu_de) ?></a><div class="small mt-2"><b><?= number_format($a['most_viewed']->views) ?></b> lượt xem</div><?php else: ?><div class="text-muted mt-2">Chưa có tin đăng</div><?php endif; ?></div></div>
        <div class="col-lg-6"><div class="card analytics-card analytics-best p-3" style="border-left-color:#16a34a"><div class="small text-muted">Tin có chuyển đổi cao nhất</div><?php if($a['best_conversion']): ?><a class="fw-bold text-decoration-none mt-1" href="<?= URL_ROOT ?>/nguoi-dung/postAnalytics/<?= $a['best_conversion']->id ?>"><?= htmlspecialchars($a['best_conversion']->tieu_de) ?></a><div class="small mt-2"><b><?= number_format($a['best_conversion']->conversion_rate,2) ?>%</b> conversion</div><?php else: ?><div class="text-muted mt-2">Chưa có tin đăng</div><?php endif; ?></div></div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-12"><div class="card analytics-card p-3"><h6 class="fw-bold mb-3">Lượt xem theo ngày</h6><div class="analytics-chart"><canvas id="analyticsDailyChart"></canvas></div></div></div>
        <div class="col-lg-6"><div class="card analytics-card p-3"><h6 class="fw-bold mb-3">Lượt xem 12 tháng</h6><div class="analytics-chart"><canvas id="analyticsMonthlyChart"></canvas></div></div></div>
        <div class="col-lg-6"><div class="card analytics-card p-3"><h6 class="fw-bold mb-3">So sánh hành động</h6><div class="analytics-chart"><canvas id="analyticsActionsChart"></canvas></div></div></div>
        <div class="col-lg-6"><div class="card analytics-card p-3"><h6 class="fw-bold mb-3">Nguồn truy cập</h6><div class="analytics-chart"><canvas id="analyticsSourcesChart"></canvas></div></div></div>
      </div>

      <div class="card analytics-card mb-4"><div class="card-header bg-white border-0 pt-3 d-flex flex-wrap justify-content-between align-items-center gap-2"><h6 class="fw-bold mb-0">Top bài đăng</h6><div class="btn-group btn-group-sm">
        <?php foreach(['view'=>'View','contact'=>'Contact','chat'=>'Chat','save'=>'Save','share'=>'Share'] as $key=>$label): ?><a class="btn <?= $a['sort']===$key?'btn-primary':'btn-outline-primary' ?>" href="?<?= http_build_query(array_merge($_GET,['sort'=>$key,'page'=>1])) ?>"><?= $label ?></a><?php endforeach; ?>
      </div></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Tin đăng</th><th>View</th><th>Contact</th><th>Chat</th><th>Save</th><th>Share</th><th>Conversion</th></tr></thead><tbody>
        <?php if(!$a['top_posts']): ?><tr><td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu.</td></tr><?php endif; ?>
        <?php foreach($a['top_posts'] as $post): ?><tr><td><a class="fw-semibold text-decoration-none" href="<?= URL_ROOT ?>/nguoi-dung/postAnalytics/<?= $post->id ?>"><?= htmlspecialchars($post->tieu_de) ?></a></td><td><?= number_format($post->views) ?></td><td><?= number_format($post->contacts) ?></td><td><?= number_format($post->chats) ?></td><td><?= number_format($post->saves) ?></td><td><?= number_format($post->shares) ?></td><td><span class="badge bg-success-subtle text-success"><?= number_format($post->conversion_rate,2) ?>%</span></td></tr><?php endforeach; ?>
      </tbody></table></div></div>
      <?php if($a['total_pages']>1): ?><nav><ul class="pagination pagination-sm justify-content-center"><?php for($p=1;$p<=$a['total_pages'];$p++): ?><li class="page-item <?= $p===$a['page']?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"><?= $p ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>window.AnalyticsDashboardData=<?= json_encode(['daily'=>$a['daily'],'monthly'=>$a['monthly'],'sources'=>$a['sources'],'summary'=>$s], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= URL_ROOT ?>/public/js/analytics-dashboard.js?v=<?= filemtime(APP_ROOT . '/public/js/analytics-dashboard.js') ?>"></script>
@include('layouts.footer')
