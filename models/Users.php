<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $code
 * @property string $username
 * @property string $password
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
 * @property string $time
 * @property int $state
 * @property int $parent_id
 * @property int $sid
 * @property string $last_modify
 * @property string $bizrule
 * @property string $auth_key
 * @property string $password_reset_token
 * @property int $created_at
 * @property int $updated_at
 * @property int $branch_id
 * @property int $gender
 * @property string $access_token
 *
 * @property AuthAssignment[] $authAssignments
 * @property IntraRequestToUsers[] $intraRequestToUsers
 * @property IntraRequests[] $requests
 * @property UserToGroup[] $userToGroups
 * @property UserGroups[] $groups
 * @property UserToShop[] $userToShops
 * @property Shops[] $s
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'username', 'password', 'password_hash', 'name', 'fname', 'lname', 'address', 'email', 'phone', 'login_phone', 'bizrule', 'auth_key', 'password_reset_token', 'access_token'], 'required'],
            [['local_id', 'is_active', 'status', 'state', 'parent_id', 'sid', 'created_at', 'updated_at', 'branch_id', 'gender'], 'integer'],
            [['birth', 'time', 'last_modify'], 'safe'],
            [['bizrule'], 'string'],
            [['code', 'password', 'fname', 'lname'], 'string', 'max' => 64],
            [['username', 'email'], 'string', 'max' => 100],
            [['password_hash', 'name', 'access_token'], 'string', 'max' => 255],
            [['type', 'phone'], 'string', 'max' => 15],
            [['address'], 'string', 'max' => 300],
            [['login_phone'], 'string', 'max' => 16],
            [['auth_key', 'password_reset_token'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'username' => 'Username',
            'password' => 'Password',
            'password_hash' => 'Password Hash',
            'type' => 'Type',
            'name' => 'Name',
            'fname' => 'Fname',
            'lname' => 'Lname',
            'address' => 'Address',
            'local_id' => 'Local ID',
            'email' => 'Email',
            'phone' => 'Phone',
            'login_phone' => 'Login Phone',
            'birth' => 'Birth',
            'is_active' => 'Is Active',
            'status' => 'Status',
            'time' => 'Time',
            'state' => 'State',
            'parent_id' => 'Parent ID',
            'sid' => 'Sid',
            'last_modify' => 'Last Modify',
            'bizrule' => 'Bizrule',
            'auth_key' => 'Auth Key',
            'password_reset_token' => 'Password Reset Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'branch_id' => 'Branch ID',
            'gender' => 'Gender',
            'access_token' => 'Access Token',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIntraRequestToUsers()
    {
        return $this->hasMany(IntraRequestToUsers::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRequests()
    {
        return $this->hasMany(IntraRequests::className(), ['id' => 'request_id'])->viaTable('intra_request_to_users', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserToGroups()
    {
        return $this->hasMany(UserToGroup::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(UserGroups::className(), ['id' => 'group_id'])->viaTable('user_to_group', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserToShops()
    {
        return $this->hasMany(UserToShop::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasMany(Shops::className(), ['id' => 'sid'])->viaTable('user_to_shop', ['user_id' => 'id']);
    }
}
