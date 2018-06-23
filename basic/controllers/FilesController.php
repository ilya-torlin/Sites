<?php

namespace app\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use app\filters\auth\HttpBearerAuth;

/**
 * FilesController implements the CRUD actions for Files model.
 */
class FilesController extends ActiveController
{

     public $modelClass = 'app\models\Files';
    /**
     * {@inheritdoc}
     */
     public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => ['Origin' => ['*']]];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        return $behaviors;
    }

    public function actions()
    {
       $actions = parent::actions();
       unset($actions['create'], $actions['update'], $actions['index'], $actions['view'], $actions['delete']);
       return $actions;
    }

    /**
     * Lists all Files models.
     * @return mixed
     */
    public function actionIndex()
    {
        $me = \Yii::$app->user->identity;
        $files = \app\models\Files::find()->where(['creator' => $me->id])->all();
        if (empty($files)) {
             return array('message' => 'В директории нет файлов');
        }
        return $files;
    }

    /**
     * Displays a single Files model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $content = null;
        $me = \Yii::$app->user->identity;
        $file = \app\models\Files::findOne($id);

        if (empty($file)) {
             throw new \yii\web\ForbiddenHttpException(sprintf('Файл не найден'));
        }

        if (!($file->creator === $me->id)) {
             throw new \yii\web\ForbiddenHttpException(sprintf('Вы не являетесь владельцем файла'));
        }

        $fileExtention = pathinfo($file->path)['extension'];

        if (in_array($fileExtention,array('doc','txt','rtf')) ) {
             $content = file_get_contents($file->path);
        }

        return array('file' => $file,
                    'content' => $content,
               );
    }

    public function getFileMeta($file)
    {
         return array(
              "size" => filesize($file),
              "modified" => filemtime($file),
              "path" => $file,
              "is_dir" => is_dir($file),
              "mime_type" => mime_content_type($file),
         );
    }

    /**
     * Creates a new Files model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * Allowed extentions 'doc','docx','xls','xlsx','jpg','jpeg','pdf','png','txt','rtf'
     * @param file $file
     * @return mixed
     */
     public function actionPostcreate()
     {
          // заполняем поля из post
          $params = \Yii::$app->request->post();
          // получаем текущего пользователя
          $me = \Yii::$app->user->identity;
          // получаем переданные файлы
          $files = \yii\web\UploadedFile::getInstancesByName('file');
          $added_files = array();
          foreach ($files as $file) {
               if(!in_array($file->getExtension(),\Yii::$app->params['extensions']))
                    throw new \yii\web\ForbiddenHttpException(sprintf('Файл с таким расширением нельзя загрузить'));
               if($file->size >= \Yii::$app->params['MAX_FILE_SIZE'])
                    throw new \yii\web\ForbiddenHttpException(sprintf('Размер файла привышает допустимый'));
               $filename = $file->getBaseName() .'.'. $file->getExtension();
               $dirName = \Yii::$app->params['defaultPath'] . $filename;

               if (file_exists($dirName)) {
                    throw new \yii\web\ForbiddenHttpException(sprintf('Файл с таким именем существует'));
               }

               $file->saveAs($dirName, true);
               $newfile = new \app\models\Files;
               // заполняем имя файла
               $newfile->name = $file->getBaseName() .'.'. $file->getExtension();
               $newfile->path = $dirName;
               $newfile->size = $file->size;
               $newfile->created = date("Y-m-d H:i:s");
               $newfile->creator = $me->id;
               $newfile->meta = json_encode($this->getFileMeta(\Yii::$app->params['defaultPath'].$filename));
               $newfile->save();
               $added_files[] = $newfile;
               // создаем связь с проектом
               $UserFile = new \app\models\UserFile();
               $UserFile->file_id = $newfile->id;
               $UserFile->user_id = $me->id;
               $UserFile->save();
          }
          return $added_files;
     }

     public function actionPutcreate()
     {
          $filename = \Yii::$app->params['defaultPath'];
          $filename .= (isset($_GET['filename'])) ? $_GET['filename'] : 'unknow.dat';

          if (file_exists($filename)) {
               throw new \yii\web\ForbiddenHttpException(sprintf('Файл с таким именем существует'));
          }

          $putdata = fopen("php://input", "r");
          $fp = fopen($filename, "w");
          while ($data = fread($putdata, 1024))
               fwrite($fp, $data);
          fclose($fp);
          fclose($putdata);

          if(!file_exists($filename)){
               throw new \yii\web\ForbiddenHttpException(sprintf('Не удалось скопировать файл'));
          }

          if( filesize($filename) > \Yii::$app->params['MAX_FILE_SIZE'] ){
               unlink($filename);
               throw new \yii\web\ForbiddenHttpException(sprintf('Размер файла привышает допустимый'));
          }

          $me = \Yii::$app->user->identity;
          $dirName = $filename;
          $newfile = new \app\models\Files;
          // заполняем имя файла
          $newfile->name = basename($filename);
          $newfile->path = $filename;
          $newfile->size = filesize($filename);
          $newfile->created = date("Y-m-d H:i:s");
          $newfile->creator = $me->id;
          $newfile->meta = json_encode($this->getFileMeta($filename));
          $newfile->save();
          // создаем связь с пользователем
          $UserFile = new \app\models\UserFile();
          $UserFile->file_id = $newfile->id;
          $UserFile->user_id = $me->id;
          $UserFile->save();
          return $newfile;
     }

     /**
     * Deletes an existing Files model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $content = null;
        $me = \Yii::$app->user->identity;
        $file = \app\models\Files::findOne($id);
        if (empty($file)) {
            throw new \yii\web\ForbiddenHttpException(sprintf('Такого файла не существует'));
        }
        if (!($file->creator === $me->id)) {
             throw new \yii\web\ForbiddenHttpException(sprintf('Вы не являетесь владельцем файла'));
        }
        $filepath = $file->path;
        $file->delete();
        unlink($filepath);
        return array('message' => 'Файл успешно удален');
    }

    /**
     * Update an existing Files model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdatefile()
    {
        $params = \Yii::$app->request->post();
        $id = $params['id'];
        $me = \Yii::$app->user->identity;
        $file = \app\models\Files::findOne($id);

        if (empty($file)) {
            throw new \yii\web\ForbiddenHttpException(sprintf('Такого файла не существует'));
        }

        if (!($file->creator === $me->id)) {
             throw new \yii\web\ForbiddenHttpException(sprintf('Вы не являетесь владельцем файла'));
        }

        if(empty($params['content']))
          return array('message' => 'контент пустой');

        file_put_contents($file->path, $params['content']);

        return array('message' => 'Файл успешно обновлен');
    }

    public function actionCreatepostfile()
    {
       $params = \Yii::$app->request->post();
       $me = \Yii::$app->user->identity;

       if (empty($params['filename'])) {
            throw new \yii\web\ForbiddenHttpException(sprintf('Не задано имя'));
       }

       if (!isset($params['content'])) {
            throw new \yii\web\ForbiddenHttpException(sprintf('Содержимое файла отсутствует'));
       }

       $filename = \Yii::$app->params['defaultPath'] . $params['filename'];
       if (file_exists($params['filename'])) {
            throw new \yii\web\ForbiddenHttpException(sprintf('Файл с таким именем существует'));
       }

       $extention = pathinfo($filename);
       if(!in_array($extention['extension'],\Yii::$app->params['extensions']))
          throw new \yii\web\ForbiddenHttpException(sprintf('Файл с таким расширением нельзя загрузить'));

       $filesize = file_put_contents($filename, $params['content']);

       if( $filesize > \Yii::$app->params['MAX_FILE_SIZE'] ){
            unlink($filename);
            throw new \yii\web\ForbiddenHttpException(sprintf('Размер файла привышает допустимый'));
       }

       $newfile = new \app\models\Files;
       // заполняем имя файла
       $newfile->name = basename($filename);
       $newfile->path = $filename;
       $newfile->size = filesize($filename);
       $newfile->created = date("Y-m-d H:i:s");
       $newfile->creator = $me->id;
       $newfile->meta = json_encode($this->getFileMeta($filename));
       $newfile->save();
       // создаем связь с пользователем
       $UserFile = new \app\models\UserFile();
       $UserFile->file_id = $newfile->id;
       $UserFile->user_id = $me->id;
       $UserFile->save();
       return $newfile;
    }
}
