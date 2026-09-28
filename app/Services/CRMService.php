<?php

namespace App\Services;

use App\Models\Database;

/**
 * CRMService – Logic chăm sóc khách hàng, phân phối yêu cầu, thông báo và tích hợp AI.
 */
class CRMService
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Tự động thông báo và chạy quy trình CRM cho một Contact mới.
     */
    public function processNewContact(array $contact): void
    {
        // 1. Tạo thông báo cho toàn bộ Admin hệ thống
        $this->notifyAdmins($contact);

        // 2. Gửi cảnh báo về Telegram Bot nếu cấu hình
        $this->sendToTelegram($contact);

        // 3. Ghi log tích hợp các kênh truyền thông khác (Zalo OA, SMS, WhatsApp)
        $this->sendToOtherChannels($contact);
    }

    /**
     * Tạo thông báo cho tất cả tài khoản Admin (ma_vai_tro = 1).
     */
    public function notifyAdmins(array $contact): void
    {
        try {
            // Lấy danh sách admin
            $this->db->query("SELECT id FROM nguoi_dung WHERE ma_vai_tro = 1 AND trang_thai = 'hoat_dong'");
            $admins = $this->db->resultSet();

            if (empty($admins)) {
                return;
            }

            $title = 'Yêu cầu tư vấn mới từ '.htmlspecialchars($contact['fullname'], ENT_QUOTES, 'UTF-8');
            $typeLabel = $this->getTypeLabel($contact['type']);
            $content = "Khách hàng: {$contact['fullname']} ({$contact['phone']}) vừa gửi yêu cầu: {$typeLabel}. Nội dung: ".mb_substr($contact['content'] ?? 'Không có', 0, 100).'...';

            // Tạo thông báo cho từng admin
            $notifService = new NotificationService;

            foreach ($admins as $admin) {
                $notifService->send((int) $admin->id, $title, $content);
            }
        } catch (Exception $e) {
            error_log('CRM NotifyAdmins Error: '.$e->getMessage());
        }
    }

    /**
     * Gửi tin nhắn cảnh báo tới Telegram.
     */
    public function sendToTelegram(array $contact): bool
    {
        $token = getenv('TELEGRAM_BOT_TOKEN');
        $chatId = getenv('TELEGRAM_CHAT_ID');

        if (empty($token) || empty($chatId)) {
            return false;
        }

        $typeLabel = $this->getTypeLabel($contact['type']);
        $message = "🔔 *YÊU CẦU TƯ VẤN MỚI* 🔔\n\n"
                 .'👤 *Khách hàng:* '.$contact['fullname']."\n"
                 .'📞 *Số điện thoại:* '.$contact['phone']."\n"
                 .'✉️ *Email:* '.($contact['email'] ?: 'Chưa cung cấp')."\n"
                 .'📂 *Phân loại:* '.$typeLabel."\n"
                 .'📝 *Tiêu đề:* '.($contact['subject'] ?: 'Không có')."\n"
                 .'💬 *Nội dung:* '.($contact['content'] ?: 'Trống')."\n\n"
                 .'🕒 *Thời gian:* '.date('d/m/Y H:i:s')."\n"
                 .'🖥️ *Thiết bị:* '.($contact['device'] ?: 'N/A').' / '.($contact['browser'] ?: 'N/A');

        $url = 'https://api.telegram.org/bot'.$token.'/sendMessage';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]));

        $isLocal = str_contains(defined('URL_ROOT') ? URL_ROOT : 'localhost', 'localhost');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, ! $isLocal);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response !== false;
    }

    /**
     * Mô phỏng gửi đến các kênh truyền thông khác.
     */
    private function sendToOtherChannels(array $contact): void
    {
        $typeLabel = $this->getTypeLabel($contact['type']);
        error_log("[CRM INTEGRATION] Đang gửi yêu cầu của {$contact['fullname']} ({$contact['phone']}) - [{$typeLabel}] tới Zalo OA, SMS Gateway và WhatsApp API...");
    }

    /**
     * AI: Tự động phân loại yêu cầu liên hệ dựa vào nội dung (Sử dụng Google Gemini).
     */
    public function aiClassifyRequest(string $content): string
    {
        $prompt = "Bạn là trợ lý ảo phân loại yêu cầu liên hệ cho website Bất động sản. Dựa vào nội dung yêu cầu sau: \"$content\".\n"
                ."Hãy phân loại và CHỈ trả về đúng 1 trong các từ khóa sau, KHÔNG thêm bất kỳ từ nào khác:\n"
                .'mua_nha, thue_nha, dang_ban, dang_cho_thue, hop_tac, khieu_nai, khac';

        $result = $this->askGemini($prompt);
        if ($result) {
            $cleaned = trim(strtolower($result));
            $validTypes = ['mua_nha', 'thue_nha', 'dang_ban', 'dang_cho_thue', 'hop_tac', 'khieu_nai', 'khac'];
            foreach ($validTypes as $t) {
                if (str_contains($cleaned, $t)) {
                    return $t;
                }
            }
        }

        return 'khac'; // Fallback
    }

    /**
     * AI: Tự động gợi ý phản hồi cho khách hàng.
     */
    public function aiSuggestResponse(string $customerContent): string
    {
        $prompt = "Khách hàng gửi yêu cầu tư vấn bất động sản với nội dung: \"$customerContent\".\n"
                .'Hãy đóng vai nhân viên tư vấn nhiệt tình và chuyên nghiệp của website TimNhaDat.site, viết một phản hồi ngắn gọn, lịch sự, hẹn thời gian gọi lại để tư vấn trực tiếp cho khách hàng (khoảng 3-5 câu).';

        return $this->askGemini($prompt) ?: 'Cảm ơn quý khách đã gửi yêu cầu. Chúng tôi sẽ liên hệ lại qua số điện thoại để hỗ trợ quý khách sớm nhất!';
    }

    /**
     * AI: Tự động phân công chuyên viên CSKH dựa trên loại yêu cầu.
     */
    public function aiAutoAssignCSKH(string $type, array $admins): ?int
    {
        if (empty($admins)) {
            return null;
        }

        // Đơn giản là chọn ngẫu nhiên hoặc có thể viết prompt cho Gemini chọn dựa trên chức vụ,
        // ở đây chúng ta cân bằng tải (Round-Robin / Random) làm fallback, hoặc hỏi AI:
        $adminInfo = [];
        foreach ($admins as $admin) {
            $adminInfo[] = "ID: {$admin['id']} - Tên: {$admin['ten']}";
        }

        $prompt = "Phân loại yêu cầu: \"$type\". Danh sách nhân viên CSKH:\n"
                .implode("\n", $adminInfo)."\n"
                .'Hãy chọn một nhân viên phù hợp nhất để xử lý yêu cầu này. Chỉ trả về ID của nhân viên đó dưới dạng số nguyên duy nhất.';

        $result = $this->askGemini($prompt);
        if ($result && is_numeric(trim($result))) {
            return (int) trim($result);
        }

        // Fallback: Random
        return (int) $admins[array_rand($admins)]['id'];
    }

    /**
     * Lấy nhãn tên loại yêu cầu.
     */
    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'mua_nha' => 'Mua nhà',
            'thue_nha' => 'Thuê nhà',
            'dang_ban' => 'Đăng bán',
            'dang_cho_thue' => 'Đăng cho thuê',
            'hop_tac' => 'Hợp tác kinh doanh',
            'khieu_nai' => 'Khiếu nại',
            default => 'Khác',
        };
    }

    /**
     * Gửi yêu cầu tới API Google Gemini.
     */
    private function askGemini(string $prompt): ?string
    {
        $apiKey = getenv('GEMINI_API_KEY');
        if (empty($apiKey)) {
            return null;
        }

        $postData = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 500,
            ],
        ];

        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key='.$apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $isLocal = str_contains(defined('URL_ROOT') ? URL_ROOT : 'localhost', 'localhost');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, ! $isLocal);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return null;
        }

        $result = json_decode($response, true);

        return $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }
}
