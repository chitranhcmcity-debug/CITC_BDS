<?php

declare(strict_types=1);

/**
 * PayOS payment configuration and helpers for pure PHP MVC projects.
 *
 * No Composer package is required. All HTTP calls use cURL first and
 * file_get_contents as a fallback.
 */

if (!function_exists('paymentSetting')) {
    function paymentSetting(string $envKey, string $settingKey): string
    {
        $envValue = (string)env($envKey, '');
        if ($envValue !== '') return $envValue;
        return class_exists('CaiDat') ? (string)CaiDat::get($settingKey) : '';
    }
}
defined('PAYOS_CLIENT_ID') || define('PAYOS_CLIENT_ID', paymentSetting('PAYOS_CLIENT_ID', 'payos_client_id'));
defined('PAYOS_API_KEY') || define('PAYOS_API_KEY', paymentSetting('PAYOS_API_KEY', 'payos_api_key'));
defined('PAYOS_CHECKSUM_KEY') || define('PAYOS_CHECKSUM_KEY', paymentSetting('PAYOS_CHECKSUM_KEY', 'payos_checksum_key'));
defined('PAYOS_ENDPOINT') || define('PAYOS_ENDPOINT', (string)env('PAYOS_ENDPOINT', 'https://api-merchant.payos.vn'));
defined('PAYOS_VERIFY_SSL') || define('PAYOS_VERIFY_SSL', !defined('URL_ROOT') || !str_contains(URL_ROOT, 'localhost'));

if (!function_exists('paymentPayosSignature')) {
    /**
     * Create a PayOS HMAC SHA256 signature.
     *
     * PayOS requires data to be sorted alphabetically by key before signing.
     * The signed string format is: key1=value1&key2=value2&...
     */
    function paymentPayosSignature(array $data): string
    {
        unset($data['signature']);
        ksort($data);

        $parts = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $parts[] = $key . '=' . $value;
        }

        return hash_hmac('sha256', implode('&', $parts), PAYOS_CHECKSUM_KEY);
    }
}

if (!function_exists('paymentPayosCreatePayment')) {
    /**
     * Create a PayOS payment request.
     *
     * Required $order keys:
     * - orderCode: numeric unique order code
     * - amount: payment amount in VND
     * - description: short description, max 25 chars recommended
     * - returnUrl: URL PayOS redirects customer to after payment
     * - cancelUrl: URL PayOS redirects customer to when cancelled
     *
     * Optional keys: buyerName, buyerEmail, buyerPhone, buyerAddress, items.
     */
    function paymentPayosCreatePayment(array $order): array
    {
        if (PAYOS_CLIENT_ID === '' || PAYOS_API_KEY === '' || PAYOS_CHECKSUM_KEY === '') {
            throw new RuntimeException('PayOS credentials are not configured.');
        }

        foreach (['orderCode', 'amount', 'description', 'returnUrl', 'cancelUrl'] as $field) {
            if (!isset($order[$field]) || $order[$field] === '') {
                throw new InvalidArgumentException('Missing PayOS field: ' . $field);
            }
        }

        $payload = [
            'orderCode' => (int) $order['orderCode'],
            'amount' => (int) $order['amount'],
            'description' => mb_substr((string) $order['description'], 0, 25),
            'returnUrl' => (string) $order['returnUrl'],
            'cancelUrl' => (string) $order['cancelUrl'],
        ];

        foreach (['buyerName', 'buyerEmail', 'buyerPhone', 'buyerAddress'] as $field) {
            if (!empty($order[$field])) {
                $payload[$field] = (string) $order[$field];
            }
        }

        if (!empty($order['items']) && is_array($order['items'])) {
            $payload['items'] = $order['items'];
        }

        $payload['signature'] = paymentPayosSignature([
            'amount' => $payload['amount'],
            'cancelUrl' => $payload['cancelUrl'],
            'description' => $payload['description'],
            'orderCode' => $payload['orderCode'],
            'returnUrl' => $payload['returnUrl'],
        ]);

        return paymentPostJson('v2/payment-requests', $payload);
    }
}

if (!function_exists('paymentPayosGetPayment')) {
    /**
     * Get a PayOS payment request by id/orderCode.
     */
    function paymentPayosGetPayment(string|int $orderCode): array
    {
        $orderCode = trim((string) $orderCode);
        if ($orderCode === '') {
            throw new InvalidArgumentException('Missing PayOS orderCode.');
        }

        return paymentGetJson('v2/payment-requests/' . rawurlencode($orderCode));
    }
}

if (!function_exists('paymentPayosVerifyWebhook')) {
    /**
     * Verify PayOS webhook signature.
     */
    function paymentPayosVerifyWebhook(array $payload): bool
    {
        if (empty($payload['signature']) || empty($payload['data']) || !is_array($payload['data'])) {
            return false;
        }

        return hash_equals(paymentPayosSignature($payload['data']), (string) $payload['signature']);
    }
}

if (!function_exists('paymentPostJson')) {
    /**
     * Send POST JSON request to PayOS.
     */
    function paymentPostJson(string $urlOrPath, array $data, int $timeout = 30): array
    {
        return paymentRequestJson('POST', $urlOrPath, $data, $timeout);
    }
}

if (!function_exists('paymentGetJson')) {
    /**
     * Send GET JSON request to PayOS.
     */
    function paymentGetJson(string $urlOrPath, int $timeout = 30): array
    {
        return paymentRequestJson('GET', $urlOrPath, null, $timeout);
    }
}

if (!function_exists('paymentRequestJson')) {
    function paymentRequestJson(string $method, string $urlOrPath, ?array $data = null, int $timeout = 30): array
    {
        $url = paymentPayosUrl($urlOrPath);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'x-client-id: ' . PAYOS_CLIENT_ID,
            'x-api-key: ' . PAYOS_API_KEY,
        ];

        $body = null;
        if ($data !== null) {
            $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($body === false) {
                throw new RuntimeException('Cannot encode PayOS payload.');
            }
        }

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                throw new RuntimeException('Cannot initialize cURL.');
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => strtoupper($method),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => PAYOS_VERIFY_SSL,
                CURLOPT_SSL_VERIFYHOST => PAYOS_VERIFY_SSL ? 2 : 0,
            ]);

            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new RuntimeException('PayOS cURL error: ' . $curlError);
            }

            return paymentDecodePayosResponse((string) $response, $httpCode);
        }

        $context = [
            'http' => [
                'method' => strtoupper($method),
                'header' => implode("\r\n", $headers),
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
        ];

        if ($body !== null) {
            $context['http']['content'] = $body;
        }

        $response = file_get_contents($url, false, stream_context_create($context));
        $httpCode = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $httpCode = (int) $matches[1];
        }

        if ($response === false) {
            throw new RuntimeException('PayOS request failed and cURL is not available.');
        }

        return paymentDecodePayosResponse((string) $response, $httpCode);
    }
}

if (!function_exists('paymentPayosUrl')) {
    function paymentPayosUrl(string $urlOrPath): string
    {
        if (str_starts_with($urlOrPath, 'http://') || str_starts_with($urlOrPath, 'https://')) {
            return $urlOrPath;
        }

        return rtrim(PAYOS_ENDPOINT, '/') . '/' . ltrim($urlOrPath, '/');
    }
}

if (!function_exists('paymentDecodePayosResponse')) {
    function paymentDecodePayosResponse(string $response, int $httpCode): array
    {
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid JSON response from PayOS: ' . $response);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $message = $decoded['desc'] ?? $decoded['message'] ?? $response;
            throw new RuntimeException('PayOS HTTP ' . $httpCode . ': ' . $message, $httpCode);
        }

        // Backward-compatible shape for existing project code that checks error === 0.
        if (!isset($decoded['error'])) {
            $decoded['error'] = (($decoded['code'] ?? null) === '00') ? 0 : ($decoded['code'] ?? null);
        }
        if (!isset($decoded['message'])) {
            $decoded['message'] = $decoded['desc'] ?? '';
        }

        return $decoded;
    }
}
