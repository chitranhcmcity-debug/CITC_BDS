<?php

/** Automatically audits state-changing MVC actions without logging request secrets. */
class AuditMiddleware
{
    private static array $context = [];

    /** Enrich the current automatic audit event with business-level changes. */
    public static function enrich(string $description, array $oldData = [], array $newData = [], ?string $targetType = null, ?int $targetId = null): void
    {
        self::$context = compact('description','oldData','newData','targetType','targetId');
    }

    public static function capture(string $controller, string $method, array $params): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return;
        if (in_array($controller,['AnalyticsController','AdminLoginController'],true)) return;
        // Load the logger before shutdown; some SAPIs stop resolving new classes late in teardown.
        if (!class_exists('SystemLogger')) return;

        $actorId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $roleId = !empty($_SESSION['user_role_id']) ? (int)$_SESSION['user_role_id'] : null;
        $isAdmin = str_starts_with($controller,'Admin') && $roleId === 1;
        $module = self::module($controller);
        $targetId = isset($params[0]) && is_numeric($params[0]) ? (int)$params[0] : null;
        $targetType = strtolower(preg_replace('/(?<!^)[A-Z]/','_$0',str_replace(['Admin','Controller'],'',$controller)));
        $path = (string)($_SERVER['REQUEST_URI']??'');
        $adminPayload = $isAdmin ? $_POST : [];

        register_shutdown_function(static function () use ($isAdmin,$actorId,$module,$method,$targetType,$targetId,$path,$adminPayload): void {
            $status = http_response_code();
            $context = self::$context;
            $description = $context['description'] ?? sprintf('%s %s (%s)', strtoupper((string)($_SERVER['REQUEST_METHOD']??'POST')), $path, $status >= 400 ? 'thất bại' : 'thành công');
            $finalType = $context['targetType'] ?? $targetType;
            $finalId = $context['targetId'] ?? $targetId;
            if ($isAdmin) SystemLogger::admin($method,$module,$finalType,$finalId,$description,$context['oldData']??[],$context['newData']??$adminPayload,$actorId);
            else SystemLogger::activity($method,$module,$finalType,$finalId,$description,$context['newData']??[],$actorId);
            SystemLogger::flush();
        });
    }

    private static function module(string $controller): string
    {
        $name = str_replace(['Admin','Controller'],'',$controller);
        return strtolower((string)preg_replace('/(?<!^)[A-Z]/','_$0',$name));
    }
}
