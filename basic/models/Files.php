<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "files".
 *
 * @property int $id
 * @property string $name
 * @property string $path
 * @property string $meta
 * @property int $size
 * @property string $created
 * @property int $creator
 *
 * @property UsersFiles[] $usersFiles
 */
class Files extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meta', 'size', 'created'], 'required'],
            [['meta'], 'string'],
            [['size', 'creator'], 'integer'],
            [['created'], 'safe'],
            [['name', 'path'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'path' => 'Path',
            'meta' => 'Meta',
            'size' => 'Size',
            'created' => 'Created',
            'creator' => 'Creator',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsersFiles()
    {
        return $this->hasMany(UsersFiles::className(), ['file_id' => 'id']);
    }
}
