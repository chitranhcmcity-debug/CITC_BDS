<?php

namespace App\Http\Controllers;

use App\Services\NewsService;

class NewsController extends Controller
{
    public function detail($slug)
    {
        $newsService = new NewsService;
        $data = $newsService->getDetailPage($slug);
        if (empty($data)) {
            abort(404);
        }

        // Record view (anti-spam)
        $newsService->recordView((int) $data['post']->id);

        // Current user status
        $userId = auth()->id();
        $data['hasLiked'] = $userId
            ? $newsService->hasLiked((int) $data['post']->id, (int) $userId)
            : false;
        $data['userId'] = $userId;
        $data['csrfToken'] = csrf_token();

        return view('tin-tuc.detail', $data);
    }
}
