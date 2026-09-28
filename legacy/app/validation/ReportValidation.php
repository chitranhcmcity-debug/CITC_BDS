<?php
class ReportValidation
{
    public static function filters(array $input): array
    {
        $requestedPeriod = (string)($input['period'] ?? '30d');
        $period = in_array($requestedPeriod, ['today','7d','30d','90d','year','custom'], true)
            ? $requestedPeriod
            : '30d';
        $today = date('Y-m-d');
        [$from, $to] = match ($period) {
            'today' => [$today, $today],
            '7d' => [date('Y-m-d', strtotime('-6 days')), $today],
            '90d' => [date('Y-m-d', strtotime('-89 days')), $today],
            'year' => [date('Y-01-01'), $today],
            'custom' => [(string)($input['from'] ?? ''), (string)($input['to'] ?? '')],
            default => [date('Y-m-d', strtotime('-29 days')), $today],
        };
        if (!self::date($from) || !self::date($to) || $from > $to) {
            $period = '30d'; $from = date('Y-m-d', strtotime('-29 days')); $to = $today;
        }
        return ['period'=>$period,'from'=>$from,'to'=>$to,'region'=>trim((string)($input['region']??'')),'type'=>trim((string)($input['type']??''))];
    }
    private static function date(string $value): bool { $d=DateTime::createFromFormat('Y-m-d',$value); return $d && $d->format('Y-m-d')===$value; }
}
