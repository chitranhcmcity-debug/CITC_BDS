<?php
class SettingValidation
{
 public static function validate(array $in):array{$errors=[];foreach(['email','smtp_from_email'] as $k)if(!empty($in[$k])&&!filter_var($in[$k],FILTER_VALIDATE_EMAIL))$errors[$k]='Email không hợp lệ.';if(isset($in['smtp_port'])&&((int)$in['smtp_port']<1||(int)$in['smtp_port']>65535))$errors['smtp_port']='Cổng SMTP không hợp lệ.';if(isset($in['upload_max_mb'])&&((int)$in['upload_max_mb']<1||(int)$in['upload_max_mb']>200))$errors['upload_max_mb']='Dung lượng phải từ 1 đến 200MB.';return$errors;}
}
