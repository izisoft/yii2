<?php
namespace izi\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class Member extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = -5;
    const STATUS_ACTIVE = 10;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customers}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
    	
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }
    
    public static function getItem($id=0,$o=[]){
    	$item = static::find()->from(['a'=>self::tableName()])
    	->select(['a.*','title'=>'a.name'])
    	->where(['a.id'=>$id, 'a.sid'=>__SID__])
    	->asArray()->one();
    	
    	return $item;
    }
    
    
    public function quick_insert($f){
    	$fn = '';
    	if(isset($f['fullName'])){
    		$fn = $f['fullName'];
    		unset($f['fullName']);
    	}
    	if(isset($f['fullname'])){
    		$fn = $f['fullname'];
    		unset($f['fullname']);
    	}
    	if(isset($f['full_name'])){
    		$fn = $f['full_name'];
    		unset($f['full_name']);
    	}
    	$f['type_id'] = TYPE_ID_MEMBERS;
    	if(!isset($f['sid'])) $f['sid'] = __SID__;
    	if(!isset($f['is_active'])) $f['is_active'] = 1;
    	//
    	$pos = strrpos(trim($fn), ' ');
    	$f['fname'] = $pos !== false ? substr($fn, $pos+1) : $fn;
    	$f['lname'] = $pos !== false ? substr($fn,0,$pos) : '';
    	return Yii::$app->zii->insert($this->tableName(),$f);
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public function findByUsername($username) 
    {    	
    	$u = static::find();
    	if(validateEmail($username)){
    		$u->where(['email'=>$username]);
    	}else{
    		$u->where(['username' => $username]);
    	}
    	return $u->andWhere(['status' => self::STATUS_ACTIVE]+($username === ROOT_USER ? [] : ['sid'=>__SID__]))->one();
    }
    
    public function findByUsername2($username)
    {
    	$u = static::find();
    	if(validateEmail($username)){
    		$u->where(['email'=>$username]);
    	}else{
    		$u->where(['username' => $username]);
    	}
    	return $u->andWhere(['sid'=>__SID__])->one();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }
    public function getPasswordHash()
    {
    	return $this->password_hash;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
    	//return $this->type;
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {    	
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    public function registerFromFacebook($params){
        $mem = Yii::$app->member->model->findByUsername2($params['email']);
        
        if(!empty($mem)){
            return $mem;
        }else{
        
        $user = new Member();
        $password = randString(6);
        //  $user->username = $this->username;
        $biz  = [            
            'thumbnail'=>$params['picture']['data']['url'],
            'icon'=>'https://graph.facebook.com/'.$params['id'].'/picture?type=large',
            'avatar'=>'https://graph.facebook.com/'.$params['id'].'/picture?type=large',
            'timezone'=>isset($params['timezone'])  ?$params['timezone'] : 7,
            'locale'=>isset($params['locale']) ? $params['locale'] : 'vi_VN',
            'link_facebook'=>isset($params['link']) ? $params['link'] : '#'
        ];
        
        $user->sid = __SID__;
        $user->code = isset($params['code']) && $params['code'] != "" ? $params['code'] : (genCustomerCode(
            isset(Yii::$site['settings']['customers'][TYPE_ID_MEMBERS]['code']) ?
            Yii::$site['settings']['customers'][TYPE_ID_MEMBERS]['code'] : []));
        $user->email = $params['email'];
        $user->facebook_id=$params['id'];
        $user->fname = $params['first_name'];
        $user->lname = $params['last_name'];
        $user->name = $params['last_name'] . ' ' . $params['first_name'];
        $user->gender = isset($params['gender']) ? ($params['gender'] == 'male' ? 1 : ($params['gender'] == 'female' ? 0 : 3) ) : 3;
        $user->bizrule = json_encode($biz, JSON_UNESCAPED_UNICODE);
        $user->type_id = TYPE_ID_MEMBERS;
        $user->setPassword($password);
        $user->generateAuthKey();
        
        if($user->save()){
            Member::updatePassword($user,[
                'password'=>$password
            ]);
            return $user;
        }
        }
        return null;
        
    }
    
    public static function register_facebook($member = []){
    	$user = new Member();
    	$password = randString(6);
    	//  $user->username = $this->username;
    	$biz  = [
    			'thumbnail'=>$member['picture']['data']['url'],
    			'icon'=>'https://graph.facebook.com/'.$member['id'].'/picture?type=large',
    			'avatar'=>'https://graph.facebook.com/'.$member['id'].'/picture?type=large',
    			'locale'=>$member['locale'],
    			'link_facebook'=>$member['link'],
    			'timezone'=>$member['timezone']    			
    	];
    	$user->sid = __SID__;
    	$user->code = isset($member['code']) ? $member['code'] : (genCustomerCode(
    			isset(Yii::$site['settings']['customers'][TYPE_ID_MEMBERS]['code']) ?
    			Yii::$site['settings']['customers'][TYPE_ID_MEMBERS]['code'] : []));
    	$user->email = $member['email'];
    	$user->facebook_id=$member['id'];
    	$user->fname = $member['first_name'];
    	$user->lname = $member['last_name'];
    	$user->name = $member['last_name'] . ' ' . $member['first_name'];
    	$user->gender = ($user['gender'] == 'male' ? 1 : ($user['gender'] == 'female' ? 0 : 3) );
    	$user->bizrule = json_encode($biz);
    	$user->type_id = TYPE_ID_MEMBERS;
    	//$user-> = 
    	
    	$user->setPassword($password);
    	$user->generateAuthKey();
    	if($user->save()){
    		self::updatePassword($user,[
    				'password'=>$password
    		]);
    		return $user;
    	}
    	return null;
    	//return $user->save() ? $user : null;
    }
    
    public static function register_google($member = []){
    	$user = new Member();
    	$password = randString(6);
    	$name = splitName(['fname'=>$member['name']]);
    	//  $user->username = $this->username;
    	$biz  = [
    			'thumbnail'=>$member['picture'],
    			'icon'=>$member['picture'],
    			'avatar'=>$member['picture'],
    			'locale'=>$member['locale'],
    			'link_google'=>isset($member['link']) ? $member['link'] : '',
    			 
    	];
    	$user->sid = __SID__;
    	$user->code = isset($member['code']) ? $member['code'] : (genCustomerCode(
    			isset(Yii::$site['settings']['customers'][TYPE_ID_MEMBERS]['code']) ?
    			Yii::$site['settings']['customers'][TYPE_ID_MEMBERS]['code'] : []));
    	$user->email = $member['email'];
    	$user->google_id =$member['id'];
    	$user->fname = $name['fname'];
    	$user->lname = $name['lname'];
    	$user->name = $member['name'];
    	$user->gender = ($user['gender'] == 'male' ? 1 : ($user['gender'] == 'female' ? 0 : 3) );
    	$user->bizrule = json_encode($biz);
    	$user->type_id = TYPE_ID_MEMBERS;
    	$user->created_at = time();
    	//$user-> =
    	
    	$user->setPassword($password);
    	$user->generateAuthKey();
    	if($user->save()){
    		self::updatePassword($user,[
    				'password'=>$password
    		]);
    		return $user;
    	}
    	return null;
    	//return $user->save() ? $user : null;
    }
    
    public static function updatePassword($item,$o = []){
    	if(!isset($item->id)){
    		$item = self::findByUsername($item->email);
    	}
    	$password = isset($o['password']) ? $o['password'] : false;
    	
    	$shop = \app\modules\admin\models\Shops::getItem(__SID__);
    	$f = [];
    	if(!empty($item) && !empty($shop)){
    		if($password == false){
    		$password = randString(6);
    		$f['password_hash'] = Yii::$app->security->generatePasswordHash($password);
    		$f['updated_at'] = time();
    		$f['auth_key'] = Yii::$app->security->generateRandomString();
    		
    		Yii::$app->db->createCommand()->update(self::tableName(),$f+['status'=>\common\models\User::STATUS_ACTIVE],['id'=>$item->id,'sid'=>__SID__])->execute();
    		//
    		}
    		$search = array(
    				'{LOGO}',
    				'{DOMAIN}',
    				'{USER}',
    				'{USER_NAME}',
    				'{USER_PASSWORD}',
    				'{ADMIN_LINK}',
    				
    		);
    		$fullname = $item->lname . ' ' . $item->fname;
    		
    		$replace = array(
    				isset(Yii::$site['logo']['logo']['image']) ? '<img src="' . Yii::$site['logo']['logo']['image']  .'" height="60"/>': '',
    				$shop['domain'],
    				strlen($fullname) > 2 ? $fullname : $item->email,
    				$item->username != "" ? $item->username : $item->email,
    				$password,
    				'http://' . $shop['domain'] .'/members',
    				
    		);
    		$text = Yii::$app->getTextRespon(array('code'=>'RP_SENDPASS', 'show'=>false));
    		//view($text); exit;
    		$form = str_replace($search, $replace, uh($text['value'],2));
    		$fx = Yii::$app->getConfigs('CONTACTS');
    		Yii::$app->sendEmail([
    				'subject'=>str_replace($search, $replace, $text['title'])  ,
    				'body'=>$form,
    				'from'=>$fx['email'],
    				'fromName'=>$fx['short_name'],
    				'replyTo'=>$fx['email'],
    				'replyToName'=>$fx['short_name'],
    				'to'=>$item->email,'toName'=>$item->lname . ' ' . $item->fname
    		]);
    	}
    }
    
    public static function showMemberName($member_id = 0){
    	$v = static::findOne(['id' => $member_id]);
    	if(!empty($v)){
    		if($v['name'] != ""){
    			return $v['name'];
    		}else{
    			return $v['email'];
    		}
    	}else{
    		return '-';
    	}
    }
}
