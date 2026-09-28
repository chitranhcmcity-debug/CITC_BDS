<?php
/**
 * RealtimeService – Xử lý cơ chế Server-Sent Events (SSE) để truyền phát thông báo thời gian thực.
 * Sử dụng cơ chế file-based buffer cực kỳ nhẹ, tối ưu hoá cho PHP Standard.
 */
class RealtimeService
{
    /**
     * Gửi tin nhắn Realtime tới Client.
     */
    public function publish(int $userId, array $notification): void
    {
        $bufferFile = $this->getBufferFilePath($userId);
        
        $events = [];
        if (file_exists($bufferFile)) {
            $raw = @file_get_contents($bufferFile);
            $events = json_decode($raw, true) ?: [];
        }
        
        $events[] = $notification;
        
        @file_put_contents($bufferFile, json_encode($events, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /**
     * Luồng lặp vô tận duy trì kết nối SSE cho Client.
     */
    public function streamConnection(int $userId): void
    {
        // Release the session lock before opening a long-running connection.
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        // Vô hiệu hóa giới hạn thời gian chạy của PHP script
        @set_time_limit(0);
        
        // Thiết lập header phục vụ SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Tắt buffering trên Nginx nếu có

        // Đảm bảo PHP flush output ngay lập tức
        if (function_exists('ob_end_clean')) {
            @ob_end_clean();
        }
        ob_implicit_flush(true);

        $bufferFile = $this->getBufferFilePath($userId);
        
        // Gửi thông điệp bắt đầu kết nối thành công
        echo "retry: 5000\n";
        echo "data: " . json_encode(['connected' => true, 'user_id' => $userId]) . "\n\n";
        @ob_flush();
        flush();

        // Xóa file đệm cũ khi vừa kết nối mới
        if (file_exists($bufferFile)) {
            @unlink($bufferFile);
        }

        // Vòng lặp lắng nghe sự kiện
        $startedAt = time();
        while (time() - $startedAt < 25) {
            // Kiểm tra xem trình duyệt đã đóng tab/hủy kết nối chưa
            if (connection_aborted()) {
                break;
            }

            if (file_exists($bufferFile)) {
                $raw = @file_get_contents($bufferFile);
                if (!empty($raw)) {
                    // Xóa file đệm ngay lập tức để tránh trùng lặp sự kiện
                    @unlink($bufferFile);
                    
                    $events = json_decode($raw, true) ?: [];
                    foreach ($events as $event) {
                        echo "event: notification\n";
                        echo "data: " . json_encode($event, JSON_UNESCAPED_UNICODE) . "\n\n";
                    }
                    @ob_flush();
                    flush();
                }
            }

            // Tạm dừng 1 giây trước khi quét tệp tin tiếp theo
            sleep(1);
        }
    }

    private function getBufferFilePath(int $userId): string
    {
        return sys_get_temp_dir() . '/bds_sse_events_' . $userId . '.json';
    }
}
