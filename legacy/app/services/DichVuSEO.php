<?php
class SEOService
{
    public function generate(string $title,string $description,array $keywords=[]):array
    {
        $cleanTitle=trim(strip_tags($title));$cleanDescription=preg_replace('/\s+/u',' ',trim(strip_tags($description)))??'';
        return['meta_title'=>mb_substr($cleanTitle,0,160),'meta_description'=>mb_substr($cleanDescription,0,320),'meta_keywords'=>mb_substr(implode(', ',array_unique(array_filter(array_map('trim',$keywords)))),0,500)];
    }
    public function slug(string $title,callable $exists):string
    {
        $base=$this->slugify($title)?:'tin-dang';$slug=$base;$i=2;while($exists($slug))$slug=$base.'-'.$i++;return$slug;
    }
    private function slugify(string $s):string{$s=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s)?:$s;$s=strtolower($s);$s=preg_replace('/[^a-z0-9]+/','-',$s)??'';return trim($s,'-');}
}
