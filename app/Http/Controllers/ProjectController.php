<?php

namespace App\Http\Controllers;

use App\Models\YeuThich;
use App\Repositories\PropertyRepository;
use App\Services\AnalyticsService;
use App\Services\PropertyDetailService;

class ProjectController extends Controller
{
    public function detail($slug)
    {
        $detailService = new PropertyDetailService;
        $detailData = $detailService->getDetail($slug);

        if (! $detailData) {
            abort(404);
        }

        $project = $detailData['post'];
        $images = $detailData['images'];
        $amenities = $detailData['amenities'];
        $videoEmbed = $detailData['videoEmbed'];
        $mapData = $detailData['mapData'];
        $breadcrumb = $detailData['breadcrumb'];

        // Record view using AnalyticsService
        $analyticsService = new AnalyticsService;
        if ($analyticsService->record((int) $project->id, 'view')) {
            $project->luot_xem = ($project->luot_xem ?? 0) + 1;
        }

        // Check if saved
        $isSaved = false;
        $userId = auth()->id();
        if ($userId) {
            $isSaved = (new YeuThich)->kiemTraDaLuu((int) $userId, (int) $project->id);
        }

        // Get related and same author posts
        $related = $detailService->getRelated($project);
        $sameAuthorPosts = (new PropertyRepository)->findByUser(
            (int) $project->ma_nguoi_dung,
            (int) $project->id,
            6
        );

        $shareUrl = url('/du-an/'.htmlspecialchars($project->duong_dan, ENT_QUOTES));

        $reportReasons = [
            'thong_tin_sai' => 'Thông tin không chính xác',
            'hinh_anh_sai' => 'Hình ảnh sai / không liên quan',
            'gia_sai' => 'Giá hiển thị sai',
            'lua_dao' => 'Nghi ngờ lừa đảo',
            'tin_trung_lap' => 'Tin đăng trùng lặp',
            'khac' => 'Lý do khác',
        ];

        return view('du-an.detail', compact(
            'project',
            'images',
            'amenities',
            'videoEmbed',
            'mapData',
            'breadcrumb',
            'isSaved',
            'related',
            'sameAuthorPosts',
            'shareUrl',
            'reportReasons'
        ));
    }
}
