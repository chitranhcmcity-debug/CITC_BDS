<?php
class SettingUploadService
{
 public function image(string$field,string$prefix):?string{if(empty($_FILES[$field]['tmp_name'])||($_FILES[$field]['error']??1)!==UPLOAD_ERR_OK)return null;$tmp=$_FILES[$field]['tmp_name'];if((int)$_FILES[$field]['size']>2*1024*1024||!is_uploaded_file($tmp))return null;$mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);$ext=match($mime){'image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp','image/x-icon','image/vnd.microsoft.icon'=>'ico',default=>null};if(!$ext)return null;$dir=UPLOAD_ROOT_DIR.'/settings';if(!is_dir($dir))mkdir($dir,0755,true);$name=$prefix.'_'.date('YmdHis').'_'.bin2hex(random_bytes(3)).'.'.$ext;return move_uploaded_file($tmp,$dir.'/'.$name)?'settings/'.$name:null;}
}
