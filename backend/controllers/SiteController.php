<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;

/**
 * Site controller
 */
class SiteController extends BaseController
{

    private  $appId;
    private  $appSecret;

    public   $session;

    public  function init()
    {
        //$this->appId      = Yii::$app->params['appid'];
        //$this->appSecret  = Yii::$app->params['appSecret'];
        //$this->session    = Yii::$app->session;
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            //错误处理
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            //验证码
            'captcha' => [
                'class'       =>  'yii\captcha\CaptchaAction',
                 'maxLength'  =>  4,
                 'minLength'  =>  4,
            ]
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        //已登录直接跳到首页
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $code    = $this->request->get('code',null);
        $baseUrl = urldecode(Yii::$app->params['redirect_url']);
        if(empty($code)){
            $url = $this->createOauthUrlCode($baseUrl);
            Header("Location:$url");
            exit();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * 生成code
     * @param $baseUrl
     * @return string
     */
    private function createOauthUrlCode($baseUrl){

      $params['client_id']       = $this->appId;
      $params['redirect_url']    = $baseUrl;
      $params['response_type']   = "code";

      $clue_string = $this->clueParam($params);

      return Yii::$app->params['authorize_url'].$clue_string;

    }

    /**
     * 拼接字符串
     * @param $params
     * @return string
     */
    private function clueParam($params){
        $str = '';
        if(empty($params['sign'])){
            foreach ($params as $key=>$v){

                $str.= $key."=".$v."&";
            }
        }
        $str = trim($str,'&');
        return $str;


    }

    private  function createOauthAccessToken(){



    }



}
