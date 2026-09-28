<?php
class PermissionMiddleware{public static function handle(string$permission):void{AuthMiddleware::handle(true);(new AuthorizationService())->require($permission);}}
