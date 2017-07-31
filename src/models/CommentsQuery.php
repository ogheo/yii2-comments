<?php

namespace ogheo\comments\models;

use Yii;
use ogheo\comments\helpers\CommentsHelper;
use ogheo\comments\Module as CommentsModule;

/**
 * Class CommentsQuery This is the ActiveQuery class for [[Comments]].
 *
 * @property mixed loadParams
 *
 * @package ogheo\comments\models
 */
class CommentsQuery extends \yii\db\ActiveQuery
{
    /**
     * @var array
     */
    private $_params = [];

    /**
     * @return array
     */
    public function getLoadParams()
    {
        return $this->_params;
    }

    /**
     * @param $params
     */
    public function setLoadParams($params)
    {
        $this->_params = $params;
    }

    /**
     * Select by model
     * @param $params
     * @return $this
     */
    public function byModel($params)
    {
        $commentClass = CommentsModule::getInstance()->commentModelClass;

        return $this->andWhere([
            'model' => $params['model'],
            'model_key' => $params['model_key'],
            'language' => Yii::$app->language,
            'status' => $commentClass::STATUS_PUBLISHED,
        ]);
    }

    /**
     * Select by url
     * @param $params
     * @return $this
     */
    public function byUrl($params)
    {
        $commentClass = CommentsModule::getInstance()->commentModelClass;

        return $this->andWhere([
            'url' => $params['url'],
            'language' => Yii::$app->language,
            'status' => $commentClass::STATUS_PUBLISHED,
        ]);
    }

    /**
     * Select without children
     * @return $this
     */
    public function withoutChildren()
    {
        return $this->andWhere([
            'main_parent_id' => null
        ]);
    }

    /**
     * @inheritdoc
     * @return Comments|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @inheritdoc
     * @param null $db
     * @return array|\yii\db\ActiveRecord[]
     */
    public function all($db = null)
    {
        $result = parent::all($db);

        if (!isset($this->loadParams['loadComments']) || $this->loadParams['loadComments'] !== true) {
            return $result;
        }

        $ids = [];
        foreach ($result as $model) {
            $ids[] = $model->id;
        }

        $parentsId = implode(',', $ids);
        if (!empty($parentsId)) {
            $commentClass = CommentsModule::getInstance()->commentModelClass;
            $nestedComments = $commentClass::find()->where("main_parent_id IN ($parentsId)")->orderBy([
                'created_at' => isset($this->loadParams['nestedOrder']) ? $this->loadParams['nestedOrder'] : null,
            ])->all();

            if (!empty($nestedComments)) {
                $mergedComments = array_merge($result, $nestedComments);
                $result = CommentsHelper::buildCommentsTree($mergedComments);
            }
        }

        return $result;
    }
}
