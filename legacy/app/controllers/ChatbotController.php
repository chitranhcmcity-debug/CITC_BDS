<?php

class ChatbotController extends Controller
{
    public function send(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['success'=>false,'message'=>'Method not allowed'], 405); return; }
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $_POST['_csrf_token'] = (string) ($input['_csrf_token'] ?? '');
        if (!Csrf::verify(false)) { $this->json(['success'=>false,'message'=>'Phiên làm việc không hợp lệ.'], 403); return; }

        $now = time(); $hits = array_values(array_filter((array) Session::get('gemini_rate_hits'), fn($t)=>$t>$now-60));
        if (count($hits) >= 12) { $this->json(['success'=>false,'message'=>'Bạn gửi quá nhanh. Vui lòng chờ một phút.'], 429); return; }
        $hits[]=$now; Session::set('gemini_rate_hits',$hits);

        $message = trim((string) ($input['message'] ?? ''));
        if ($message === '' || mb_strlen($message) > 2000) { $this->json(['success'=>false,'message'=>'Tin nhắn không hợp lệ.'], 422); return; }
        $history = is_array($input['history'] ?? null) ? array_slice($input['history'], -12) : [];
        require_once APP_ROOT.'/app/services/DichVuTroChuyenGemini.php';
        $reply = (new GeminiChatService())->reply($message, $history);
        $this->json(['success'=>true,'reply'=>$reply]);
    }

    private function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
