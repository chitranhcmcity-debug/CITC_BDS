<?php

class SettingService
{
    private SettingRepository $repo;

    public function __construct()
    {
        $this->repo = new SettingRepository();
    }

    public function all(): array
    {
        return array_replace(CaiDat::getAll(), $this->repo->all());
    }

    public function update(array $input, int $admin): array
    {
        $allowed = ['site_name','slogan','description','address','hotline','email','facebook','youtube','tiktok','zalo','copyright','logo','logo_dark','logo_light','favicon','default_banner','share_image','meta_title','meta_description','keywords','canonical','robots','sitemap','google_search_console','google_analytics','facebook_pixel','smtp_host','smtp_port','smtp_auth','smtp_secure','smtp_user','smtp_from_email','smtp_from_name','payos_client_id','payos_return_url','payos_webhook_url','google_maps_key','google_geocoding_enabled','google_places_enabled','upload_max_mb','image_extensions','video_extensions','document_extensions','upload_path'];
        $data = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $input)) {
                $data[$key] = trim((string)$input[$key]);
            }
        }

        foreach (['smtp_pass','payos_api_key','payos_checksum_key'] as $secret) {
            if (trim((string)($input[$secret] ?? '')) !== '') {
                $data[$secret] = trim((string)$input[$secret]);
            }
        }

        $errors = SettingValidation::validate($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $old = $this->all();
        $ok = $this->repo->save($data) && CaiDat::save($data) !== false;
        if ($ok) {
            $safeData = $data;
            foreach (array_keys($safeData) as $key) {
                if (str_contains($key, 'key') || str_contains($key, 'pass')) {
                    $safeData[$key] = '[REDACTED]';
                }
            }
            SystemLogger::admin('update', 'cai_dat', 'settings', null, 'Cập nhật cài đặt hệ thống', [], $safeData, $admin);
        }

        return ['success' => $ok, 'errors' => []];
    }
}
