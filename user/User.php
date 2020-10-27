<?php
/**
 *
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\user;
use Yii;

use izi\user\models\User as UserModel;

class User extends \yii\web\User
{
    /**
     *
     * {@inheritDoc}
     * @see \yii\web\User::init()
     */
    
    public function init()
    {
        
        // Defind permission
        foreach ($this->_specialPermission as $permission => $val) {
            
            $auth = "AUTH_" . strtoupper($permission);
            
            defined($auth) || define($auth, $permission);
        }
        defined('ROOT_USER') || define('ROOT_USER', AUTH_ROOT);
        defined('ADMIN_USER') || define('ADMIN_USER', 'admin');
        defined('DEV_USER') || define('DEV_USER', 'dev');
        
        $this->loginUrl = [
            (
                defined('__DOMAIN_MODULE__') && __DOMAIN_MODULE__ ? '' :
                (defined('__MODULE_NAME__') ?
                    __MODULE_NAME__ : '')) . '/login'
            
        ];
         
        parent::init();
        
    }
    
	/**
	 *
	 */
	//public $identityClass;
	private $_access = [];
	
	private $_permission;
	
	public function getPermission(){
	    if($this->_permission == null){
	        $this->_permission = Yii::createObject('izi\web\Permission');
	    }
	    return $this->_permission;
	}
	
	
	private $_authority;
	
	public function getAuthority(){
	    if($this->_authority == null){
	        $this->_authority = Yii::createObject(
	            [
	            'class' =>  'izi\user\Authority',
	            'permission'=>$this->_specialPermission
	             
	            ]
	            
// 	            ['user'=>$this] 
	             
	            );
	    }
	    return $this->_authority;
	}
	
	
	
	
	
	
	
	
	
	private $_specialPermission = [
	    'root' => [
	        'title' => 'Người có quyền cao nhất hệ thống',
	        'level' => 0
	    ],
	    'admin' => [
	        'title' => 'Quản trị hệ thống',
	        'level' => 1
	    ],
	    'director' => [
	        'title' => 'Ban giám đốc',
	        'level' => 2
	    ],
	    'manager' => [
	        'title' => 'Quản lý',
	        'level' => 3
	    ],
	    'leader' => [
	        'title' => 'Leader',
	        'level' => 4
	    ],
	    'sale' => [
	        'title' => 'Kinh doanh',
	        'level' => 5
	    ],
	    'accounting' => [
	        'title' => 'Kế toán',
	        'level' => 5
	    ],
	    'operator' => [
	        'title' => 'Điều hành',
	        'level' => 5
	    ],
	    'technical'   =>  [
	        'title' => 'Kỹ thuật',
	        'level' => 5
	    ],
	    'seo'   =>  [
	        'title' => 'Seo web',
	        'level' => 5
	    ],
	    'web'   =>  [
	        'title' => 'Quản trị web',
	        'level' => 5
	    ],
	    'inspector'   =>  [
	        'title' => 'Kiểm duyệt',
	        'level' => 5
	    ],
	    
	    'tester'   =>  [
	        'title' => 'Thử nghiệm',
	        'level' => 5
	    ],
	];
	
	/**
	 * Check permission
	 * {@inheritDoc}
	 * @see \yii\web\User::can()
	 */
	public function can($permissionName, $params = [], $allowCaching = true)
	{
	    if($this->getLoggedUserName() == ROOT_USER){
	        return true;
	    }
		$p = $permissionName; $access = false;
		if(!is_array($p)){$p = array($permissionName);}
		$type = 'OR';
		if(strtoupper($p[0]) == 'AND'){
			$type = 'AND';
			unset($p[0]);
		}

		foreach ($p as $permissionName){

			if ($allowCaching && empty($params) && isset($this->_access[$permissionName])) {
				$access = $this->_access[$permissionName];
				if($type == 'OR' && $access){
					break;
				}elseif($type == 'AND' && !$access) {
					return false;
					break;
				}
			}
			if (($accessChecker = $this->getAccessChecker()) === null) {
				$access = false; break;
			}
						
			
			$access = $accessChecker->checkAccess($this->getId(), $permissionName, $params);
			if ($allowCaching && empty($params)) {
				$this->_access[$permissionName] = $access;
			}
			if($access === true) return $access;
		}

		return $access;
	}
	public function getPasswordHash()
  {
      $identity = $this->getIdentity();

      return $identity !== null ? $identity->getPasswordHash() : null;
  }
	public function validatePassword($password)
  {
	 return Yii::$app->security->validatePassword($password, $this->getPasswordHash());
  }
 
	
	public function getUserName($id){
	    $u = $this->getUser($id); 
	    if($u['name'] != ""){
	        return $u['name'];
	    }
	    return $u['email'];
	}

	private $_loggedUserName;
	public function getLoggedUserName(){
	    if($this->_loggedUserName == null && !Yii::$app->user->isGuest){
	       $u = $this->getIdentity();
	       $this->_loggedUserName = $u->username;
	    }
	     
	    return $this->_loggedUserName;
	}
	
	
	
	public function getName(){
		$u = $this->getIdentity();
		if($u->name != ""){
			return $u->name;
		}
		if($u->fname != ""){
			return $u->lname .' ' . $u->fname;
		}
		return $u->email;
	}

	public function getBranch_Id(){
		$u = $this->getIdentity();
		if($u->branch_id == 0){
			$branch = \app\modules\admin\v1\models\Branches::getDefaultItem();
			if(!empty($branch)){
				$u->branch_id = $branch['id'];
			}
		}
		return $u->branch_id;
	}

	public function getBranch(){
		return \app\modules\admin\v1\models\Branches::getItem($this->getBranch_Id());
	}

	public function getCurrentUser(){
		return \app\modules\admin\v1\models\Users::getItem($this->getId());
	}

	public function getAllUserGroup(){
		return (new \yii\db\Query())->from(['a'=>'user_groups'])
		->innerJoin(['b'=>'user_to_group'],'a.id=b.group_id')
		->where([
				'b.user_id'=>$this->getId()
		])->all();
	}

	public function getUser($user_id){
	    return (new \yii\db\Query())->from(['a'=>'users'])
	    ->where([
	        'a.id'=>$user_id
	    ])->one();
	}

	public function getNameByUser($user_id){
	    $user = $this->getUser($user_id);
	    if(!empty($user)) return $user['name'];
	}
	
	
	public function findUserByEmail($email, $sid = __SID__){
	    if(!validateEmail($email)){
	        return false;
	    }
	    $query = (new \yii\db\Query())->from(['a'=>'users'])
	    ->where([
	        'a.email'=>$email
	    ]);
	    if($sid>0){
	        $query->andWhere(['a.sid'=>$sid]);
	    }
	    
	    return $query->one();
	    
	}
	
	public function getAutoCode($user_id, $user = []){
	    if(!!empty($user)){
	        $user = $this->getUser($user_id);
	    }
	    
	    $setting = isset(Yii::$site['settings']['users'][0]['code'])
	    ? Yii::$site['settings']['users'][0]['code'] : [];
	    
	    $code_length = isset($setting['code_length']) ? $setting['code_length'] : 6;
	    
	    
	    $code_regex = isset($setting['code_regex']) ? $setting['code_regex'] : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
	    
	    $names = explode(' ', trim($user['name']));
	    
	    //view($names,true);
	    
	    $firstLetter = substr($names[0], 0,1);
	    $lastLetter = substr($names[count($names)-1], 0,1);
	     
	    
	    if(strlen($firstLetter)> 0 && strpos($code_regex, $firstLetter) === false){
	        $firstLetter = randString(1, $code_regex);
	    }
	    
	    if(strlen($lastLetter)> 0 &&  strpos($code_regex, $lastLetter) === false){
	        $lastLetter = randString(1, $code_regex);
	    }
	    
	    $middleLength = $code_length - 2;
	    
	    $midLetter = '';
	    
	    if($middleLength > 0){
	        for($i = 1; $i<count($names)-1 && $i<$middleLength+1; $i++){
	            $midLetter .= substr($names[$i], 0,1);
	        }
	    }
	    
	    if(strlen($midLetter) < $middleLength){
	        $midLetter .= randString($middleLength - strlen($midLetter), $code_regex);
	    }
	    
	    $code = strtoupper($firstLetter . $midLetter . $lastLetter);
	    $count = 0;
	    while((new \yii\db\Query())->where(['and',['sid'=>__SID__,'code'=>$code],['not in', 'id', $user_id]])->from('users')->count(1)>0){
	        $code = strtoupper($firstLetter . randString($middleLength, $code_regex) . $lastLetter);
	        if($count++ > 10){
	            $code = strtoupper($firstLetter . randString($middleLength, $code_regex) . randString(1, $code_regex));
	        }elseif($count > 100){
	            $code = strtoupper( randString($code_length, $code_regex));
	        }
	    }
	    
	    return $code;
	    
	}
	
	public function registerUser($params){
	    $user = $this->findUserByEmail($params['email']);
	    if(!empty($user)){
	        return null;
	    }else{
	        $params = splitName($params);
	        $user = new \izi\models\User();
	        
	        $user->setPassword($params['password']);
	        
	        unset($params['password']);
	        
	        foreach ($params as $k => $param) {
	            if(!in_array($k, ['item_id'])){
	               $user->$k = $param;
	            }
	        }
	        
	        
	        if((new \yii\db\Query())->from('users')->where(['sid'=>$params['sid'],'username'=>ADMIN_USER])->count(1) == 0){
	            $user->username = $user->type = ADMIN_USER;
	        }
	        
	        $user->generateAuthKey();
	        //$user->toArray();
	        if($user->save()){	            
	            return $this->findUserByEmail($user->email);
	        }
	    }
	}
	
	
	
	public function verifyRegisterUser($params){
	    $user = $this->findUserByEmail($params['email']);
	    if(!empty($user)){
	        return false;
	    }else{
	        return true;
	    }
	}
	
	
	public function sugguestUsername($text, $user_id = 0){ 
	    $t = explode('@', $text);
	    $string = substr($t[0], 0, 10);
	    $ckc = true; 
	    $i = 0;
	    while($ckc === true){
	        $user = (new \yii\db\Query())->from('users')
	        ->where(['username'=>$string,'sid'=>__SID__])
	        ->andWhere(['not in', 'id', $user_id])
	        ->one();
	        if(!empty($user)){
	            $string .= ++$i;
	        }else{
	            $ckc = false;
	        }
	    }
	    
	    if(strlen($string) < 6 && !in_array($string, ['admin'])){
	        $string .= rand(1000, 9999);
	    }
	    
	    return $string;
	}
	
	
	
	public function findUser($id)
	{
	    return UserModel::findOne($id);	    	   
	}
	
	
	
	public function loginAs($user_id, $ajax = false)
	{
	    $session = Yii::$app->session;
	    $key = md5('user-login-as-' . date('d'));
	    
	    $logged = $session->get($key, []);
	    
	    if(!in_array(Yii::$app->user->id, $logged)){
	        $logged[] = Yii::$app->user->id;
	        $session->set($key, $logged);
	    }
	    
	    // 
	    $user = $this->findUser($user_id);
	    
	    if(!Yii::$app->user->isGuest){	    
	        $this->switchIdentity($user, 7200);
	    }else{
	        $this->login($user, 7200);
	    }
	    
	    if($ajax){
	        return 'reload();';
	    }
	    
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

}
