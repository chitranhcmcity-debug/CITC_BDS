<?php

class GeminiChatService
{
    public function reply(string $message, array $history): string
    {
        $key = (string) env('GEMINI_API_KEY', '');
        if ($key === '') return 'Trợ lý đang được cấu hình. Bạn vui lòng liên hệ bộ phận tư vấn để được hỗ trợ ngay nhé.';

        $model = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) env('GEMINI_MODEL', 'gemini-2.5-flash')) ?: 'gemini-2.5-flash';
        $contents = [];
        foreach (array_slice($history, -12) as $item) {
            $role = ($item['role'] ?? '') === 'model' ? 'model' : 'user';
            $text = mb_substr(trim((string) ($item['text'] ?? '')), 0, 2000);
            if ($text !== '') $contents[] = ['role'=>$role, 'parts'=>[['text'=>$text]]];
        }
        $contents[] = ['role'=>'user', 'parts'=>[['text'=>mb_substr($message, 0, 2000)]]];

        $payload = [
            'systemInstruction'=>['parts'=>[['text'=>(string) env('GEMINI_SYSTEM_INSTRUCTION', 'Bạn là nhân viên tư vấn chuyên nghiệp của TimNhaDat.site. Trả lời bằng tiếng Việt, thân thiện, chính xác, ngắn gọn. Không bịa thông tin. Khi cần hỗ trợ chuyên sâu, hướng dẫn khách liên hệ tư vấn viên.')]]],
            'contents'=>$contents,
            'generationConfig'=>['temperature'=>0.6, 'maxOutputTokens'=>800],
        ];
        $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode($payload, JSON_UNESCAPED_UNICODE), CURLOPT_HTTPHEADER=>['Content-Type: application/json', 'x-goog-api-key: '.$key], CURLOPT_TIMEOUT=>20]);
        if (str_contains(URL_ROOT, 'localhost')) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $body = curl_exec($ch); $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch);
        if ($status < 200 || $status >= 300 || !is_string($body)) {
            error_log('[GEMINI PROXY] HTTP '.$status.' '.$error.' '.mb_substr((string) $body, 0, 500));
            return 'Trợ lý đang bận trong giây lát. Bạn vui lòng thử lại hoặc chọn “Liên hệ tư vấn” nhé.';
        }
        $data = json_decode($body, true);
        $text = trim((string) ($data['candidates'][0]['content']['parts'][0]['text'] ?? ''));
        return $text !== '' ? $text : 'Mình chưa có câu trả lời phù hợp. Bạn có thể mô tả rõ hơn nhu cầu được không?';
    }
}
