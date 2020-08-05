<?php
namespace izi\filemanager;
use Yii;

class FileManager extends \yii\base\Component
{
         
    
    private $_driver;
    
    public function getDriver(){
        if($this->_driver == null){
            if(Yii::$app->settings['ftp_media'] == 1){
                /**
                 * 
                 */
                $cfg = (new yii\db\Query)->from(['{{%server_config}}'])
                ->where(['is_active'=>1,'sid'=>__SID__])
                ->andWhere(['>','state',-2])
                ->one();
                
                if(empty($cfg)){
                    $cfg = (new yii\db\Query)->from(['{{%server_config}}'])
                    ->where(['is_active'=>1,'sid'=>0])
                    ->andWhere(['>','state',-2])
                    ->one();
                }
                
                $configs['label'] = dString($cfg['label']);
                $configs['host_address'] = dString($cfg['host_address']);
                $configs['username'] = dString($cfg['username']);
                $configs['password'] = dString($cfg['password']);
                $configs['web_address'] = dString($cfg['web_address']);
                $configs['root_directory'] = dString($cfg['root_directory']);
                $configs['host_port'] = $cfg['host_port'] ;
                $configs['ssl_mode'] = $cfg['ssl_mode'] ;
                $configs['class'] = 'izi\filemanager\Ftp';
                $this->_driver = Yii::createObject($configs);
            }else{
                $this->_driver = Yii::createObject('izi\filemanager\Local');
            }
        }
        
        return $this->_driver;
    }
    
    public function file_extension_upload($type = 'files') {
        switch ($type){
            case 'image':case 'images':
                $file_extensions = array('jpeg', 'png', 'gif','jpg','ico','bmp','svg');
                break;
            case 'doc':case 'docs':case 'document':case 'documents':
                $file_extensions = array('xls', 'xlsx', 'doc','docx','dot','txt','pdf','ppt','pptx');
                break;
            case 'video':case 'videos':
                $file_extensions = array('mp4', '3gp', '3gpp2', 'avi','flv');
                break;
            case 'audio':
                $file_extensions = array('3gpp', 'mp3', 'acc', 'ac3', 'amr', 'm4a', 'wma', 'wav',);
                break;
            case 'text':
                $file_extensions = array('txt','srt','ass','sub');
            default:
                $file_extensions = array(
                'txt','srt','ass','sub','zip','rar','7z','tar','gz',
                'jpeg', 'png', 'gif','jpg','ico','bmp','svg',
                'xls', 'xlsx', 'doc','docx','dot','pdf','ppt','pptx',
                'mp4', '3gp', '3gpp2', 'avi',
                '3gpp', 'mp3', 'acc', 'ac3', 'amr', 'm4a', 'wma', 'wav',
                
                );
                break;
        }
        return $file_extensions;
    }
    
    public function get_folder_upload_file($file_name = ''){
        $folder = 'files';
        $pos = strrpos($file_name,'.',1);
        $file_type = $pos > 0 ? substr($file_name,$pos+1) : '';
        switch (strtolower($file_type)){
            case 'jpeg':case 'png':case 'gif':case 'jpg':case 'ico':case 'bmp':case 'svg':
                $folder = 'images';
                break;
                //case 'jpeg':case 'png':case 'gif':case 'jpg':case 'ico':case 'bmp':case 'svg':
                //	$folder = 'images';
                //	break;
            case 'mp4':case '3gp':case '3gpp2':case 'avi':case 'flv':
                $folder = 'videos';
                break;
        }
        return $folder;
    }
    
    /*
     * 
     * 
     */
    public function upload_files($files, $params = []){
        return $this->uploadFiles($files, $params);
    }
    
    public function uploadFiles($files, $params = []){
        return $this->getDriver()->uploadFiles($files, $params);
    }
}