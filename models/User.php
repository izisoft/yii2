<?php
namespace app\models;
 
use yii\web\IdentityInterface;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property int $sid
 * @property string $code
 * @property string $username
 * @property string $password_hash
 * @property string $type
 * @property string $name
 * @property string $fname
 * @property string $lname
 * @property string $address
 * @property int $local_id
 * @property string $email
 * @property string $phone
 * @property string $login_phone
 * @property string $birth
 * @property int $is_active
 * @property int $status
 * @property string $auth_key
 * @property string $password_reset_token
 * @property int $gender
 * @property string $access_token
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property Shops $s
 */

class User extends ActiveRecord implements IdentityInterface {
    
//     public $id, $username, $password, $authkey, $accessToken;
    
    public static function tableName()
    {
        return '{{%users%}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'local_id', 'is_active', 'status', 'gender', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['username', 'password_hash', 'name'], 'required'],
            [['address'], 'string'],
            [['birth'], 'safe'],
            [['code', 'fname', 'lname'], 'string', 'max' => 64],
            [['username', 'email'], 'string', 'max' => 100],
            [['password_hash', 'name', 'access_token'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 15],
            [['phone', 'login_phone'], 'string', 'max' => 16],
            [['auth_key', 'password_reset_token'], 'string', 'max' => 128],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => \izi\models\Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(\izi\models\AuthAssignment::className(), ['user_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(\izi\models\Shops::className(), ['id' => 'sid']);
    }
    
    public static function findIdentity($id)
    {
        return self::findOne($id);
    }
    
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::find()->andWhere(['access_token' => $token])->one();
    }
    
    
    
    public static function findByUsername($username)
    {
        return self::find()->andWhere(['username' => $username])->one();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getAuthKey()
    {
        return null;
    }
    
    
    public function validateAuthKey($authKey)
    {
        return false;
    }
    
    
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
}