<?php

/** Validation server-side cho tạo và cập nhật tin đăng. */
class PostValidation
{
    public function validate(array $d,bool $draft=false):array
    {
        $e=[];$title=trim((string)($d['title']??''));$description=trim((string)($d['description']??''));
        if($title==='')$e['title']='Vui lòng nhập tiêu đề.';elseif(mb_strlen($title)>150)$e['title']='Tiêu đề không được vượt quá 150 ký tự.';
        if(!$draft&&mb_strlen($description)<100)$e['description']='Mô tả phải có ít nhất 100 ký tự.';
        if(!$draft){
            foreach(['category_id'=>'danh mục','property_type'=>'loại bất động sản','transaction_type'=>'loại giao dịch','province'=>'tỉnh/thành','district'=>'quận/huyện','ward'=>'phường/xã','address'=>'địa chỉ','contact_name'=>'người liên hệ','contact_phone'=>'số điện thoại'] as $key=>$label)if(trim((string)($d[$key]??''))==='')$e[$key]="Vui lòng nhập {$label}.";
            if((float)($d['price']??0)<=0)$e['price']='Giá phải lớn hơn 0.';if((float)($d['area']??0)<=0)$e['area']='Diện tích phải lớn hơn 0.';
            if(!preg_match('/^(\+?84|0)[3-9][0-9]{8}$/',preg_replace('/[\s.\-]/','',(string)($d['contact_phone']??''))))$e['contact_phone']='Số điện thoại không hợp lệ.';
        }
        if(!in_array(($d['transaction_type']??''),['ban','cho_thue'],true))$e['transaction_type']='Loại giao dịch không hợp lệ.';
        foreach(['video_url','tour360'] as $key)if(($d[$key]??'')!==''&&!filter_var($d[$key],FILTER_VALIDATE_URL))$e[$key]='URL không hợp lệ.';
        $year=(int)($d['construction_year']??0);if($year&&($year<1800||$year>(int)date('Y')+5))$e['construction_year']='Năm xây dựng không hợp lệ.';
        return $e;
    }
}
