<?php

namespace App\Services;

/** Gửi OTP xác thực số điện thoại qua SMS gateway eSMS. */
class SMSService
{
    private const ESMS_ENDPOINT = 'https://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_post_json/';

    public function sendOTP(string $phone, string $otp): array
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (! preg_match('/^0(?:3|5|7|8|9)\d{8}$/', $phone)) {
            return ['success' => false, 'message' => 'Số điện thoại Việt Nam không hợp lệ.'];
        }

        $isLocal = strtolower((string) env('APP_ENV', 'production')) === 'local';
        $provider = strtolower(trim((string) env('SMS_PROVIDER', '')));
        if ($provider === '' && $isLocal) {
            return ['success' => true, 'delivery' => 'local_preview', 'message' => 'Môi trường local: mã OTP điện thoại được hiển thị để kiểm thử, không gửi qua Gmail.'];
        }
        if ($provider !== 'esms') {
            return ['success' => false, 'message' => 'Chưa cấu hình SMS gateway để gửi OTP đến điện thoại.'];
        }

        $apiKey = trim((string) env('ESMS_API_KEY', ''));
        $secretKey = trim((string) env('ESMS_SECRET_KEY', ''));
        $brandname = trim((string) env('ESMS_BRANDNAME', ''));
        if ($apiKey === '' || $secretKey === '' || $brandname === '') {
            return ['success' => false, 'message' => 'Thiếu API Key, Secret Key hoặc Brandname của eSMS.'];
        }
        if (! function_exists('curl_init')) {
            return ['success' => false, 'message' => 'Máy chủ chưa bật cURL để kết nối SMS gateway.'];
        }

        $template = (string) env('SMS_OTP_TEMPLATE', '{OTP} la ma OTP xac thuc so dien thoai cua TimNhaDat. Ma co hieu luc 5 phut.');
        $payload = json_encode([
            'ApiKey' => $apiKey, 'SecretKey' => $secretKey, 'Phone' => $phone,
            'Content' => str_replace('{OTP}', $otp, $template), 'Brandname' => $brandname,
            'SmsType' => '2', 'IsUnicode' => '0',
            'Sandbox' => filter_var(env('ESMS_SANDBOX', false), FILTER_VALIDATE_BOOL) ? '1' : '0',
            'RequestId' => bin2hex(random_bytes(12)),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $curl = curl_init(self::ESMS_ENDPOINT);
        $curlOptions = [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => $payload, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 15];
        $caBundle = trim((string) env('ESMS_CA_BUNDLE', ''));
        if ($caBundle !== '' && is_file($caBundle)) {
            $curlOptions[CURLOPT_CAINFO] = $caBundle;
        }
        curl_setopt_array($curl, $curlOptions);
        $raw = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($raw === false || $curlError !== '' || $httpCode < 200 || $httpCode >= 300) {
            error_log('[SMS] eSMS connection failed: HTTP '.$httpCode.' '.$curlError);

            return ['success' => false, 'message' => 'Không thể kết nối dịch vụ SMS. Vui lòng thử lại sau.'];
        }
        $response = json_decode($raw, true);
        if (! is_array($response) || (string) ($response['CodeResult'] ?? '') !== '100') {
            $code = (string) ($response['CodeResult'] ?? 'invalid_response');
            error_log('[SMS] eSMS rejected request: code='.$code);
            if ($code === '103') {
                return ['success' => false, 'message' => 'Tài khoản eSMS không đủ số dư để gửi mã OTP. Vui lòng nạp tiền eSMS rồi thử lại.'];
            }

            return ['success' => false, 'message' => 'Nhà cung cấp SMS từ chối yêu cầu gửi mã OTP.'];
        }

        return ['success' => true, 'delivery' => 'sms', 'message' => 'Mã OTP 6 số đã được gửi bằng SMS đến số điện thoại '.substr($phone, 0, 3).'****'.substr($phone, -3).'.'];
    }
}
