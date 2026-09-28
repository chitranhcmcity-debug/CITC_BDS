<?php

/** Aggregates cached, presentation-ready data for the admin dashboard. */
class AdminDashboardService
{
    private const TTL = 300;
    private AdminDashboardRepository $repository;

    public function __construct() { $this->repository = new AdminDashboardRepository(); }

    public function overview(int $days = 30): array
    {
        $days = DashboardValidation::period($days);
        return $this->remember('overview_v4_'.$days, function () use ($days): array {
            $statistics = $this->repository->statistics();
            return ['statistics'=>$statistics,'chart'=>$this->normalizedChart($days),'top'=>$this->repository->top(),
                'pending'=>$this->repository->pending(),'activity'=>$this->repository->recentActivity(),
                'errors'=>$this->repository->recentErrors(),'system'=>$this->system(),'period'=>$days];
        });
    }

    public function statistics(): array { return $this->remember('statistics', fn()=> $this->repository->statistics()); }
    public function chart(int $days): array { $days=DashboardValidation::period($days); return $this->remember('chart_v2_'.$days, fn()=> $this->normalizedChart($days)); }
    public function realtime(): array { return ['statistics'=>$this->repository->statistics(),'activity'=>$this->repository->recentActivity(),'generated_at'=>date(DATE_ATOM)]; }

    public function system(): array
    {
        $settings=CaiDat::getAll();
        $root=disk_total_space(APP_ROOT) ?: 1; $free=disk_free_space(APP_ROOT) ?: 0;
        return ['database'=>true,'storage'=>is_writable(APP_ROOT.'/storage'),'disk_used_percent'=>round(($root-$free)*100/$root,1),
            'mail'=>!empty($settings['smtp_host']),'payos'=>!empty($settings['payos_api_key']),
            'google_maps'=>!empty($settings['google_maps_key']),'cache'=>is_writable(sys_get_temp_dir())];
    }

    private function remember(string $key, callable $loader): array
    {
        $file=sys_get_temp_dir().'/admin_dashboard_'.md5($key).'.json';
        if(is_file($file)&&time()-filemtime($file)<self::TTL){$data=json_decode((string)file_get_contents($file),true);if(is_array($data))return$data;}
        $data=$loader(); @file_put_contents($file,json_encode($data,JSON_UNESCAPED_UNICODE),LOCK_EX); return$data;
    }

    private function normalizedChart(int $days): array
    {
        $rows=[];
        foreach($this->repository->chart($days) as$row){$rows[$row->day]=$row;}
        $result=[];
        for($offset=$days-1;$offset>=0;$offset--){
            $day=date('Y-m-d',strtotime("-{$offset} days"));$row=$rows[$day]??null;
            $result[]=(object)['day'=>$day,'revenue'=>(int)($row->revenue??0),'users'=>(int)($row->users??0),
                'posts'=>(int)($row->posts??0),'views'=>(int)($row->views??0),'chats'=>(int)($row->chats??0)];
        }
        return$result;
    }
}
