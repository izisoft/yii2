<?php
namespace izi\filemanager;
use Yii;

class Local extends FileManager
{
    
    
    
    public function upload_files($post,$o = []){
        return $this->uploadFiles($post,$o);
    }
    
    public function uploadFiles($post,$o = []){
        $time = time();
        $year = date("Y",$time); $month = date("m",$time);
        $rename = isset($o['rename']) && $o['rename'] == false ? false : true;
        $replace = isset($o['replace']) && $o['replace'] == true ? true : false;
        $multiple = isset($o['multiple']) && $o['multiple'] == true ? true : false;
        $extension = isset($o['type']) ? $o['type'] : 'files';
        $extension_block = array('php','html','js','c','py','json','phtml');
        $file_extensions = $this->file_extension_upload($extension);
        ///////////////////////////////////////////////
        if(is_array($post['name'])){
            $multiple = true;
        }else{
            $post = array(
                'name'=>array($post['name']),
                'type'=>array($post['type']),
                'tmp_name'=>array($post['tmp_name']),
                'error'=>array($post['error']),
                'size'=>array($post['size']),
            );
            $multiple = false;
        }
        $results = [];
        
        foreach($post['name'] as $k=>$v){
            $error = false;
            if($post['error'][$k] == 0){
                $filename_img = $post['name'][$k];
                $filesize_img = $post['size'][$k];
                $filetype_img = $post['type'][$k];
                $filetemp_img = $post['tmp_name'][$k];
                ///////////////////////////////////////////////
                $fx = explode('/', $filetype_img);
                $pos = strrpos($filename_img,'.',1);
                $file_type = substr($filename_img,$pos+1);
                if(!in_array(strtolower($file_type), $file_extensions) || in_array(strtolower($file_type), $extension_block)){
                    $error = true;
                }
                if(!$error){
                    ///////////////////////////////////////////////
                    $folder_save = isset($o['folder']) ? $o['folder'].'/'.$fx[0].'s' : $fx[0].'s'.'/'.$year.'/'.$month;
                    $folder = '/medias/'.__SITE_NAME__.'/'.$folder_save;	
                    $folder = str_replace('//','/',$folder);
                    if(!file_exists(__ROOT_PATH__.$folder)){
                        @mkdir(__ROOT_PATH__.$folder,0755,true);
                    }
                    if(!$rename){
                        $file_name_save = unMark(substr($filename_img, 0,$pos)). '.' .$file_type;
                    }else {
                        $file_name_save = md5(unMark(substr($filename_img, 0,$pos)).$time) . '.' .$file_type;
                    }
                    $remote_file = __ROOT_PATH__.$folder.'/'.$file_name_save;
                    if(!$replace){
                        $counter = 0;
                        while(@filesize ($remote_file) > 0){
                            $file_name_save = unMark(substr($filename_img, 0,$pos)).'-'.(++$counter). '.' .$file_type;
                            $remote_file = __ROOT_PATH__.$folder.'/'.$file_name_save;
                        }
                    }
                    if(@move_uploaded_file($filetemp_img,$remote_file) !== false){
                        $results[] = DYNAMIC_SCHEME_DOMAIN .  $folder . '/'.$file_name_save;
                    }else{
                        $results[] = "";
                    }
                }
            }}
            if($multiple){
                return $results;
            }elseif(!empty($results)){
                return $results[0];
            }
            return '';	
    }
}