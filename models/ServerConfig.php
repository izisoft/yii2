<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "server_config".
 *
 * @property int $id
 * @property int $sid
 * @property string $label
 * @property string $host_address
 * @property int $host_port
 * @property string $username
 * @property string $password
 * @property string $web_address
 * @property string $root_directory
 * @property string $connect_type
 * @property string $server_type
 * @property int $ssl_mode
 * @property int $update_source
 * @property int $is_active
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Shops $s
 */
class ServerConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'server_config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'host_port', 'ssl_mode', 'update_source', 'is_active', 'created_at', 'updated_at'], 'integer'],
            [['label', 'host_address', 'username', 'password', 'web_address', 'root_directory', 'connect_type', 'server_type'], 'string', 'max' => 255],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sid' => 'Sid',
            'label' => 'Label',
            'host_address' => 'Host Address',
            'host_port' => 'Host Port',
            'username' => 'Username',
            'password' => 'Password',
            'web_address' => 'Web Address',
            'root_directory' => 'Root Directory',
            'connect_type' => 'Connect Type',
            'server_type' => 'Server Type',
            'ssl_mode' => 'Ssl Mode',
            'update_source' => 'Update Source',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }
}
