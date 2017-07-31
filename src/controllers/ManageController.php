<?php

namespace ogheo\comments\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use ogheo\comments\Module as CommentsModule;

/**
 * Class ManageController for the `comments` module
 * @package ogheo\comments\controllers
 */
class ManageController extends \yii\web\Controller
{
    /**
     * @var string
     */
    public $indexView = '@vendor/ogheo/yii2-comments/src/views/manage/index';

    /**
     * @var string
     */
    public $updateView = '@vendor/ogheo/yii2-comments/src/views/manage/update';

    /**
     * @var string
     */
    public $viewView = '@vendor/ogheo/yii2-comments/src/views/manage/view';

    /**
     * @var string
     */
    public $searchModelClass = 'ogheo\comments\models\CommentsSearch';

    /**
     * @var array verb filter config
     */
    public $verbFilterConfig = [
        'class' => 'yii\filters\VerbFilter',
        'actions' => [
            'delete' => ['POST'],
        ],
    ];

    /**
     * @var array access control config
     */
    public $accessControlConfig = [
        'class' => 'yii\filters\AccessControl',
        'rules' => [
            [
                'allow' => true,
                'roles' => ['admin'],
            ],
        ],
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => $this->verbFilterConfig,
            'access' => $this->accessControlConfig
        ];
    }

    /**
     * Lists all Comments models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = Yii::createObject($this->searchModelClass);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render($this->indexView, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Comments model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render($this->viewView, [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Updates an existing Comments model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render($this->updateView, [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Comments model and nested Comments models.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id)->delete();

        if ($model) {
            $commentClass = CommentsModule::getInstance()->commentModelClass;
            if (($nestedComments = $commentClass::find()->where(['main_parent_id' => $id])->all()) !== null) {
                foreach ($nestedComments as $nestedComment) {
                    $nestedComment->delete();
                }
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Comments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return mixed the loaded model
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $commentClass = CommentsModule::getInstance()->commentModelClass;
        if (($model = $commentClass::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
