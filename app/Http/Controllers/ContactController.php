<?php

namespace App\Http\Controllers;

use App\Models\KhachHang;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('lien-he.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string|max:5000',
            'project_id' => 'nullable|integer|exists:du_an,id',
        ]);

        $lead = new KhachHang;
        $ok = $lead->taoLead($data);

        return back()->with('success', $ok ? 'Gửi liên hệ thành công!' : 'Có lỗi xảy ra, vui lòng thử lại.');
    }
}
