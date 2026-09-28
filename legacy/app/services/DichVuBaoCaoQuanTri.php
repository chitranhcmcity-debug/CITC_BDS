<?php
class AdminReportService
{
    public function __construct(private ?AdminReportRepository $repo=null){$this->repo??=new AdminReportRepository();}
    public function build(string $section,array $input): array { $f=ReportValidation::filters($input); return ['section'=>$section,'filters'=>$f,'overview'=>$this->repo->overview($f),'chart'=>$this->repo->chart($f),'sources'=>$this->repo->revenueSources($f),'topUsers'=>$this->repo->topUsers($f),'topPosts'=>$this->repo->topPosts($f),'transactions'=>$this->repo->transactions($f),'chat'=>$this->repo->chat($f)]; }
}
