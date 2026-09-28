<?php
class AdminReportRepository
{
    private Database $db;
    public function __construct(){ $this->db=new Database(); }
    private function one(string $sql,array $p=[]): array { $this->db->query($sql); foreach($p as $k=>$v)$this->db->bind($k,$v); return (array)($this->db->single()?:[]); }
    private function all(string $sql,array $p=[]): array { $this->db->query($sql); foreach($p as $k=>$v)$this->db->bind($k,$v); return $this->db->resultSet(); }
    private function range(array $f,string $column='ngay_tao'): array { return ["{$column} BETWEEN :from AND :to",[':from'=>$f['from'].' 00:00:00',':to'=>$f['to'].' 23:59:59']]; }
    public function overview(array $f): array {
        [$r,$p]=$this->range($f);
        $users=$this->one("SELECT COUNT(*) total_users,SUM({$r}) new_users,SUM(trang_thai='hoat_dong') active_users,SUM(locked_until>NOW()) locked_users FROM nguoi_dung WHERE ma_vai_tro<>1",$p);
        $postFilter=''; $postParams=$p;
        if($f['region']!==''){ $postFilter.=' AND (tinh_thanh LIKE :region OR vi_tri LIKE :region2)'; $postParams[':region']=$postParams[':region2']='%'.$f['region'].'%'; }
        if($f['type']!==''){ $postFilter.=' AND loai_bat_dong_san LIKE :property_type'; $postParams[':property_type']='%'.$f['type'].'%'; }
        $posts=$this->one("SELECT COUNT(*) total_posts,SUM({$r}) new_posts,SUM(goi_vip>0) vip_posts,SUM(trang_thai='cho_duyet') pending_posts,SUM(trang_thai='tu_choi') rejected_posts,SUM(ngay_het_han<NOW()) expired_posts,COALESCE(SUM(luot_xem),0) views FROM du_an WHERE deleted_at IS NULL{$postFilter}",$postParams);
        $tx=$this->one("SELECT COUNT(*) transactions,COALESCE(SUM(so_tien),0) transaction_value,SUM(trang_thai='da_duyet') success_tx,SUM(trang_thai='tu_choi') failed_tx,COALESCE(SUM(CASE WHEN trang_thai='da_duyet' THEN so_tien ELSE 0 END),0) revenue FROM nap_tien WHERE {$r}",$p);
        $events=$this->one("SELECT COALESCE(SUM(type='call'),0) calls,COALESCE(SUM(type='chat'),0) chats,COALESCE(SUM(type='save'),0) saves FROM post_analytics WHERE created_at BETWEEN :from AND :to",$p);
        return array_merge($users,$posts,$tx,$events);
    }
    public function chart(array $f): array { [$r,$p]=$this->range($f); return $this->all("SELECT DATE(ngay_tao) label,COUNT(*) transactions,COALESCE(SUM(CASE WHEN trang_thai='da_duyet' THEN so_tien ELSE 0 END),0) revenue FROM nap_tien WHERE {$r} GROUP BY DATE(ngay_tao) ORDER BY label",$p); }
    public function revenueSources(array $f): array { [$r,$p]=$this->range($f); return $this->all("SELECT loai source,COUNT(*) quantity,COALESCE(SUM(so_tien),0) amount FROM chi_tieu WHERE {$r} GROUP BY loai ORDER BY amount DESC",$p); }
    public function topUsers(array $f): array { return $this->all("SELECT u.id,u.ten,u.email,(SELECT COUNT(*) FROM du_an d WHERE d.ma_nguoi_dung=u.id AND d.deleted_at IS NULL) posts,(SELECT COALESCE(SUM(n.so_tien),0) FROM nap_tien n WHERE n.ma_nguoi_dung=u.id AND n.trang_thai='da_duyet') deposited FROM nguoi_dung u WHERE u.ma_vai_tro<>1 ORDER BY posts DESC,deposited DESC LIMIT 10"); }
    public function topPosts(array $f): array { $where='';$p=[];if($f['region']!==''){$where.=' AND (tinh_thanh LIKE :region OR vi_tri LIKE :region2)';$p[':region']=$p[':region2']='%'.$f['region'].'%';}if($f['type']!==''){$where.=' AND loai_bat_dong_san LIKE :type';$p[':type']='%'.$f['type'].'%';}return $this->all("SELECT id,tieu_de,luot_xem,luot_luu,luot_click_sdt,goi_vip,trang_thai FROM du_an WHERE deleted_at IS NULL{$where} ORDER BY luot_xem DESC,luot_luu DESC LIMIT 10",$p); }
    public function transactions(array $f): array { [$r,$p]=$this->range($f); return $this->all("SELECT n.id,u.ten,n.so_tien,n.phuong_thuc,n.trang_thai,n.ngay_tao FROM nap_tien n LEFT JOIN nguoi_dung u ON u.id=n.ma_nguoi_dung WHERE n.{$r} ORDER BY n.ngay_tao DESC LIMIT 100",$p); }
    public function chat(array $f): array { $p=[':from'=>$f['from'].' 00:00:00',':to'=>$f['to'].' 23:59:59']; return $this->one("SELECT COUNT(*) conversations,SUM(status='closed') closed_conversations,COALESCE(AVG(TIMESTAMPDIFF(MINUTE,created_at,COALESCE(closed_at,updated_at))),0) avg_minutes FROM live_chat_conversations WHERE created_at BETWEEN :from AND :to",$p); }
}
