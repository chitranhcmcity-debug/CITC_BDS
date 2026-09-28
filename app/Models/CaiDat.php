<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaiDat extends Model
{
    protected $table = 'cai_dat';

    public $timestamps = false;

    protected $fillable = [
        'khoa_cai_dat',
        'gia_tri_cai_dat',
    ];

    public static function getAll()
    {
        $file = base_path('config/settings.json');
        if (! file_exists($file)) {
            return [
                'site_name' => 'CITC FullHouse',
                'address' => '123 Đường ABC, Quận X, TP.HCM',
                'hotline' => '0368180923',
                'email' => 'chitran.hcmcity@gmail.com',
                'facebook' => 'https://www.facebook.com/',
                'zalo' => 'https://zalo.me/0368180923',
                'youtube' => 'https://www.youtube.com/',
                'price_vip5' => '30000',
                'price_vip4' => '25000',
                'price_vip3' => '20000',
                'price_vip2' => '15000',
                'price_vip1' => '10000',
                'price_up60' => '30000',
                'price_up150' => '50000',
                'price_up500' => '100000',
                'price_up750' => '150000',
                'price_up1500' => '200000',
                'bonus_tier1' => '20',
                'bonus_tier2' => '50',
                'bonus_tier3' => '100',
                'bonus_tier4' => '150',
                'bonus_tier5' => '200',
                'bonus_tier6' => '250',
            ];
        }
        $json = file_get_contents($file);

        return json_decode($json, true);
    }

    public static function get($key)
    {
        $settings = self::getAll();

        return isset($settings[$key]) ? $settings[$key] : '';
    }

    public static function saveSettings($data)
    {
        $file = base_path('config/settings.json');
        $settings = self::getAll();

        foreach ($data as $k => $v) {
            $settings[$k] = $v;
        }

        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return file_put_contents($file, $json);
    }
}
