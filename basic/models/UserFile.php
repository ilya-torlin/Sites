<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users_files".
 *
 * @property int $id
 * @property int $user_id
 * @property int $file_id
 *
 * @property Users $user
 * @property Files $file
 */
class UserFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'file_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => Files::className(), 'targetAttribute' => ['file_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'file_id' => 'File ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(Files::className(), ['id' => 'file_id']);
    }
}
