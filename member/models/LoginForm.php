<?php
namespace izi\member\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    
    public $type_id = TYPE_ID_MEMBER;

    private $_user;

    

    public function init()
    {
        Member::setTypeId($this->type_id);
    }
    /**
     * 
     * {@inheritDoc}
     * @see \yii\base\Model::attributeLabels()
     */
    public function attributeLabels()
    {
        return [
            //'verifyCode' => getTextTranslate(226),
            //'full_name' => getTextTranslate(136),
            'username' => Yii::$app->t->translate('label_username_or_email') , // 'Tên đăng nhập hoặc Email',
            'password' => Yii::$app->t->translate('label_password') , //'Mật khẩu' ,
            //'phone' => getTextTranslate(138),
            //'address' => getTextTranslate(137),
            //'body'=>getTextTranslate(225),
            
        ];
    }  
    
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::$app->t->translate('label_username_or_password_not_correct'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        
        if ($this->validate()) {
            
            
            
            return Yii::$app->member->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Member::findByUsername($this->username);
        }

        return $this->_user;
    }
}
