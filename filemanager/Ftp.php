<?php
namespace izi\filemanager;
use Yii;

class Ftp extends FileManager
{
    public $label ,$root_dir, $host_address, $username, $password, $web_address, $root_directory, $host_port, $ssl_mode;
    
    public $dir = '/';
    
    public $isLogged = false;
    
    private function halt($msg,$line=__LINE__){
        echo "Đã xảy ra lỗi: $msg<br/>\n";
        exit();
    }
    
    private $_connectString;
    public function getConnectString(){
        if($this->_connectString == null){
            if($this->ssl_mode == 1){
                
                $this->_connectString = @ftp_ssl_connect($this->host_address,$this->host_port) or
                
                $this->_connectString = @ftp_ssl_connect("localhost") or
                
                ////////////////////////////////////////////////
                
                $this->_connectString = @ftp_connect($this->host_address,$this->host_port) or
                
                $this->_connectString = @ftp_connect("localhost") or
                
                $this->halt("Không kết nối được server ảnh");
                
            }else{
                
                $this->_connectString = @ftp_connect($this->host_address,$this->host_port) or
                
                $this->_connectString = @ftp_connect("localhost") or
                
                $this->halt("Không kết nối được server ảnh");
                
            }
        }
        return $this->_connectString;
    }
    
    private $_login;
    public function getLogin(){
        if($this->_login == null){
            $this->_login = @ftp_login($this->getConnectString(), $this->username,  $this->password);
        }
        return $this->_login;
    }
    
    /**
     * 
     */
    
    private function close(){
        
        @ftp_quit($this->_connectString);
        
    }
    
    
    private function systype(){
        
        return ftp_systype($this->_connectString);
        
    }
    
    private function pwd(){
        
        $this->getLogin();
        
        $dir = ftp_pwd($this->_connectString);
        
        $this->root_directory = $dir;
        
        return $dir;
        
    }
    
    private function cdup(){
        
        $this->getLogin();
        
        $isok =  ftp_cdup($this->_connectString);
        
        if($isok) $this->root_directory = $this->pwd();
        
        return $isok;
        
    }
    
    private function cd($dir){
        
        $this->getLogin();
        
        $isok = ftp_chdir($this->_connectString,$dir);
        
        if($isok) $this->root_directory = $dir;
        
        return $isok;
        
    }
    
    private function nlist($dir=""){
        
        $this->getLogin();
        
        if(!$dir) $dir = ".";
        
        $arr_dir = ftp_nlist($this->_connectString,$dir);
        
        return $arr_dir;
        
    }
    
    private function rawlist($dir="/"){
        
        $this->getLogin();
        
        $arr_dir = ftp_rawlist($this->_connectString,$dir);
        
        return $arr_dir;
        
    }
    
    private function mkdir($dir){
        
        $this->getLogin();
        
        return @ftp_mkdir($this->_connectString,$dir);
        
    }
    
    private function file_size($file){
        
        $this->getLogin();
        
        $size = ftp_size($this->_connectString,$file);
        
        return $size;
        
    }
    
    private function chmod($file,$mode=0666){
        
        $this->getLogin();
        
        return ftp_chmod($this->_connectString,$file,$mode);
        
    }
     
    private function get($local_file,$remote_file,$mode=FTP_BINARY){
        
        $this->getLogin();
        
        return ftp_get($this->_connectString,$local_file,$remote_file,$mode);
        
    }
    
    
    
    private  function put($remote_file,$local_file,$mode=FTP_BINARY){
        
        //echo $remote_file.'----'.$local_file; exit();
        
        if($local_file!=""){
            
            $this->getLogin();
            
            return @ftp_put($this->_connectString,$remote_file,$local_file,$mode);
            
        }else{
            return false;
        }
        
    }
    
    private function rename($oldname, $newname){
        
        $this->getLogin();
        
        if(@ftp_rename($this->_connectString, $oldname, $newname)){           
            return $newname;
        }
        return false;
            
    }
    
    private function ftp_directory_exists($ftp, $dir){
        
        // Get the current working directory
        $this->getLogin();
        $origin = @ftp_pwd($this->_connectString);
        
        // Attempt to change directory, suppress errors
        
        if (@ftp_chdir($this->_connectString, $dir)){
            
            // If the directory exists, set back to origin
            
            ftp_chdir($this->_connectString, $origin);
            
            return true;
            
        }
        return false;
        
    }
    
    
    private function MkDir2($path,$mode = 0){
        
        $this->getLogin();
        
        $dir=explode("/", $path);
        
        $path="";
        
        $ret = true;
        
        for ($i=0;$i<count($dir);$i++){
            
            $path.="/".$dir[$i];
            
            //echo "$path\n";
            
            $path = str_replace('//','/',$path);
            
            if(!@ftp_chdir($this->_connectString,$path)){
                
                @ftp_chdir($this->_connectString,"/");
                
                if(!@ftp_mkdir($this->_connectString,$path)){
                    
                    $ret=false;
                    
                    break;
                    
                }
                
                if($mode > 0) @ftp_chmod($this->_connectString,$mode,$path);
                
            }
            
        }
        
        return $ret;
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Upload
     */
    
    public function upload_file_ckeditor($path,$filename,$file){
        $this->getLogin();
        if (substr($path,-1) != '/'){
            $path = $path . '/';
        }
        
        if(!$this->ftp_directory_exists($this->_connectString,$path)){
            $this->MkDir2($path);
        }
        $folder = $this->get_folder_upload_file($filename);
        //var_dump($path); exit;
        if(!$this->ftp_directory_exists($this->_connectString,$path . $folder)){
            $this->MkDir2($path . $folder);
        }
        //
        //var_dump($path. $folder . '/' . $filename); exit;
        //
        $this->put($path. $folder . '/' . $filename, $file);
        //var_dump($path . '/' .$filename); exit;
        $this->close();
    }
    
    /**
     * 
     * @param unknown $post
     * @param array $o
     * @return string[]|string
     */
    public function upload_files($post,$o = []){
        return $this->uploadFiles($post,$o);
    }
    
    public function uploadFiles($post,$o = []){
        @date_default_timezone_set('Asia/Ho_Chi_Minh');
        @set_time_limit(0);
       // if(1 || defined('USED_HOST_MEDIA') && USED_HOST_MEDIA){
        $this->getLogin();	
        $time = time();
        $year = date("Y",$time); $month = date("m",$time);
        $rename = isset($o['rename']) && $o['rename'] == true ? true : false;
        $replace = isset($o['replace']) && $o['replace'] == true ? true : false;
        $multiple = isset($o['multiple']) && $o['multiple'] == true ? true : false;
        $extension = isset($o['type']) ? $o['type'] : 'files';
        $extension_block = array('php','html','js','c','py','json');
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
                    if(isset($o['folder_save']) && $o['folder_save'] != ''){
                        //$folder_save = isset($o['folder']) ? $o['folder'].'/'.$fx[0].'s' : $fx[0].'s'.'/'.$year.'/'.$month;
                        $folder = $o['folder_save'];
                    }else{
                        $folder_save = isset($o['folder']) ? $o['folder'].'/'.$fx[0].'s' : $fx[0].'s'.'/'.$year.'/'.$month;
                        $folder = $folder_save;
                    }
                    
                    if(!(isset($o['include_site_name']) && $o['include_site_name'] == false)){
                        $folder = '/'.__SITE_NAME__.'/'.$folder;
                    }
                    
                    if(substr($folder, 0,1) != '/'){
                        $folder = '/' . $folder;
                    }
                    
                    $folder = str_replace('//','/',$folder);
                    
                    
                    if(!$this->ftp_directory_exists($this->_connectString, $this->root_directory.$folder)){
                        $this->MkDir2($this->root_directory.$folder);
                    }
                    if(!$rename){
                        $file_name_save = unMark(substr($filename_img, 0,$pos)). '.' .$file_type;
                    }else {
                        $file_name_save = md5(unMark(substr($filename_img, 0,$pos)).$time) . '.' .$file_type;
                    }
                    $remote_file = $this->root_directory.$folder.'/'.$file_name_save;
                    if(!$replace){
                        $counter = 0;
                        while(@ftp_size($this->_connectString, $remote_file) > 0){
                            $file_name_save = unMark(substr($filename_img, 0,$pos)).'-'.(++$counter). '.' .$file_type;
                            $remote_file = $this->root_directory.$folder.'/'.$file_name_save;
                        }
                    }
                    if($this->put($remote_file, $filetemp_img) !== false){
                        $results[] = $this->web_address.$folder.'/'.$file_name_save;
                    }else{
                        $results[] = "";
                    }
                }
            }}
            $this->close();
            if($multiple){
                return $results;
            }elseif(!empty($results)){
                return $results[0];
            }
            return '';
        //}else{
        //    return $this->upload_files_no_ftp($post,$o);
        //}
    }
    
    
    public function copyRemoteFile($source, $dest){
        set_time_limit(0);
        
        $tmp_img = copyRemoteFile($source, Yii::getAlias('@app/runtime/tmp'));
        
        if(!$tmp_img) return $source;
        
        $this->getLogin();	
        
        $f = explode('/', $source);
        
        $file = pathinfo($source);
        
        $filename = unMark($file['filename']) . '.' . $file['extension'];
        
        $destFile = rtrim($this->root_directory,'/') . "/$dest/" . $filename;
        
        if(!@file_exists(dirname($destFile)) || !@is_dir(dirname($destFile))){
            
            $this->MkDir2(dirname($destFile));
            
        }
        
        $this->put($destFile, $tmp_img);
        
        @unlink($tmp_img);
        
        return rtrim($this->web_address,'/') . "/$dest/" . $filename;
        
        
    }
    
    
}