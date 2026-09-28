<div class="card border-0 shadow-sm h-100">
 <div class="card-body">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><div><h2 class="h6 fw-bold mb-1">Hiệu quả tin đăng</h2><small class="text-muted">View, Call, Chat, Save, Share và CTR</small></div>
   <div class="btn-group btn-group-sm" role="group" aria-label="Khoảng thời gian">
    <?php foreach([7,30,90] as $period): ?><button class="btn btn-outline-primary chart-period <?= $d['period']===$period?'active':'' ?>" data-period="<?= $period ?>"><?= $period ?> ngày</button><?php endforeach; ?>
   </div>
  </div>
  <div class="dashboard-chart-wrap"><canvas id="dashboard-performance-chart" aria-label="Biểu đồ hiệu quả tin đăng"></canvas></div>
 </div>
</div>
