<?php
$s=$d['statistics'];
$cards=[
 ['total_posts','Tổng số tin','fa-layer-group','primary'],['visible_posts','Đang hiển thị','fa-eye','success'],
 ['vip_posts','Tin VIP','fa-crown','warning'],['expired_posts','Tin hết hạn','fa-calendar-xmark','danger'],
 ['pending_posts','Chờ duyệt','fa-clock','info'],['rejected_posts','Bị từ chối','fa-circle-xmark','danger'],
 ['views','Lượt xem','fa-chart-line','primary'],['saves','Lượt lưu','fa-heart','danger'],
 ['shares','Lượt chia sẻ','fa-share-nodes','success'],['chats','Lượt chat','fa-comments','info'],
 ['calls','Lượt gọi','fa-phone','warning'],
]; ?>
<div class="dashboard-stat-grid mb-4" aria-label="Thống kê tài khoản">
<?php foreach($cards as [$key,$label,$icon,$color]): ?>
  <article class="dashboard-stat-card"><span class="stat-icon text-bg-<?= $color ?>"><i class="fa-solid <?= $icon ?>"></i></span><div><div class="text-muted small"><?= $label ?></div><strong><?= number_format((int)$s[$key]) ?></strong></div></article>
<?php endforeach; ?>
</div>
