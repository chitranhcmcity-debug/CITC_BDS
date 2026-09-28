<?php

namespace App\Services;

use App\Models\SystemLog;
use App\Repositories\SystemLogRepository;

/** Application service for filtering, pagination, dashboard and retention. */
class SystemLogService
{
    private SystemLogRepository $repository;

    public function __construct(?SystemLogRepository $repository = null)
    {
        $this->repository = $repository ?? new SystemLogRepository;
    }

    public function dashboard(): array
    {
        return [
            'stats' => $this->normalizeStats($this->repository->dashboardStats()),
            'daily' => $this->fillDaily($this->repository->dailyStats(365), 365),
            'recent_errors' => $this->repository->list('error', ['order' => 'desc'], 5, 0),
            'recent_admin' => $this->repository->list('admin', ['order' => 'desc'], 5, 0),
        ];
    }

    public function listing(string $type, array $input): array
    {
        if (! SystemLog::validType($type)) {
            $type = 'activity';
        }
        $filters = $this->filters($type, $input);
        $requestedPerPage = (int) ($input['per_page'] ?? 25);
        $perPage = in_array($requestedPerPage, [25, 50, 100], true) ? $requestedPerPage : 25;
        $total = $this->repository->count($type, $filters);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($pages, max(1, (int) ($input['page'] ?? 1)));

        return [
            'type' => $type, 'filters' => $filters, 'rows' => $this->repository->list($type, $filters, $perPage, ($page - 1) * $perPage),
            'modules' => $this->repository->modules($type), 'total' => $total, 'page' => $page,
            'per_page' => $perPage, 'total_pages' => $pages,
        ];
    }

    public function detail(string $type, int $id): object|false
    {
        return SystemLog::validType($type) ? $this->repository->find($type, $id) : false;
    }

    public function export(string $type, array $input): array
    {
        if (! SystemLog::validType($type)) {
            return [];
        }

        return $this->repository->list($type, $this->filters($type, $input), 10000, 0);
    }

    public function resolve(int $id, int $adminId, bool $resolved): bool
    {
        return $this->repository->resolveError($id, $adminId, $resolved);
    }

    public function purge(int $months): array
    {
        $months = in_array($months, [6, 12, 24], true) ? $months : 12;

        return $this->repository->purgeBefore(date('Y-m-d H:i:s', strtotime("-{$months} months")));
    }

    private function filters(string $type, array $i): array
    {
        $date = static function ($v): string {
            $v = trim((string) $v);

            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';
        };

        return [
            'q' => mb_substr(trim((string) ($i['q'] ?? '')), 0, 200),
            'module' => preg_replace('/[^a-z0-9_-]/i', '', (string) ($i['module'] ?? '')),
            'action' => preg_replace('/[^a-z0-9_-]/i', '', (string) ($i['action'] ?? '')),
            'level' => in_array(($i['level'] ?? ''), SystemLog::ERROR_LEVELS, true) ? $i['level'] : '',
            'status' => in_array(($i['status'] ?? ''), SystemLog::LOGIN_STATUSES, true) ? $i['status'] : '',
            'resolved' => in_array((string) ($i['resolved'] ?? ''), ['0', '1'], true) ? (string) $i['resolved'] : '',
            'ip' => mb_substr(trim((string) ($i['ip'] ?? '')), 0, 45),
            'user_id' => max(0, (int) ($i['user_id'] ?? 0)),
            'from' => $date($i['from'] ?? ''), 'to' => $date($i['to'] ?? ''),
            'order' => ($i['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc',
        ];
    }

    private function normalizeStats(array $s): array
    {
        foreach (['activity_today', 'login_today', 'admin_today', 'error_today', 'failed_today', 'unresolved_errors'] as $key) {
            $s[$key] = (int) ($s[$key] ?? 0);
        }

        return $s;
    }

    private function fillDaily(array $rows, int $days): array
    {
        $lookup = [];
        foreach ($rows as $row) {
            $lookup[$row->period] = (array) $row;
        }
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $r = $lookup[$date] ?? [];
            $result[] = [
                'period' => $date, 'label' => date('d/m', strtotime($date)), 'activity' => (int) ($r['activity'] ?? 0),
                'admin' => (int) ($r['admin_actions'] ?? 0), 'login' => (int) ($r['logins'] ?? 0),
                'failed' => (int) ($r['failed_logins'] ?? 0), 'error' => (int) ($r['errors'] ?? 0),
            ];
        }

        return $result;
    }
}
