@include('admin.layouts.header')

@php
$labels = ['id'=>'ID','tieu_de'=>'Tiêu đề','trang_thai'=>'Trạng thái','luot_xem'=>'Lượt xem','noi_bat'=>'Nổi bật','ngay_tao'=>'Ngày tạo','ten'=>'Tên','email'=>'Email','dien_thoai'=>'Điện thoại','nguoi_phu_trach'=>'Phụ trách','ma_nguoi_dung'=>'Người dùng','so_tien'=>'Số tiền','tong_cong'=>'Tổng cộng','phuong_thuc'=>'Phương thức','ma_giao_dich'=>'Mã giao dịch','customer_name'=>'Khách hàng','customer_email'=>'Email','subject'=>'Chủ đề','status'=>'Trạng thái','priority'=>'Ưu tiên','assigned_staff_id'=>'Nhân viên','last_message_at'=>'Tin cuối','ma_vai_tro'=>'Vai trò','loai_tai_khoan'=>'Loại tài khoản','so_du'=>'Số dư','name'=>'Tên gói','price'=>'Giá','up_count'=>'Lượt up','bonus_up'=>'Thưởng','valid_days'=>'Số ngày','is_active'=>'Hoạt động','created_at'=>'Ngày tạo','khoa_cai_dat'=>'Khóa','gia_tri_cai_dat'=>'Giá trị','slug'=>'Slug','mo_ta'=>'Mô tả','is_system'=>'Hệ thống','user_id'=>'Người dùng','action'=>'Hành động','module'=>'Module','description'=>'Mô tả','ip_address'=>'IP'];
@endphp
<section class="content text-start"><div class="container-fluid">
    <div class="row mb-3"><div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Tổng số bản ghi</div><div class="fs-2 fw-bold text-primary">{{ number_format($total) }}</div></div></div></div></div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-bold">{{ $moduleTitle }}</h5>
            <form method="GET" class="d-flex gap-2"><input name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Tìm kiếm dữ liệu"><button class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>@if($search !== '')<a href="{{ url('/admin/'.$module) }}" class="btn btn-light btn-sm border">Đặt lại</a>@endif</form>
        </div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr>@foreach($columns as $column)<th>{{ $labels[$column] ?? $column }}</th>@endforeach</tr></thead><tbody>
        @forelse($rows as $row)<tr>@foreach($columns as $column)@php $value=$row->$column; @endphp<td>@if(in_array($column,['so_tien','tong_cong','so_du','price'],true)){{ number_format((float)$value) }} đ @elseif(in_array($column,['trang_thai','status','is_active'],true))<span class="badge bg-{{ in_array($value,['active','da_duyet','hoat_dong',1,'1'],true)?'success':'secondary' }}">{{ $value ?? '—' }}</span>@else<span title="{{ $value }}">{{ \Illuminate\Support\Str::limit((string)$value, 55) ?: '—' }}</span>@endif</td>@endforeach</tr>
        @empty<tr><td colspan="{{ count($columns) }}" class="text-center text-muted py-5">Không có dữ liệu phù hợp.</td></tr>@endforelse
        </tbody></table></div>
        @if($rows->hasPages())<div class="card-footer bg-white">{{ $rows->links() }}</div>@endif
    </div>
</div></section>
@include('admin.layouts.footer')
