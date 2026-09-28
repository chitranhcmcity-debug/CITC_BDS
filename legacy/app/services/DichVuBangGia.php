<?php

/** Nguon duy nhat cho bang gia, khuyen mai va cach tinh phi. */
class PricingService
{
    public function vipDailyPrices(): array
    {
        return [
            1 => $this->settingInt('price_vip1', 10000),
            2 => $this->settingInt('price_vip2', 15000),
            3 => $this->settingInt('price_vip3', 20000),
            4 => $this->settingInt('price_vip4', 25000),
            5 => $this->settingInt('price_vip5', 30000),
        ];
    }

    public function upPackages(): array
    {
        return [
            60   => $this->settingInt('price_up60', 30000),
            150  => $this->settingInt('price_up150', 50000),
            500  => $this->settingInt('price_up500', 100000),
            750  => $this->settingInt('price_up750', 150000),
            1500 => $this->settingInt('price_up1500', 200000),
        ];
    }

    public function viewDiscount(int $totalViews): int
    {
        return $totalViews >= 5000 ? 15 : ($totalViews >= 1000 ? 5 : 0);
    }

    public function vipPrice(int $level, int $days, int $discountPercent = 0): int
    {
        $base = ($this->vipDailyPrices()[$level] ?? 0) * max(0, $days);
        return max(0, (int)floor($base * (100 - max(0, min(100, $discountPercent))) / 100));
    }

    public function postingPrice(int $level, int $days, int $upPackagePrice, int $discountPercent): int
    {
        $validUpPrice = in_array($upPackagePrice, $this->upPackages(), true) ? $upPackagePrice : 0;
        return $this->vipPrice($level, $days, $discountPercent) + $validUpPrice;
    }

    public function depositBonusPercent(int $amount): int
    {
        $tiers = $this->bonusTiers();
        return match (true) {
            $amount > 3000000 => $tiers[6],
            $amount >= 1000000 => $tiers[5],
            $amount >= 500000 => $tiers[4],
            $amount >= 200000 => $tiers[3],
            $amount >= 100000 => $tiers[2],
            $amount >= 50000 => $tiers[1],
            default => 0,
        };
    }

    public function bonusTiers(): array
    {
        return [
            1 => $this->settingInt('bonus_tier1', 20),
            2 => $this->settingInt('bonus_tier2', 50),
            3 => $this->settingInt('bonus_tier3', 100),
            4 => $this->settingInt('bonus_tier4', 150),
            5 => $this->settingInt('bonus_tier5', 200),
            6 => $this->settingInt('bonus_tier6', 250),
        ];
    }

    private function settingInt(string $key, int $default): int
    {
        $value = CaiDat::get($key);
        return $value === '' || !is_numeric($value) ? $default : max(0, (int)$value);
    }
}
