@include('admin.layouts.header')

<section class="content text-start">
    <div class="container-fluid">
        <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Tổng danh mục</div><div class="fs-2 fw-bold text-primary">{{ number_format($categoryCount) }}</div></div></div></div>
            <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Tổng tin đăng</div><div class="fs-2 fw-bold text-success">{{ number_format($projectCount) }}</div></div></div></div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-tags text-primary me-2"></i>Danh mục bất động sản</h5>
                <form method="GET" action="{{ url('/admin/danh-muc') }}" class="d-flex gap-2">
                    <input name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Tìm tên hoặc đường dẫn">
                    <button class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
                    @if($search !== '') <a href="{{ url('/admin/danh-muc') }}" class="btn btn-light btn-sm border">Đặt lại</a> @endif
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th class="ps-3">ID</th><th>Tên</th><th>Đường dẫn</th><th>Loại</th><th class="text-center">Trạng thái</th></tr></thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr><td class="ps-3 fw-semibold">#{{ $category->id }}</td><td>{{ $category->ten }}</td><td><code>{{ $category->duong_dan }}</code></td><td>{{ $category->loai ?: '—' }}</td><td class="text-center"><span class="badge {{ in_array($category->trang_thai, ['active', 'hoat_dong', 1, '1'], true) ? 'bg-success' : 'bg-secondary' }}">{{ $category->trang_thai ?: 'Chưa đặt' }}</span></td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">Không tìm thấy danh mục phù hợp.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

@include('admin.layouts.footer')
