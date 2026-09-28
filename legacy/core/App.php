<?php

/** Front controller router cua ung dung. */
class App
{
    private string $controllerClass = 'TrangChuController';

    private string $method = 'index';

    private array $params = [];

    public function __construct()
    {
        $segments = $this->segments();
        $fixedAction = null;
        $postRoutes = ['post' => 'index', 'editPost' => 'edit', 'deletePost' => 'delete', 'renewVipPost' => 'renew', 'toggleAutoRenewVip' => 'toggleAutoRenew', 'upPost' => 'up', 'myPost' => 'my', 'hidePost' => 'hide', 'showPost' => 'show', 'previewPost' => 'preview', 'deletePostImage' => 'deleteImage'];
        $favRoutes = ['daLuu' => 'index', 'toggleLuu' => 'toggle', 'removeLuu' => 'delete', 'removeAllLuu' => 'deleteAll'];
        $compRoutes = ['compare' => 'index', 'toggleCompare' => 'toggle', 'removeCompare' => 'delete', 'removeAllCompare' => 'deleteAll'];
        $profileRoutes = ['profile' => 'profileRouter', 'uploadAvatar' => 'uploadAvatar', 'change_password' => 'changePassword', 'loginHistory' => 'loginHistory', 'logoutAllDevice' => 'logoutAllDevice', 'sendVerifyEmail' => 'verifyEmail', 'sendOTP' => 'sendOTP', 'verifyOTP' => 'verifyOTP'];
        $publicRoutes = ['bang-gia' => ['TrangChuController', 'pricing']];
        if (isset($publicRoutes[$segments[0] ?? ''])) {
            [$this->controllerClass, $fixedAction] = $publicRoutes[array_shift($segments)];
        }
        if ($fixedAction === null && ($segments[0] ?? '') === 'nguoi-dung') {
            if (isset($postRoutes[$segments[1] ?? ''])) {
                array_shift($segments);
                $route = array_shift($segments);
                $this->controllerClass = 'PostController';
                $fixedAction = $postRoutes[$route];
            } elseif (isset($favRoutes[$segments[1] ?? ''])) {
                array_shift($segments);
                $route = array_shift($segments);
                $this->controllerClass = 'FavoriteController';
                $fixedAction = $favRoutes[$route];
            } elseif (isset($compRoutes[$segments[1] ?? ''])) {
                array_shift($segments);
                $route = array_shift($segments);
                $this->controllerClass = 'CompareController';
                $fixedAction = $compRoutes[$route];
            } elseif (isset($profileRoutes[$segments[1] ?? ''])) {
                array_shift($segments);
                $route = array_shift($segments);
                $this->controllerClass = 'ProfileController';
                $fixedAction = $profileRoutes[$route];
            } elseif (($segments[1] ?? '') === 'analytics') {
                array_shift($segments);
                array_shift($segments);
                $sub = $segments[0] ?? '';
                if ($sub === 'chart') {
                    array_shift($segments);
                    $fixedAction = 'chart';
                } elseif ($sub === 'export') {
                    array_shift($segments);
                    $fixedAction = 'export';
                } else {
                    $fixedAction = 'dashboard';
                }
                $this->controllerClass = 'AnalyticsController';
            } elseif (($segments[1] ?? '') === 'postAnalytics') {
                array_shift($segments);
                array_shift($segments);
                $this->controllerClass = 'AnalyticsController';
                $fixedAction = 'detail';
            } elseif (($segments[1] ?? '') === 'notifications') {
                array_shift($segments);
                array_shift($segments);
                $sub = $segments[0] ?? '';
                $this->controllerClass = 'NotificationController';
                if ($sub === '') {
                    $fixedAction = 'index';
                } elseif ($sub === 'read-all') {
                    $fixedAction = 'markAllRead';
                } elseif ($sub === 'clear') {
                    $fixedAction = 'clear';
                } elseif ($sub === 'read') {
                    array_shift($segments);
                    $fixedAction = 'markRead';
                } elseif (ctype_digit($sub)) {
                    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                        $fixedAction = 'delete';
                    } else {
                        $fixedAction = 'detail';
                    }
                }
            }
        }
        // Dashboard thành viên có các action lồng dưới /nguoi-dung/dashboard.
        if ($fixedAction === null && ($segments[0] ?? '') === 'nguoi-dung' && ($segments[1] ?? '') === 'dashboard') {
            array_shift($segments);
            array_shift($segments);
            $action = array_shift($segments) ?: 'index';
            $this->controllerClass = 'DashboardController';
            $fixedAction = $this->camel($action);
        }
        // Các route xác thực cấp gốc theo hợp đồng của module.
        if ($fixedAction === null && ($segments[0] ?? '') === 'verify-email') {
            array_shift($segments);
            $this->controllerClass = 'NguoiDungController';
            $fixedAction = 'verifyEmail';
        } elseif ($fixedAction === null && ($segments[0] ?? '') === 'verify-otp') {
            array_shift($segments);
            $this->controllerClass = 'NguoiDungController';
            $fixedAction = 'verifyOtp';
        }
        $isAdmin = ($segments[0] ?? '') === 'admin';

        if ($fixedAction !== null) {
            // Controller và action đã được xác định ở trên.
        } elseif ($isAdmin) {
            array_shift($segments);
            $controllerSegment = array_shift($segments) ?: 'dashboard';
            if ($controllerSegment === 'du-an') {
                $this->controllerClass = 'AdminPropertyController';
            } elseif ($controllerSegment === 'nguoi-dung') {
                $this->controllerClass = 'AdminUserController';
            } else {
                $this->controllerClass = 'Admin'.$this->studly($controllerSegment).'Controller';
            }
        } elseif ($segments) {
            $controllerSegment = array_shift($segments);
            if (strtolower($controllerSegment) === 'lien-he') {
                $controllerSegment = 'contact';
            } elseif (strtolower($controllerSegment) === 'wallet') {
                $controllerSegment = 'vi-dien-tu';
            }
            $this->controllerClass = $this->studly($controllerSegment).'Controller';
        }

        $controllerFile = "../app/controllers/{$this->controllerClass}.php";
        if (! is_file($controllerFile)) {
            $this->notFound();
        }

        require_once $controllerFile;
        if (! class_exists($this->controllerClass)) {
            $this->notFound();
        }

        $controller = new $this->controllerClass;

        if ($fixedAction !== null) {
            if (! $this->isRoutableAction($controller, $fixedAction)) {
                $this->notFound();
            }
            $this->method = $fixedAction;
        } elseif ($segments) {
            $rawAction = (string) array_shift($segments);

            // If segment is numeric (e.g. /author/1), treat it as index() + prepend as param
            if (ctype_digit($rawAction)) {
                if (! $this->isRoutableAction($controller, 'index')) {
                    $this->notFound();
                }
                array_unshift($segments, $rawAction);
            } else {
                // Analytics keeps the public /analytics/view contract without
                // colliding with Controller::view(), which renders templates.
                $candidate = $this->controllerClass === 'AnalyticsController' && $rawAction === 'view'
                    ? 'recordView'
                    : $this->camel($rawAction);
                if (! $this->isRoutableAction($controller, $candidate)) {
                    $this->notFound();
                }
                $this->method = $candidate;
            }
        } elseif (! $this->isRoutableAction($controller, 'index')) {
            $this->notFound();
        }

        $this->params = array_map(
            static fn (string $value): string => rawurldecode($value),
            array_values($segments)
        );

        AuditMiddleware::capture($this->controllerClass, $this->method, $this->params);

        try {
            call_user_func_array([$controller, $this->method], $this->params);
        } catch (ArgumentCountError|TypeError $exception) {
            error_log('[ROUTER] Invalid route parameters: '.$exception->getMessage());
            $this->notFound();
        }
    }

    private function isRoutableAction(object $controller, string $method): bool
    {
        if ($method === '' || str_starts_with($method, '_') || ! method_exists($controller, $method)) {
            return false;
        }

        $reflection = new ReflectionMethod($controller, $method);

        return $reflection->isPublic()
            && $reflection->getDeclaringClass()->getName() !== Controller::class;
    }

    private function segments(): array
    {
        $url = trim((string) ($_GET['url'] ?? ''), '/');
        if ($url === '') {
            return [];
        }

        $segments = explode('/', filter_var($url, FILTER_SANITIZE_URL));

        return array_values(array_filter($segments, static fn (string $part): bool => $part !== ''));
    }

    private function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', strtolower($value))));
    }

    /** Convert a kebab-case URL action to the camelCase controller method name. */
    private function camel(string $value): string
    {
        $studly = str_replace(' ', '', ucwords(str_replace('-', ' ', $value)));

        return $studly === '' ? '' : lcfirst($studly);
    }

    private function notFound(): never
    {
        http_response_code(404);
        $view = '../app/views/errors/404.php';
        if (is_file($view)) {
            require $view;
        } else {
            echo '404 - Không tìm thấy trang.';
        }
        exit;
    }
}
