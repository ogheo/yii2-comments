<?php

namespace ogheo\comments\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\caching\TagDependency;
use ogheo\comments\models\CommentsRating;
use ogheo\comments\helpers\CommentsHelper;
use ogheo\comments\models\Comments as CommentsModel;

/**
 * Class DefaultController for the `comments` module
 * @package ogheo\comments\controllers
 */
class DefaultController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'rate' => ['post'],
                    'create' => ['post'],
                    'validate' => ['post']
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['rate', 'create', 'validate'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['rate', 'create', 'validate'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'validate'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['rate'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Create new comment
     * @param $data
     * @return string
     */
    public function actionCreate($data)
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $comment = new CommentsModel(
                array_merge(
                    CommentsHelper::decryptData($data),
                    [
                        'scenario' => Yii::$app->user->isGuest ?
                            CommentsModel::SCENARIO_GUEST : CommentsModel::SCENARIO_USER
                    ]
                )
            );

            if (Yii::$app->user->isGuest && ($comment->username === null && $comment->email === null)) {
                $comment->username = CommentsHelper::getUsername();
                $comment->email = CommentsHelper::getEmail();
            }

            if ($comment->load(Yii::$app->request->post()) && $comment->validate()) {
                if ($comment->save()) {
                    if ($comment->username !== null && $comment->email !== null) {
                        CommentsHelper::setUsername($comment->username);
                        CommentsHelper::setEmail($comment->email);
                    }

                    TagDependency::invalidate(
                        Yii::$app->cache,
                        Url::previous(Yii::$app->controller->module->urlCacheSessionKey)
                    );

                    return [
                        'status' => 'success',
                        'message' => Yii::t('comments', 'Comment has been added successfully.')
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'errors' => $comment->errors
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => Yii::t('comments', 'Sorry, something went wrong. Please try again later.')
        ];
    }

    /**
     * Validate new comment
     * @throws \yii\base\ExitException
     */
    public function actionValidate()
    {
        $model = new CommentsModel([
            'scenario' => Yii::$app->user->isGuest ?
                CommentsModel::SCENARIO_GUEST : CommentsModel::SCENARIO_USER
        ]);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->data = ActiveForm::validate($model);
            Yii::$app->response->send();
            Yii::$app->end();
        }
    }

    /**
     * Rate comments
     * @return array
     */
    public function actionRate()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $newRating = new CommentsRating(['scenario' => CommentsRating::SCENARIO_SAVE]);
            if ($newRating->load(Yii::$app->request->post()) && $newRating->validate()) {

                $oldRating = CommentsRating::findOne([
                    'comment_id' => CommentsHelper::decodeId($newRating->comment_id),
                    'created_by' => Yii::$app->getUser()->getId()
                ]);

                if ($oldRating) {
                    if ($oldRating->status == $newRating->status) {
                        $oldRating->scenario = CommentsRating::SCENARIO_DELETE;
                        if ($oldRating->delete()) {
                            if ($oldRating->status == 1) {
                                CommentsHelper::deleteUprated($oldRating->comment_id);
                            } elseif ($oldRating->status == 2) {
                                CommentsHelper::deleteDownrated($oldRating->comment_id);
                            }

                            TagDependency::invalidate(
                                Yii::$app->cache,
                                Url::previous(Yii::$app->controller->module->urlCacheSessionKey)
                            );

                            return [
                                'status' => 'success',
                                'action' => 'unrated',
                                'message' => Yii::t('comments', 'Comment rating has been updated successfully.')
                            ];
                        }
                    } else {
                        $oldRating->status = $newRating->status;
                        $oldRating->scenario = CommentsRating::SCENARIO_UPDATE;
                        if ($oldRating->update()) {
                            if ($oldRating->status == 1) {
                                CommentsHelper::deleteDownrated($oldRating->comment_id);
                                CommentsHelper::setUprated($oldRating->comment_id);
                            } elseif ($oldRating->status == 2) {
                                CommentsHelper::deleteUprated($oldRating->comment_id);
                                CommentsHelper::setDownrated($oldRating->comment_id);
                            }

                            TagDependency::invalidate(
                                Yii::$app->cache,
                                Url::previous(Yii::$app->controller->module->urlCacheSessionKey)
                            );

                            return [
                                'status' => 'success',
                                'action' => $oldRating->status == 1 ? 'updated+' : 'updated-',
                                'message' => Yii::t('comments', 'Comment rating has been updated successfully.')
                            ];
                        }
                    }
                } else {
                    if ($newRating->save()) {
                        if ($newRating->status == 1) {
                            CommentsHelper::setUprated($newRating->comment_id);
                        } elseif ($newRating->status == 2) {
                            CommentsHelper::setDownrated($newRating->comment_id);
                        }

                        TagDependency::invalidate(
                            Yii::$app->cache,
                            Url::previous(Yii::$app->controller->module->urlCacheSessionKey)
                        );

                        return [
                            'status' => 'success',
                            'action' => 'rated',
                            'message' => Yii::t('comments', 'Comment rating has been updated successfully.')
                        ];
                    }
                }
            } else {
                return [
                    'status' => 'error',
                    'errors' => $newRating->errors
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => Yii::t('comments', 'Sorry, something went wrong. Please try again later.')
        ];
    }
}
