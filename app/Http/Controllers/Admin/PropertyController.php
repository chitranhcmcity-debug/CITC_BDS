<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use App\Models\DuAn;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'status' => (string) $request->query('status', ''),
            'vip_level' => (string) $request->query('vip_level', ''),
            'category_id' => (string) $request->query('category_id', ''),
            'transaction_type' => (string) $request->query('transaction_type', ''),
            'location' => (string) $request->query('location', ''),
            'search' => trim((string) $request->query('search', '')),
            'start_date' => (string) $request->query('start_date', ''),
            'end_date' => (string) $request->query('end_date', ''),
        ];

        $query = DuAn::query()
            ->leftJoin('danh_muc as c', 'c.id', '=', 'du_an.ma_danh_muc')
            ->leftJoin('nguoi_dung as u', 'u.id', '=', 'du_an.ma_nguoi_dung')
            ->select('du_an.*', 'c.ten as category_name', 'u.ten as seller_name');

        if ($filters['status'] !== '') {
            $query->where('du_an.trang_thai', $filters['status']);
        }
        if ($filters['vip_level'] !== '') {
            $query->where('du_an.goi_vip', (int) $filters['vip_level']);
        }
        if ($filters['category_id'] !== '') {
            $query->where('du_an.ma_danh_muc', (int) $filters['category_id']);
        }
        if ($filters['transaction_type'] !== '') {
            $query->where('du_an.loai_giao_dich', $filters['transaction_type']);
        }
        if ($filters['location'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('du_an.tinh_thanh', 'like', '%'.$filters['location'].'%')
                    ->orWhere('du_an.vi_tri', 'like', '%'.$filters['location'].'%');
            });
        }
        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $q->where('du_an.tieu_de', 'like', $term)
                    ->orWhere('du_an.id', $filters['search'])
                    ->orWhere('u.email', 'like', $term)
                    ->orWhere('u.dien_thoai', 'like', $term);
            });
        }
        if ($filters['start_date'] !== '') {
            $query->whereDate('du_an.ngay_tao', '>=', $filters['start_date']);
        }
        if ($filters['end_date'] !== '') {
            $query->whereDate('du_an.ngay_tao', '<=', $filters['end_date']);
        }

        $perPage = 20;
        $page = max(1, (int) $request->query('page', 1));
        $total = (clone $query)->count();
        $list = $query->orderByDesc('du_an.id')
            ->forPage($page, $perPage)
            ->get()
            ->all();

        $base = DuAn::query()->whereNull('deleted_at');
        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('trang_thai', 'cho_duyet')->count(),
            'rejected' => (clone $base)->where('trang_thai', 'tu_choi')->count(),
            'vip' => (clone $base)->where('goi_vip', '>', 0)->where('ngay_het_han_vip', '>=', now())->count(),
            'expired' => (clone $base)->where('ngay_het_han', '<', now())->count(),
            'locked' => (clone $base)->where('trang_thai', 'khoa')->count(),
        ];

        return view('admin.property.index', [
            'title' => 'Quản lý tin đăng bất động sản',
            'list' => $list,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'stats' => $stats,
            'categories' => DanhMuc::query()->orderBy('ten')->get(),
            'filters' => $filters,
        ]);
    }
}
