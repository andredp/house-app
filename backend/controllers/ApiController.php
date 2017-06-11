<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use app\models\LoginForm;
use yii\web\Response;

class ApiController extends Controller
{

    public function behaviours()
    {
        $behaviors = parent::behaviors();
        return ArrayHelper::merge($behaviors, [
            'authenticator' => [
                'class' => HttpBearerAuth::className(),
                'only' => ['dashboard'],
                'except' => ['options'],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['dashboard'],
                'rules' => [
                    [
                        'actions' => ['dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ]);
    }

    public function actionLogin()
    {
        $model = new LoginForm(['scenario' => LoginForm::SCENARIO_LOGIN]);
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->login()) {
            return [
              'access_token' => Yii::$app->user->identity->getAuthKey()
            ];
        } else {
            $model->validate();
            return $model;
        }
    }

    public function actionValidateToken()
    {
        $model = new LoginForm(['scenario' => LoginForm::SCENARIO_VALIDATE]);
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->login()) {
            return [
                'username' => Yii::$app->user->identity->username
            ];
        } else {
            $model->validate();
            return $model;
        }
    }

    public function actionDashboard()
    {
        $response = [
            'username' => Yii::$app->user->identity->username,
            'access_token' => Yii::$app->user->identity->getAuthKey(),
        ];
        return $response;
    }
}
