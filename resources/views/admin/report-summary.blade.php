@include('admin.layouts.header')
<section class="content"><div class="container-fluid"><div class="row g-3">@foreach($stats as $label=>$value)<div class="col-xl-4 col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted">{{ $label }}</div><div class="fs-2 fw-bold text-primary">{{ number_format($value) }}{{ $label === 'Tổng tiền nạp' ? ' đ' : '' }}</div></div></div></div>@endforeach</div></div></section>
@include('admin.layouts.footer')
