<?php

/** Domain model for listing analytics event types. */
class PostAnalytics extends Model
{
    public const TYPES = ['view', 'call', 'chat', 'save', 'share', 'phone', 'zalo', 'contact'];

    public function __construct()
    {
        parent::__construct();
        $this->table = 'thong_ke_bai_dang';
    }

    public static function isValidType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }
}
