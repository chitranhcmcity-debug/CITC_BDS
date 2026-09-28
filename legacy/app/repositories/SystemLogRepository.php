<?php

/** Persistence and optimized read queries for structured system logs. */
class SystemLogRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function insertActivity(array $d): bool
    {
        $this->db->query("INSERT INTO activity_logs
          (user_id,action,module,target_type,target_id,description,metadata,ip_address,user_agent,created_at)
          VALUES (:actor,:action,:module,:target_type,:target_id,:description,:metadata,:ip,:agent,NOW())");
        return $this->bindCommon($d, 'actor');
    }

    public function insertAdmin(array $d): bool
    {
        $this->db->query("INSERT INTO admin_logs
          (admin_id,action,module,target_type,target_id,description,old_data,new_data,ip_address,user_agent,created_at)
          VALUES (:actor,:action,:module,:target_type,:target_id,:description,:old_data,:new_data,:ip,:agent,NOW())");
        $this->bindCommonValues($d, 'actor');
        $this->db->bind(':old_data', $d['old_data'] ?? null);
        $this->db->bind(':new_data', $d['new_data'] ?? null);
        return $this->db->execute();
    }

    public function insertLogin(array $d): bool
    {
        $this->db->query("INSERT INTO login_history
          (user_id,email,ip_address,browser,platform,device,country,status,fail_reason,user_agent,created_at)
          VALUES (:user_id,:email,:ip,:browser,:platform,:device,:country,:status,:reason,:agent,NOW())");
        foreach ([':user_id'=>'user_id',':email'=>'email',':ip'=>'ip_address',':browser'=>'browser',':platform'=>'platform',
                  ':device'=>'device',':country'=>'country',':status'=>'status',':reason'=>'fail_reason',':agent'=>'user_agent'] as $param=>$key) {
            $this->db->bind($param, $d[$key] ?? null);
        }
        return $this->db->execute();
    }

    public function hasSuccessfulLoginFromIp(int $userId, string $ip): bool
    {
        $this->db->query("SELECT 1 FROM login_history WHERE user_id=:user AND ip_address=:ip AND status='success' LIMIT 1");
        $this->db->bind(':user',$userId,PDO::PARAM_INT);
        $this->db->bind(':ip',$ip);
        return (bool)$this->db->single();
    }

    public function insertError(array $d): bool
    {
        $this->db->query("INSERT INTO error_logs
          (module,level,error_type,message,stack_trace,request_url,request_method,user_id,ip_address,context,created_at)
          VALUES (:module,:level,:error_type,:message,:trace,:url,:method,:user_id,:ip,:context,NOW())");
        foreach ([':module'=>'module',':level'=>'level',':error_type'=>'error_type',':message'=>'message',':trace'=>'stack_trace',
                  ':url'=>'request_url',':method'=>'request_method',':user_id'=>'user_id',':ip'=>'ip_address',':context'=>'context'] as $param=>$key) {
            $this->db->bind($param, $d[$key] ?? null);
        }
        return $this->db->execute();
    }

    public function dashboardStats(): array
    {
        $sql = "SELECT
          (SELECT COUNT(*) FROM activity_logs WHERE created_at>=CURDATE()) activity_today,
          (SELECT COUNT(*) FROM login_history WHERE created_at>=CURDATE()) login_today,
          (SELECT COUNT(*) FROM admin_logs WHERE created_at>=CURDATE()) admin_today,
          (SELECT COUNT(*) FROM error_logs WHERE created_at>=CURDATE()) error_today,
          (SELECT COUNT(*) FROM login_history WHERE created_at>=CURDATE() AND status IN ('failed','blocked')) failed_today,
          (SELECT COUNT(*) FROM error_logs WHERE resolved=0) unresolved_errors";
        $this->db->query($sql);
        $row = $this->db->single();
        return $row ? (array)$row : [];
    }

    public function dailyStats(int $days = 30): array
    {
        $days = max(7, min(365, $days));
        $this->db->query("SELECT period,
          SUM(activity) activity, SUM(admin_actions) admin_actions, SUM(logins) logins,
          SUM(failed_logins) failed_logins, SUM(errors) errors
          FROM (
            SELECT DATE(created_at) period, COUNT(*) activity,0 admin_actions,0 logins,0 failed_logins,0 errors
              FROM activity_logs WHERE created_at>=DATE_SUB(CURDATE(),INTERVAL {$days} DAY) GROUP BY DATE(created_at)
            UNION ALL
            SELECT DATE(created_at),0,COUNT(*),0,0,0 FROM admin_logs WHERE created_at>=DATE_SUB(CURDATE(),INTERVAL {$days} DAY) GROUP BY DATE(created_at)
            UNION ALL
            SELECT DATE(created_at),0,0,COUNT(*),SUM(status IN ('failed','blocked')),0 FROM login_history WHERE created_at>=DATE_SUB(CURDATE(),INTERVAL {$days} DAY) GROUP BY DATE(created_at)
            UNION ALL
            SELECT DATE(created_at),0,0,0,0,COUNT(*) FROM error_logs WHERE created_at>=DATE_SUB(CURDATE(),INTERVAL {$days} DAY) GROUP BY DATE(created_at)
          ) x GROUP BY period ORDER BY period");
        return $this->db->resultSet();
    }

    public function list(string $type, array $filters, int $limit, int $offset): array
    {
        [$sql, $bindings] = $this->listSql($type, $filters, false);
        $sql .= ' ORDER BY l.created_at ' . (($filters['order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC') . ', l.id DESC LIMIT :limit OFFSET :offset';
        $this->db->query($sql);
        $this->bindAll($bindings);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function count(string $type, array $filters): int
    {
        [$sql, $bindings] = $this->listSql($type, $filters, true);
        $this->db->query($sql);
        $this->bindAll($bindings);
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    public function find(string $type, int $id): object|false
    {
        $definitions = $this->definitions();
        if (!isset($definitions[$type])) return false;
        $d = $definitions[$type];
        $this->db->query("SELECT l.*, u.ten actor_name, u.email actor_email FROM {$d['table']} l LEFT JOIN nguoi_dung u ON u.id=l.{$d['actor']} WHERE l.id=:id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->single();
    }

    public function resolveError(int $id, int $adminId, bool $resolved): bool
    {
        $this->db->query("UPDATE error_logs SET resolved=:resolved,resolved_by=:admin,resolved_at=" . ($resolved ? 'NOW()' : 'NULL') . " WHERE id=:id");
        $this->db->bind(':resolved', $resolved ? 1 : 0, PDO::PARAM_INT);
        $this->db->bind(':admin', $resolved ? $adminId : null);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function purgeBefore(string $before): array
    {
        $result = [];
        foreach ($this->definitions() as $type=>$d) {
            $this->db->query("DELETE FROM {$d['table']} WHERE created_at < :before");
            $this->db->bind(':before', $before);
            $this->db->execute();
            $result[$type] = $this->db->rowCount();
        }
        return $result;
    }

    public function modules(string $type): array
    {
        $d = $this->definitions()[$type] ?? null;
        if (!$d || $type === 'login') return [];
        $this->db->query("SELECT DISTINCT module FROM {$d['table']} WHERE module<>'' ORDER BY module");
        return array_map(static fn($r)=>(string)$r->module, $this->db->resultSet());
    }

    public function permissionsForRole(int $roleId): array
    {
        $this->db->query("SELECT log_type,can_view,can_export,can_manage FROM system_log_permissions WHERE role_id=:role");
        $this->db->bind(':role',$roleId,PDO::PARAM_INT);
        $result=[];
        foreach($this->db->resultSet() as $row)$result[$row->log_type]=[
            'view'=>(bool)$row->can_view,'export'=>(bool)$row->can_export,'manage'=>(bool)$row->can_manage
        ];
        return $result;
    }

    private function listSql(string $type, array $f, bool $count): array
    {
        $d = $this->definitions()[$type] ?? $this->definitions()['activity'];
        $select = $count ? 'COUNT(*) total' : 'l.*, u.ten actor_name, u.email actor_email';
        $sql = "SELECT {$select} FROM {$d['table']} l LEFT JOIN nguoi_dung u ON u.id=l.{$d['actor']} WHERE 1=1";
        $b = [];
        if (($f['q'] ?? '') !== '') {
            $searchCols = $type === 'login' ? "CONCAT_WS(' ',l.email,l.ip_address,l.browser,l.platform,l.fail_reason)" :
                ($type === 'error' ? "CONCAT_WS(' ',l.module,l.level,l.error_type,l.message,l.request_url,l.ip_address)" : "CONCAT_WS(' ',l.action,l.module,l.description,l.target_type,l.ip_address,u.ten,u.email)");
            $sql .= " AND {$searchCols} LIKE :q"; $b[':q']='%'.$f['q'].'%';
        }
        if (($f['module'] ?? '') !== '' && $type !== 'login') { $sql.=' AND l.module=:module'; $b[':module']=$f['module']; }
        if (($f['action'] ?? '') !== '' && in_array($type,['activity','admin'],true)) { $sql.=' AND l.action=:action'; $b[':action']=$f['action']; }
        if (($f['level'] ?? '') !== '' && $type === 'error') { $sql.=' AND l.level=:level'; $b[':level']=$f['level']; }
        if (($f['status'] ?? '') !== '' && $type === 'login') { $sql.=' AND l.status=:status'; $b[':status']=$f['status']; }
        if (($f['resolved'] ?? '') !== '' && $type === 'error') { $sql.=' AND l.resolved=:resolved'; $b[':resolved']=(int)$f['resolved']; }
        if (($f['ip'] ?? '') !== '') { $sql.=' AND l.ip_address LIKE :ip'; $b[':ip']='%'.$f['ip'].'%'; }
        if (($f['user_id'] ?? 0) > 0) { $sql.=" AND l.{$d['actor']}=:user_id"; $b[':user_id']=(int)$f['user_id']; }
        if (($f['from'] ?? '') !== '') { $sql.=' AND l.created_at>=:from'; $b[':from']=$f['from'].' 00:00:00'; }
        if (($f['to'] ?? '') !== '') { $sql.=' AND l.created_at<=:to'; $b[':to']=$f['to'].' 23:59:59'; }
        return [$sql,$b];
    }

    private function definitions(): array
    {
        return [
            'activity'=>['table'=>'activity_logs','actor'=>'user_id'],
            'admin'=>['table'=>'admin_logs','actor'=>'admin_id'],
            'login'=>['table'=>'login_history','actor'=>'user_id'],
            'error'=>['table'=>'error_logs','actor'=>'user_id'],
        ];
    }

    private function bindCommon(array $d, string $actorKey): bool
    {
        $this->bindCommonValues($d, $actorKey);
        $this->db->bind(':metadata', $d['metadata'] ?? null);
        return $this->db->execute();
    }

    private function bindCommonValues(array $d, string $actorKey): void
    {
        foreach ([':actor'=>$actorKey,':action'=>'action',':module'=>'module',':target_type'=>'target_type',':target_id'=>'target_id',
                  ':description'=>'description',':ip'=>'ip_address',':agent'=>'user_agent'] as $param=>$key) $this->db->bind($param,$d[$key]??null);
    }

    private function bindAll(array $bindings): void
    {
        foreach ($bindings as $param=>$value) $this->db->bind($param,$value);
    }
}
