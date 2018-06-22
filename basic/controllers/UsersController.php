<?php

namespace app\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use app\filters\auth\HttpBearerAuth;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends ActiveController
{
     public $modelClass = 'app\models\Users';
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

    public function actions() {
       $actions = parent::actions();
       unset($actions['create'], $actions['update'], $actions['index'], $actions['view'], $actions['delete']);
       return $actions;
    }

    /**
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $users = \app\models\Users::find()->all();
        return $users;
    }

    /**
     * Add User. Password saved as hash, token is genering by random string.
     * @return integer
     */
    public function actionSave()
    {
         $params = \Yii::$app->request->post();
         if (!array_key_exists('login', $params)) {
              throw new \yii\web\ForbiddenHttpException(sprintf('Не указан login'));
         }
         if (!array_key_exists('pass', $params)) {
              throw new \yii\web\ForbiddenHttpException(sprintf('Не указан пароль'));
         }
         $current_user = \app\models\Users::find()->where(['login' => $params['login']])->one();
         if ($current_user) {
              throw new \yii\web\ForbiddenHttpException(sprintf('Пользователь с таким логином уже существует'));
         }
         $me = \Yii::$app->user->identity;
         $user = new \app\models\Users;
         $user->login = strip_tags($params['login']);
         $user->pass = \Yii::$app->getSecurity()->generatePasswordHash($params['pass']);
         $user->token = \Yii::$app->getSecurity()->generateRandomString(16);
         $user->save();
         return array('login' => $user->login,'token' => $user->token);
    }

    /**
     * Delete Users. Return null
     * @return null
    **/
    public function actionDelete($id){
         if (empty($id)) {
              throw new \yii\web\ForbiddenHttpException(sprintf('Пользователь с таким id не найден'));
         }
         $deletedUser = \app\models\Users::findOne($id);
         if (empty($deletedUser)) {
              throw new \yii\web\ForbiddenHttpException(sprintf('Пользователь с таким id не найден'));
         }
         $deletedUser->delete();
         return array('message' => 'Пользователь удален');

    }

}
