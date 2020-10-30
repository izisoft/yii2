<?php 
namespace izi\web;

class Application extends \yii\web\Application

{
    /**
     * 
     * Old value 
     */
    public $slug, $settings, $config, $category, $currency;
    
    /**
     * access controller without check authorization
     * @var array
     */
    public $allowController = [
        'index',
        'default',
        'ajax',
        'sajax',
        'login',
        'logout',
        'error',
        'forgot'
    ];
    
    /**
     * access action without check authorization
     * @var array
     */
    
    public $allowAction = [];
    
    
    
    public function bootstrap(){
        \Yii::setAlias('@izi', dirname(__DIR__));
        
        return parent::bootstrap();
        
    }
    
    public function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            
            'cfg'   =>  [
                'class' =>  'izi\config\Config'
            ],
            
            'icache'	            =>	['class'	=>	'izi\\cache\\Cache'],
            
            'api'   =>  [
                'class' =>  'izi\api\Api'
            ],
            
            'product'   =>  [
                'class' =>  'izi\product\Product'
            ],
            
            'vote'   =>  [
                'class' =>  'izi\vote\Vote'
            ],
            
            'image'   =>  [
                'class' =>  'izi\image\Image'
            ],
            
            'statics'   =>  [
                'class' =>  'izi\statics\Statics'
            ],
            
            'service'   =>  [
                'class' =>  'izi\web\Service'
            ],
            
            'dbs' => [
                'class' => 'yii\db\Connection',
                'dsn' => 'sqlite:' . APP_PATH . '/runtime/sqlite/sqlite.db',
//                 'username' => 'root',
//                 'password' => '',
                'charset' => 'utf8',
            ],
            
            'filter'   =>  [
                'class' =>  'izi\filters\Filter',
                
            ],
            'menu'   =>  [
                'class' =>  'izi\menu\Menu',
                
            ],
            
            'cart'   =>  [
                'class' =>  'izi\pos\Cart',
                
            ],
            
            'order'   =>  [
                'class' =>  'izi\pos\Order',
                
            ],
            
            'view'	    =>	[
                'class'	=>	'izi\web\View',
            ],

            'store'	    =>	[
                'class'	=>	'izi\web\Store',
            ],
            
            // 'pos'   =>  [
            //     'class' =>  'izi\pos\Pos',
                
            // ],
            
            'request'   =>  [
                'class' =>  'izi\web\Request'
            ],
            
            'l'	                    =>	['class'	=>	'izi\\language\\Language'],
            'c'	                    =>	['class'	=>	'izi\\currencies\\Currencies'],
            
            // 'import'   =>  [
            //     'class' =>  'izi\import\Import'
            // ],
           
            // Frontend component
            'frontend'	=>	['class'	=>	'izi\\frontend\\Frontend'],
            'f'	=>	['class'	=>	'izi\\frontend\\Frontend'],
            'backend'	=>	[
                'class'	=>	'izi\backend\Backend'
            ],
            
            // Customer manager
            'customer' => [
                'identityClass' => 'izi\user\models\Customer',
                'class'	=>	'izi\user\Customer',
                'enableAutoLogin' => true,
                'identityCookie' => ['name' => '_identity-customer-frontend', 'httpOnly' => true],
                
            ],
            
            // Member manager (site member - extends customer)
            'member' => [
                'identityClass' => 'izi\user\models\Member',
//                 'class'	=>	'izi\web\Member',
                'class'	=>	'izi\user\Member',
                'enableAutoLogin' => true,
                'identityCookie' => ['name' => '_identity-member-frontend', 'httpOnly' => true],
                
            ],

            // Cooperator
//             'collaborator' => [
//                 'identityClass' => 'izi\collaborator\models\Member',
// //                 'class'	=>	'izi\web\Member',
//                 'class'	=>	'izi\collaborator\Member',
//                 'enableAutoLogin' => true,
//                 'identityCookie' => ['name' => '_identity-collaborator-frontend', 'httpOnly' => true],
                
//             ],
            
            // User manager (staff only)
            'user' => [
                
                'identityClass' => 'izi\user\models\User', 
                'class'	=>	'izi\user\User',
                'enableAutoLogin' => true,
                'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
                
            ],
            
            // SLINK
            'slink'	=>	[
                'class'	=> 'izi\slink\ShortLink'
            ],
            
            // SIM
            // 'sim'	=>	[
            //     'class'	=> 'izi\sim\Simonline'
            // ],
            
            // TRanslate site multilang 
            't'	=>	[
                'class'	=> 'izi\web\Translate'
            ],
            
            // Box manager (special page on frontend page)
            'box'	=>	[
                'class'	=>	'izi\web\Box'
            ],
            /**
             * Add old components & remove 
             */
            //'zii'	=>	['class'	=>	'izi\web\Zii'],
            'izi'	=>	['class'	=>	'izi\web\Izi'],
            //'template'	=>	['class'	=>	'izi\web\Template'],
			'template'	=>	['class'	=>	'izi\template\Template'],
            
            /**
             * end old component
             */
            
            // Ads (google, bing, ...)
            'ads'	=> ['class'	=> 'izi\ads\Ads'],
            
            // Cronjob manager
            'cronjob'	=> ['class'	=> 'izi\cronjob\Cronjob'],
            
            // GeoIP
            'geoip' => [
                'class' => 'izi\geoip\components\CGeoIP',
                //'class' => 'dpodium\yii2\geoip\components\CGeoIP',
                //'filename' => dirname(__DIR__) . '/components/GeoIP/GeoIP.dat', // specify filename location for the corresponding database
                //'mode' => 'STANDARD',  // Choose MEMORY_CACHE or STANDARD mode
            ],
            
            // Place manager
            'place'	=>	[
                'class'	=>	'izi\local\Place'
            ],
            
            // Local manager (Country, province, distrist, ...)
            'local'	=>	[
                'class'	=>	'izi\local\Local'
            ],
            
            'note'	=>	[
                'class'	=>	'izi\note\Note'
            ],
            
            'file' => ['class' => 'izi\filemanager\FileManager'],
            'backup' => ['class' => 'izi\backup\Driver'],
//             'cart' => ['class' => 'izi\web\Cart'],
            'ftp' => ['class' => 'izi\ftp\FtpConnection'],
            'notify' => ['class' => 'izi\notify\Notify'],
            
            'log2'	=>	[
                'class'	=>	'izi\log\Log'
            ],
            
            'security'	=> ['class'	=> 'izi\base\Security'],
            'calendar'    =>  ['class'    =>  'izi\web\Calendar'],
            'mailer'    =>  ['class'    =>  'izi\mailer\Mailer'],
            
            'purifier'    =>  ['class'    =>  'HTMLPurifier_HTMLPurifier'],
            
            'authManager'=>[
                'class'=>'izi\rbac\DbManager'
            ],
            
            'satellite'   =>  [
                'class' =>  'izi\satellite\Satellite'
            ],
            /**
             * Promotion
             */
            
            // 'promotion'=>[
            //     'class'=>'izi\promotion\Promotion'
            // ],
            
            /**
             * INTRANET
             */
            // 'tour'=>[
            //     'class'=>'izi\tour\Tour'
            // ],
            
            /**
             * end intranet
             */
            
        ]);
    }
    
    
    private $_device;
    
    public function setDevice($value){
        $this->_device = $value;
    }
    
    public function getDevice(){
        
        if($this->_device == null){
        
//             // Get device
//             if(isset($config['set_device']) && in_array($config['set_device'],['mobile','desktop'])){
//                 $this->device=$config['device']=$config['set_device'];
//                 $t = false;
//             }else{
//                 $t = true;
//             }
            
//             //
//             if($t || !isset($config['device'])){
//                 $useragent=$_SERVER['HTTP_USER_AGENT'];
                
//                 if(preg_match('/(android|bb\d+|meego).+mobile|(android \d+)|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)
//                     ||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
//                         $this->device = 'mobile';
// //                         $this->is_mobile = true;
//                 }
//                 $config['device'] = $this->device;
//             }else{
//                 $this->device = $config['device'];
//             }
//             $this->session->set('config', $config);
        }
        return $this->_device;
    }
    
    
    public function getBrowser()
    {
        $u_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
        $bname = $ub = 'Unknown';
        $platform = 'Unknown';
        $version= "";
        
        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }
        
        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif(preg_match('/Firefox/i',$u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif(preg_match('/Chrome/i',$u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif(preg_match('/Safari/i',$u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }
        elseif(preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
        }
        elseif(preg_match('/Netscape/i',$u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }
        
        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }
        
        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= isset($matches['version'][1]) ? $matches['version'][1] : '';
            }
        }
        else {
            $version= $matches['version'][0];
        }
        
        // check if we have a number
        if ($version==null || $version=="") {$version="?";}
        $version = str_replace('.', '_',  $version);
        $pos = strpos($version,'_');
        $v = $pos !== false ? substr($version,0, $pos) : $version;
        return array(
            //'userAgent' => $u_agent,
            'name'      => strtolower($bname),
            'short_name'=> strtolower($ub),
            'browser' => strtolower($ub),
            'full_version'   => $version,
            'version'   => $v,
            'platform'  => $platform, // window - linux - ios - android
            'platform_version'  => $platform ,// win10, win8 ...,
            'device_type' => '', // Desktop or Mobile
            'device_pointing_method' => '', // Touch or mouse
            
            
            
        );
    }
    
    
    
    public function getAdminVersion(){
        return 'v1';
    }
    
    
    /**
     * Old function
     */
    public function getConfigs($key,$lang = __LANG__,$sid = __SID__,$cache = false, $requiredSid = false){
        return \app\models\SiteConfigs::getConfigs($key,$lang,$sid,$cache,$requiredSid);
    }
    
    public function addAllowAction($action){
        if(!is_array($action)) $action = [$action];
        $this->allowAction += $action;
    }
    public function checkAppExisted($code){
        if( (new \yii\db\Query())->from(['a'=>'apps'])
            ->innerJoin(['b'=>'apps_to_shop'],'a.id=b.app_id')
            ->where([
                'b.shop_id'=>__SID__,
                'a.is_active'=>1,
                'b.status'=>10,
                'a.code'=>$code
            ])->count(1) > 0
            ){
                return true;
            }
            return false;
    }
    
    
    public function hasApp($code){
        return isset($this->getComponents()[$code]);
    }
    public function setUserApp(){
        $l = (new \yii\db\Query())
        ->select(['a.*'])
        ->from(['a'=>'apps'])
        ->innerJoin(['b'=>'apps_to_shop'],'a.id=b.app_id')
        ->where([
            'b.shop_id'=>__SID__,
            'a.is_active'=>1,
            'b.status'=>10
        ])->all();
        if(!empty($l)){
            foreach ($l as $v){
                $app_id = $v['app_id'] != "" ? $v['app_id'] : $v['code'];
                $class = isset($v['component']['class']) ? $v['component']['class'] : null;
                if($class != null && class_exists($class)){
                    $this->set($app_id,$v['component']);
                }else{
                    $class_name = 'app\apps\\' .$v['code'] . '\\' . $v['code'];
                    if(class_exists($class_name)){
                        $this->set($v['code'],[
                            'class' => $class_name
                        ]);
                    }
                }
            }
        }
        
        
        
    }
    
    
    /**
     * OLD VERSION
     */
    public function getTextRespon($o = []){
        return $this->izi->getTextRespon($o);
    }
    
    public function getVersions()
    {
        return \app\models\SiteConfigs::getConfigs('VERSION',false,0);
    }
}
