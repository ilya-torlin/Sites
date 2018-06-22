<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $login
 * @property string $pass
 * @property string $token
 *
 * @property UsersFiles[] $usersFiles
 */
class Users extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
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
            [['login', 'pass', 'token'], 'required'],
            [['login'], 'string', 'max' => 50],
            [['pass', 'token'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Login',
            'pass' => 'Pass',
            'token' => 'Token',
        ];
    }

    /**
    * Lists all Users fileds exclude token and pass.
    * @return mixed
    **/
    public function fields() {
        $fields = parent::fields();
        // unset secret fileds from responce from server
        unset($fields['pass'], $fields['token']);
        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsersFiles()
    {
        return $this->hasMany(UsersFiles::className(), ['user_id' => 'id']);
    }

    public function getAuthKey(): string {
        //  return $this->authKey;
    }

    public function getId() {
        return $this->id;
    }

    public function validateAuthKey($authKey): bool {
        //  return $this->getAuthKey() === $authKey;
    }

    public static function findIdentity($id): \yii\web\IdentityInterface {
        $user = static::findOne($id);
        return isset($user) ? $user : null;
    }

    public static function findIdentityByAccessToken($token, $type = null): \yii\web\IdentityInterface {
        //return static::findOne(1);
        $user = static::findOne(['token' => $token]);
        if(!isset($user))
             throw new \yii\web\ForbiddenHttpException(sprintf('Авторизация не пройдена'));
        return $user;
    }
}
