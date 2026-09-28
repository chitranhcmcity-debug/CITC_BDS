<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use App\Models\DuAn;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $query = DanhMuc::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('ten', 'like', "%{$search}%")
                    ->orWhere('duong_dan', 'like', "%{$search}%");
            });
        }

        return view('admin.category.index', [
            'title' => 'Quản lý danh mục',
            'search' => $search,
            'categories' => $query->orderBy('ten')->get(),
            'categoryCount' => DanhMuc::count(),
            'projectCount' => DuAn::count(),
        ]);
    }
}
