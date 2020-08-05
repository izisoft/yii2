<?php
namespace izi\collaborator\models;

use yii\base\Model;

use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $verifyCode;
    public $sid;

    
    public function attributeLabels()
    {
        return [
            'verifyCode' => Yii::$app->t->translate('label_captcha_code') ,
            //'full_name' => getTextTranslate(136),
            'email' => Yii::$app->t->translate('label_email_address') ,
            'password' => Yii::$app->t->translate('label_password') ,
            //'phone' => getTextTranslate(138),
            //'address' => getTextTranslate(137),
            //'body'=>getTextTranslate(225),
            
        ];
    }  

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
//             ['username', 'required'],
//             ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
//             ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            
            ['verifyCode', 'required'],
            ['verifyCode', 'captcha'],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        
        if (!$this->validate()) {
            return null;
        }
        $item = Yii::$app->collaborator->model->findByUsername2($this->email);
        
        if(!empty($item)) return null;
        
        $user = new Member();
        //  $user->username = $this->username;
        $user->sid = __SID__;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->type_id = TYPE_ID_COLLABORATOR;
        $user->created_at = time();        
        $user->updated_at = time();
        $user->auth_key = Yii::$app->security->generateRandomString();
       
//         $user->code = genCustomerCode(
//             isset(Yii::$site['settings']['customers'][TYPE_ID_COLLABORATOR]['code']) ?
//             Yii::$site['settings']['customers'][TYPE_ID_COLLABORATOR]['code'] : []);
        
        if($user->save()){
//             \common\models\Member::updatePassword($user,[
//                 'password'=>$this->password
//             ]);

            // Sent email to user
            
            $text1 = Yii::$app->frontend->getTextRespon(array('code'=>'RP_SENDPASS', 'show'=>false));
            
            $regex = [
                '{LOGO}' => '<img src="' . Yii::$app->cfg->app['logo']['image']  .'" height="90" style="max-height:90px;"/>',
                '{DOMAIN}'   =>  ABSOLUTE_DOMAIN,
                '{USER}'    =>  $this->email,
                '{USER_NAME}'   =>  $this->email,
                '{USER_PASSWORD}'   =>  $this->password,
                '{MEMBER_LINK}' => ABSOLUTE_DOMAIN . '/member',
                '{ADMIN_LINK}' => ABSOLUTE_DOMAIN . '/member',
                
            ];
            
 
            
            $body = str_replace(array_keys($regex), array_values($regex), uh($text1['value'],2));
 
//             $fx = Yii::$app->cfg->contact;
            
            Yii::$app->mailer->sendEmail([
                'subject'=>Yii::$app->t->translate('label_account_infomation_from_system'),
                'body'=>$body,
//                 'from'=>$fx['email'],
               
                'to'=> [
                    $this->email => $this->email
                ]
            ]);
            
            
            return $user;
            //return \app\models\Member::findByEmail($this->email);
        }
        return null;
    }
}
