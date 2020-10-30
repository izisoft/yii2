<?php
namespace izi\web;
class Ftp{
    var $config = [];
    var $host = "localhost";//FTP HOST
    var $port = "21";        //FTP port
    var $user = "anonymous";//FTP user
    var $pass = "";    //FTP password
    var $link_id = "";        //FTP hand
    public $is_login = false;        //is login 
    var $debug = 1;
    var $local_dir = "";    //local path for upload or download
    var $rootdir = "";        //FTP root path of FTP server
    var $dir = "/";            //FTP current path
    var $type = 0;    
	var $root_dir = '/'; 
    public function __construct($config = []){
    	$configs['label'] = dString($config['label']);
    	$configs['host_address'] = dString($config['host_address']);
    	$configs['username'] = dString($config['username']);
    	$configs['password'] = dString($config['password']);
    	$configs['web_address'] = dString($config['web_address']);
    	$configs['root_directory'] = dString($config['root_directory']);    	
    	$configs['host_port'] = $config['host_port'] ;
    	$configs['ssl_mode'] = $config['ssl_mode'] ;
        $this->config = $configs;   
       
    }      
    private function halt($msg,$line=__LINE__){
        echo "Đã xảy ra lỗi: $msg<br/>\n";
        exit();
    }

    private function login(){

		$this->root_dir =$this->config['root_directory'];
  
        if(!$this->link_id){

		 	if(isset($this->config['ssl_mode']) && $this->config['ssl_mode'] == 1){

		 		$this->link_id = @ftp_ssl_connect($this->config['host_address'],$this->config['host_port']) or

		 		$this->link_id = @ftp_ssl_connect("localhost") or

		 		////////////////////////////////////////////////

		 		$this->link_id = @ftp_connect($this->config['host_address'],$this->config['host_port']) or

		 		$this->link_id = @ftp_connect("localhost") or

		 		$this->halt("Không kết nối được server ảnh");

		 	}else{
 
				$this->link_id = @ftp_connect($this->config['host_address'],$this->config['host_port']) or

				$this->link_id = @ftp_connect("localhost") or

				$this->halt("Không kết nối được server ảnh");

		 	}

        }
		
        if(!$this->is_login){
            $this->is_login = @ftp_login($this->link_id, $this->config['username'],  $this->config['password']) 
            or $this->halt("Không kết nối được server");

        }	

    }

    
    public function testConnected(){
    
    	 
    
    	if(!$this->link_id){
    
    		if(isset($this->config['ssl_mode']) && $this->config['ssl_mode'] == 1){
    
    			$this->link_id = @ftp_ssl_connect($this->config['host_address'],$this->config['host_port']) or
    
    			$this->link_id = @ftp_ssl_connect("localhost") or
    
    			////////////////////////////////////////////////
    
    			$this->link_id = @ftp_connect($this->config['host_address'],$this->config['host_port']) or
    
    			$this->link_id = @ftp_connect("localhost") ;
    
    		}else{
    
    			$this->link_id = @ftp_connect($this->config['host_address'],$this->config['host_port']) or
    
    			$this->link_id = @ftp_connect("localhost") ;
    
    		}
    
    	}
    
    	if(!$this->is_login){
    
    		$this->is_login = @ftp_login($this->link_id, $this->config['username'],  $this->config['password']);        	
    
    	}
    	 
    	return $this->is_login;
    
    }


    private function systype(){

        return ftp_systype($this->link_id);

    }

    private function pwd(){

        $this->login();

        $dir = ftp_pwd($this->link_id);

        $this->dir = $dir;

        return $dir;

    }

    private function cdup(){

        $this->login();

        $isok =  ftp_cdup($this->link_id);

        if($isok) $this->dir = $this->pwd();

        return $isok;

    }

    private function cd($dir){

        $this->login();

        $isok = ftp_chdir($this->link_id,$dir);

        if($isok) $this->dir = $dir;

        return $isok;

    }

    private function nlist($dir=""){

        $this->login();

        if(!$dir) $dir = ".";

        $arr_dir = ftp_nlist($this->link_id,$dir);

        return $arr_dir;

    }

    private function rawlist($dir="/"){

        $this->login();

        $arr_dir = ftp_rawlist($this->link_id,$dir);

        return $arr_dir;

    }

    private function mkdir($dir){

        $this->login();

        return @ftp_mkdir($this->link_id,$dir);

    }

    private function file_size($file){

        $this->login();

        $size = ftp_size($this->link_id,$file);

        return $size;

    }

    private function chmod($file,$mode=0666){

        $this->login();		 

        return ftp_chmod($this->link_id,$file,$mode);

    }

    private function chmod_($file,$mode=0666){

        $this->login();		 

        return ftp_chmod($this->link_id,$file,$mode);

    }

    function delete($remote_file){
        $this->login();
        if(!@ftp_delete($this->link_id,$remote_file)){
			return @ftp_rmdir($this->link_id, $remote_file);
		}		
		return false;
    }
	
    private function recursiveDelete( $directory)

	{   $this->login();

		# here we attempt to delete the file/directory

		if( !(@ftp_rmdir($this->link_id, $directory) || @ftp_delete($this->link_id, $directory)) )

		{            

			# if the attempt to delete fails, get the file listing

			$filelist = @ftp_nlist($this->link_id, $directory);

			// var_dump($filelist);exit;

			# loop through the file list and recursively delete the FILE in the list

			foreach($filelist as $file) {            

				$this->recursiveDelete($this->link_id, $file);            

			}

			$this->recursiveDelete($this->link_id, $directory);

		}

	}
 

function removeFile($file){
	if(checkPPermission('ftp_file_manages','del')){
	$this->login();
	$path = $this->config['root_directory'];
	if($path == '') $path = '/';
	if(substr($path,-1) != '/'){
		$path .= '/';
	}
	$path .= __SITE_NAME__;
	
	$file = $path . '/' . get_folder_upload_file($file) .'/'. $file;
	return @ftp_delete ($this->link_id, $file);
	}
	return false;
}


	function ftpDelete($path){

		$this->login();

		if (@ftp_delete ($this->link_id, $path) === false) {

		$children = ftp_nlist ($this->link_id, $path);
		 
		if (is_array($children) && !empty($children)) {

		  //foreach ($children as $p){
			  //$this->ftpDelete ($path . DIRECTORY_SEPARATOR . $p);
			  //$this->ftpDelete ($p);
		  //}
			
			

		}

	

		//@ftp_rmdir ($this->link_id, $path);

	  }

	}



	private function get($local_file,$remote_file,$mode=FTP_BINARY){

        $this->login();

        return ftp_get($this->link_id,$local_file,$remote_file,$mode);

    }

	

    private  function put($remote_file,$local_file,$mode=FTP_BINARY){

		//echo $remote_file.'----'.$local_file; exit();

		if($local_file!=""){

        $this->login();

        return @ftp_put($this->link_id,$remote_file,$local_file,$mode);

		}else{return false;}

    }

	

    private function put_string($remote_file,$data,$mode=FTP_BINARY){

        $this->login();

        $tmp = "/tmp";//ini_get("session.save_path");

        $tmpfile = tempnam($tmp,"tmp_");

        $fp = @fopen($tmpfile,"w+");

        if($fp){

            fwrite($fp,$data);

            fclose($fp);

        }else return 0;

        $isok = $this->put($remote_file,$tmpfile,FTP_BINARY);

        @unlink($tmpfile);

        return $isok;

    }

    function p($msg){

        echo "<pre>";

        print_r($msg);

        echo "</pre>";

    }

 

    private function close(){

        @ftp_quit($this->link_id);

    }

	

    private function MkDir_($path,$mode = 0){ 

	$this->login();

		$dir=explode("/", $path); 		 

		$path=""; 

		$ret = true; 		 

		for ($i=0;$i<count($dir);$i++){ 

		   $path.="/".$dir[$i]; 

		   //echo "$path\n"; 

		   $path = str_replace('//','/',$path);

		   if(!@ftp_chdir($this->link_id,$path)){ 

			 @ftp_chdir($this->link_id,"/"); 

			 if(!@ftp_mkdir($this->link_id,$path)){ 

			  $ret=false; 

			  break; 

			 }

			 if($mode > 0) @ftp_chmod($this->link_id,$mode,$path);

		   } 

		} 

		return $ret; 

	} 	

	

	private function Rename($file,$file1){

        $this->login();

        if(ftp_rename($this->link_id, $file, $file1))

        return $file1;

		return 0;

    }

	

    private function _rename($file,$file1){

        $this->login();

        if(ftp_rename($this->link_id, $file, $file1))

        return $file1;

		return 0;

    }

	

    

    function ftp_rdel ( $path , $level = 0) {

      $this->login();

      if($path == $this->root_dir) exit;

      if($level == 0)      $path = $this->root_dir.DIRECTORY_SEPARATOR.$path;

      //echo $level .'----' . $path . '<br />';

      if($level > 500) exit;

      if (@ftp_delete ($this->link_id, $path) === false) {

        if ($children = @ftp_nlist ($this->link_id, $path)) {

          foreach ($children as $p){

            $f = explode('/',$p);

            if(!in_array($f[count($f)-1] , array('.','..')) )

            $this->ftp_rdel($p, ++$level);

          }

        }

        //echo $path.'<br>';

        $a = @ftp_rmdir ($this->link_id, $path);

      }

      return $a; 

    }

function ftp_directory_exists($ftp, $dir){

    // Get the current working directory
	$this->login();
    $origin = @ftp_pwd($this->link_id);   

    // Attempt to change directory, suppress errors

    if (@ftp_chdir($this->link_id, $dir)){

   		// If the directory exists, set back to origin

   		ftp_chdir($this->link_id, $origin);

   		return true;

   	}
    return false;

}
function list_all_folder_tree($recursive = false, $path = false,$ul = ''){
	$this->login();
	if($path === false){
		$path = $this->config['root_directory'] . DIRECTORY_SEPARATOR . __SITE_NAME__;
	}
	//view($path);
    $buff = ftp_rawlist($this->link_id, $path);
 
    //static $ul = '';
    if(count($buff)>0){
		$ul .= '<ul class="jstree-ul-default" data-parent="'.$path.'">';
        foreach($buff as $result){
			$chunks = preg_split("/\s+/", $result);
			$type = $chunks[0]{0} === 'd' ? 'dir' : 'file';
			$r = $this->parse_rawlist($result);
            // verify if is dir , if not add to the  list of files
            if( $type == 'dir'){
                // recursively call the function if this file is a folder
				$ul .= '<li data-id="'.$path.'/'.$r['zfile_name'].'" data-parent="'.$path.'"><a href="#">'.$r['zfile_name'].'</a>';
                if($recursive){$ul = $this->list_all_folder_tree($recursive,$path.'/'.$r['zfile_name'],$ul);}
				$ul .= '</li>';
				
            }
            else{
            	//$ul .= '<li data-id="'.$path.'" data-parent="'.$path.'"><a href="#">'.$r['zfile_name'].'</a>';
            	 
            	//$ul .= '</li>';
            }     
        }
		$ul .= '</ul>';
    }
	//view($ul);
    return $ul;
}
function list_all_files($recursive = false, $path = false){
	$this->login();
	if($path == false){
		$path = $this->config['root_directory'] . DIRECTORY_SEPARATOR . __SITE_NAME__;
	}
    $buff = ftp_rawlist($this->link_id, $path);
     
//     if(1){
//         view($buff, $path);
//     }
    
	$find = 'public_html';
	$pos = strpos($path,'public_html');
	if(substr($path,0,1) == '/'){
		$path = substr($path, 1);
	}
	if($pos !== false){
		$t_path = substr($path,$pos+strlen($find));		
	}else{
		$t_path = $path;
	}
	//view(substr($path,0,1));
	if(substr($t_path,0,1) != '/'){
		 
		$t_path = '/' . $t_path;
	} 
	//$t_path = . ($t_path != "" ? $t_path : '');
	 
	///view($t_path);
    static $flist = [];
    if(count($buff)>0){
        
        $buff2 = [];
        
        foreach($buff as $result){
            $chunks = preg_split("/\s+/", $result);
            $buff2[] = $this->parse_rawlist($result);
            
        }
        
        array_sort($buff2, 'file_time', SORT_DESC);
        
        //if($path == '/ecom01/images') view($buff2,1,1);
        
        foreach($buff2 as $r){
			//$chunks = preg_split("/\s+/", $result);
			//$type = $chunks[0]{0} === 'd' ? 'dir' : 'file';
			//view($result);
			//$r = $this->parse_rawlist($result);
			 
			
            // verify if is dir , if not add to the  list of files
            if( $r['type'] == 'dir'){
                // recursively call the function if this file is a folder
                $flist[] = array('type'=>$r['type'], 'path'=>$path, 'name'=>$r['zfile_name']);
                if($recursive) $this->list_all_files($recursive,$path.'/'.$r['zfile_name']);
            }
            else{
            // this is a file, add to final list
            $folder_upload = get_folder_upload_file($r['zfile_name']);
            //view($folder_upload);
            
            $pos = strpos($t_path,'/',strlen(__SITE_NAME__));
			$_p = $pos !== false ? substr($t_path,$pos+1) :$t_path;
			if(substr($_p,0,strlen($folder_upload)) == $folder_upload){
				if(!($_p = substr($_p,strlen($folder_upload)+1))) $_p = '';
				 
			}
			
			
            $r['tpath'] = $_p;
            $r['file_name'] = $r['tpath'] != "" ? $r['tpath'] . DIRECTORY_SEPARATOR . $r['zfile_name'] : $r['zfile_name'];
			//$r['type'] = $type;
			$r['path'] = $path; 
			$r['link'] = $this->config['web_address'] . $t_path . '/' . $r['zfile_name'];
			//$ftime = @ftp_mdtm($this->link_id, $path . '/' . $r['zfile_name']);
			//$r['file_time'] = $ftime;
                $flist[] = $r;//array('type'=>$type, 'path'=>$path, 'name'=>$r['zfile_name']);
            }
            
            if(count($flist) > 2500) {
                array_sort($flist, 'file_time', SORT_DESC);
                return $flist;
                break;
            }
        }
    }
    return $flist;
}
function parse_rawlist( $curraw ) 
{ 
 
    $i=0; 
    //foreach($array as $curraw) 
    //{ 
        $struc = []; 
        $current = preg_split("/[\s]+/",$curraw,9);   

        $struc['perms']      =     $current[0]; 
        $struc['number']     =     $current[1]; 
        $struc['owner']     =     $current[2]; 
        $struc['group']      =     $current[3]; 
        $struc['size']         =     $current[4]; 
        $struc['month']      =     $current[5]; 
        $struc['day']        =     $current[6]; 
        $struc['time']      =     $current[7]; 
        $struc['year']      =     $current[7]; 
        $struc['zfile_name']      =     $current[8]; 
        $struc['file_time']      =     strtotime($struc['day'] . ' ' . $struc['month'] . $struc['time']); 
        $struc['type']      =   $current[0]{0} === 'd' ? 'dir' : 'file';
		return $struc;
    $structure[$i]      =     $struc; 
    $i++; 
    //} 
    return $structure; 

} 
 
function nfileupload($file_source,$file_dest){
	$this->login();	
	
	$file_dest = $this->config['root_directory'] . $file_dest;
	$path = dirname($file_dest);
	if (substr($path,-1) != '/'){
		$path = $path . '/';
	}
	//if(!$this->ftp_directory_exists($this->link_id,$path)){
		$this->MkDir_($path);	
	//}
	//$folder = get_folder_upload_file($filename);
	//if(!$this->ftp_directory_exists($this->link_id,$folder)){
	//	$this->MkDir_($folder);
	//}
	$this->put($file_dest, $file_source);
	 
	$this->close(); 
}
 
function upload_file_ckeditor($path,$filename,$file){
	$this->login();
	if (substr($path,-1) != '/'){
		$path = $path . '/';
	}
	
	if(!$this->ftp_directory_exists($this->link_id,$path)){
		$this->MkDir_($path);
	}
	$folder = get_folder_upload_file($filename);
	//var_dump($path); exit;
	if(!$this->ftp_directory_exists($this->link_id,$path . $folder)){
		$this->MkDir_($path . $folder);
	}
	//
	//var_dump($path. $folder . '/' . $filename); exit;
	//
	$this->put($path. $folder . '/' . $filename, $file);
	//var_dump($path . '/' .$filename); exit;
	$this->close();
}
function upload_files($post,$o = []){
	@date_default_timezone_set('Asia/Ho_Chi_Minh');@set_time_limit(0);
	if(1 || defined('USED_HOST_MEDIA') && USED_HOST_MEDIA){
	$this->login();	$time = time();	
	$year = date("Y",$time); $month = date("m",$time);	
	$rename = isset($o['rename']) && $o['rename'] == true ? true : false;
	$replace = isset($o['replace']) && $o['replace'] == true ? true : false;
	$multiple = isset($o['multiple']) && $o['multiple'] == true ? true : false;
	$extension = isset($o['type']) ? $o['type'] : 'files';
	$extension_block = array('php','html','js','c','py','json');	
	$file_extensions = file_extension_upload($extension);
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
				
				
				if(!$this->ftp_directory_exists($this->link_id, $this->config['root_directory'].$folder)){
					$this->MkDir_($this->config['root_directory'].$folder);
				}
				if(!$rename){
					$file_name_save = unMark(substr($filename_img, 0,$pos)). '.' .$file_type;
				}else {
					$file_name_save = md5(unMark(substr($filename_img, 0,$pos)).$time) . '.' .$file_type;
				}	
				$remote_file = $this->config['root_directory'].$folder.'/'.$file_name_save;
				if(!$replace){
					$counter = 0;
					while(@ftp_size($this->link_id, $remote_file) > 0){
						$file_name_save = unMark(substr($filename_img, 0,$pos)).'-'.(++$counter). '.' .$file_type;
						$remote_file = $this->config['root_directory'].$folder.'/'.$file_name_save;
					}		
				}	
				if($this->put($remote_file, $filetemp_img) !== false){
					$results[] = $this->config['web_address'].$folder.'/'.$file_name_save;
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
	}else{
		return $this->upload_files_no_ftp($post,$o);
	}
}
function upload_files_no_ftp($post,$o = []){
	$time = time();	
	$year = date("Y",$time); $month = date("m",$time);	
	$rename = isset($o['rename']) && $o['rename'] == false ? false : true;
	$replace = isset($o['replace']) && $o['replace'] == true ? true : false;
	$multiple = isset($o['multiple']) && $o['multiple'] == true ? true : false;
	$extension = isset($o['type']) ? $o['type'] : 'files';
	$extension_block = array('php','html','js','c','py','json');	
	$file_extensions = file_extension_upload($extension);
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
				$folder = '/medias/'.__SITE_NAME__.'/'.$folder_save;	$folder = str_replace('//','/',$folder);
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
					$results[] = SITE_ADDRESS . $folder . '/'.$file_name_save;
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
function check_file_existed($remote_file){
	$this->login(); 
	return @ftp_size($this->link_id, $remote_file) > 0 ? true : false;
}

function get_file_size($remote_file){

	$this->login();

	//var_dump(md5_file($remote_file));

	return @ftp_size($this->link_id, $remote_file) ;

}

function uploadFile($post,$fd = __SITE_NAME__,$type = 'images'){

	set_time_limit(0);

	$this->login();

	$time=time();

	date_default_timezone_set('Asia/Ho_Chi_Minh');

	$year = date("Y",$time);

	$month = date("m",$time);

	$str_rand = md5($post['tmp_name'] . $time);

	$folder = '/'.$fd.'/'.$type.'/'.$year.'/'.$month;

    $folder = str_replace('//','',$folder);

		$filename_img = $post['name'];

		$filesize_img = $post['size'];

		$filetype_img = $post['type'];

		$filetemp_img = $post['tmp_name'];

	if(!$this->ftp_directory_exists($this->link_id,$this->config['root_directory'].$folder)){

		$this->MkDir_($this->config['root_directory'].$folder);

	}	
	$ftype = explode('.',$filename_img);	
	$ftype = $ftype[count($ftype)-1];			
	$this->put($this->config['root_directory'].$folder.'/'.$str_rand.".".$ftype, $filetemp_img);
	if($filename_img!=""){
		return $this->config['web_address'].$folder.'/'.$str_rand.".".$ftype;
	}else{return "";}	
	$this->close();
}



function uploadZip($fd=false,$post){

	set_time_limit(0);

	if($fd == false) $fd = 'public';

	$alow_type = array('zip','7z','gz','doc','docx','pdf','xls','xlsx');

	$this->login();

	$dm = $this->get_host();

	$folder = '/'.$fd ;	 

	$filename_img = $post['name'];

	$filesize_img = $post['size'];

	$filetype_img = $post['type'];

	$filetemp_img = $post['tmp_name'];

	//////////////////////////////////////

	$ftype = explode('.',$filename_img);	

	$ftype = $ftype[count($ftype)-1];

	if(!in_array($ftype,$alow_type)){ 

		return false;	

	}

	//////////////////////////////////////

	if(!file_exists($dm->physical_add.$folder) || !is_dir($dm->physical_add.$folder)){

		$this->MkDir_($dm->physical_add.$folder,0755);

	}

	$this->put($dm->physical_add.$folder.'/'.$filename_img, $filetemp_img);

	if($filename_img!=""){

		return  $filename_img;

	}else{return "";}

	$this->close();
	
}

////////////////////

function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')

{

    // Length of character list

    $chars_length = (strlen($chars) - 1);



    // Start our string

    $string = $chars{rand(0, $chars_length)};

    

    // Generate random string

    for ($i = 1; $i < $length; $i = strlen($string))

    {

        // Grab a random character from our list

        $r = $chars{rand(0, $chars_length)};

        

        // Make sure the same two characters don't appear next to each other

        if ($r != $string{$i - 1}) $string .=  $r;

    }

    

    // Return the string

    return $string;

}



function multiUpload($fd=false,$post,$allow = array('jpg','png','gif','jpeg','ico','bmp')){

	set_time_limit(0);

	$this->login();

	$dm = $this->get_host();

	//$time = time();	 

	$year = date("Y",time());

///	$month = date("m",$time);

 	$result = [];

	if(!empty($post) && count($post['name'])>0)	{	

	 

	foreach($post['name'] as $k=>$v){

		if($post['error'][$k]>0){ exit;break;return;}		

		$filename_img = $post['name'][$k];

		$filesize_img = $post['size'][$k];

		$filetype_img = $post['type'][$k];

		$filetemp_img = $post['tmp_name'][$k];	

		

		/// check allow filetype

		$ftype = explode('.',$filename_img);			 

		$ftype = strtolower($ftype[count($ftype)-1]);

		if(!in_array($ftype,$allow)){			

			$result[$k]['status'] = 2;

			$result[$k]['error'] = 1;

		}else{

		 

			switch($filetype_img){

				case 'image/jpeg': 

				case 'image/jpg': 

				case 'image/png': 

				case 'image/gif': 

				case 'image/bmp': 

				case 'image/x-icon':$type = "images"; break;			

				default: $type = "files";  break;

				

			}

			$folder = str_replace('//','/','/'.$fd.'/'.$type.'/'.$year);

			 

			////////////////////

  

			$filename_save =  md5(time().$filename_img).".".$ftype;		

			if(!@file_exists($dm->physical_add.$folder) || !@is_dir($dm->physical_add.$folder)){

					$this->MkDir_($dm->physical_add.$folder);

			}

			if(file_exists($dm->physical_add.$folder.'/'.$filename_save)){

				if(@hash_file('md5',$dm->physical_add.$folder.'/'.$filename_save) != @hash_file($filetemp_img)){

					$filename_save = md5(time().$this->rand_str().$filename_save).'.'.$ftype;

				} 

			}

			$u = $this->put($dm->physical_add.$folder.'/'.$filename_save, $filetemp_img);

			$result[$k]['status'] = $u ? 1 : 0;

			$result[$k]['error'] = $u ? 0 : 1 ;

			$result[$k]['image'] = $dm->site_add.$folder.'/'.$filename_save;

	 

		}

		

	}

	 

	$this->close();

	//view($result); exit;

	return $result;	exit;

	}

	return false;	exit;

}




 
 
function ftp_putAll($src_dir, $dst_dir) {

	//if(!isset($_ftp)) $_ftp = new ClsFTP;

	

	$this->login();

	$m = $this->MkDir_($dst_dir);

	//var_dump($m);

    $d = dir($src_dir);

 //echo $dst_dir;

    while($file = $d->read()) { // do this for each file in the directory

	//var_dump($file);	 

        if ($file != "." && $file != "..") { // to prevent an infinite loop

            if (is_dir($src_dir."/".$file)) { // do the following if it is a directory

                //if (!@ftp_chdir($this->link_id, $dst_dir."/".$file)) {

					 

                    $this->MkDir_($dst_dir."/".$file); // create directories that do not yet exist

               // }else{echo 's';}

                $this->ftp_putAll( $src_dir."/".$file, $dst_dir."/".$file); // recursive part

            } else { 

			//echo $dst_dir."/".$file;

                $upload = $this->put($dst_dir."/".$file, $src_dir."/".$file, FTP_BINARY); // put the files

				 

            }

        } 

    }

    $d->close();

	return true;

}





    function copy_all($path, $dest)

    {

		$this->login();

		//$this->get_host();

		//$path = $this->root_dir.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR;

		//var_dump(is_dir($path));

		//echo $path;

		//$dest = $this->root_dir.DIRECTORY_SEPARATOR.$dest;

        if( is_dir($path)){

			//echo $path;

			//exit;

			$this->MkDir_($dest) ;        

			$this->chmod($dest,0777);						

            $objects = scandir($path);

            if( sizeof($objects) > 0 )

            {

                foreach( $objects as $file )

                {

                    if( $file == "." || $file == ".." )

                        continue;

                    // go on

                    if( is_dir( $path.DIRECTORY_SEPARATOR.$file ) )

                    {

                        $this->copy_all( $path.DIRECTORY_SEPARATOR.$file, $dest.DIRECTORY_SEPARATOR.$file);

                    }

                    else

                    {

                        copy( $path.DIRECTORY_SEPARATOR.$file, $dest.DIRECTORY_SEPARATOR.$file );

                    }

                }

            }

            return true;

        }

        elseif(is_file($path) )

        {

            return copy($path, $dest);

        }

        else

        {

            return false;

        }

    }

	

    public function copyRemoteFile($source, $dest){
        set_time_limit(0);
        
        $this->login();
        
        $f = explode('/', $source);
        
        $destFile = rtrim($this->config['root_directory'],'/') . "/$dest/" . ltrim($f[count($f)-1], '/');
         
        if(!@file_exists(dirname($destFile)) || !@is_dir(dirname($destFile))){
            
            $this->MkDir_(dirname($destFile));
            
        }
                
        $this->put($destFile, $source);
         
        
        return rtrim($this->config['web_address'],'/') . "/$dest/" . ltrim($f[count($f)-1], '/');
        
        
    }
    
}