<?php

namespace ogheo\comments\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use ogheo\comments\Module as CommentsModule;

/**
 * Class CommentsSearch represents the model behind the search form of `ogheo\comments\models\Comments`.
 * @package ogheo\comments\models
 */
class CommentsSearch extends Comments
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'main_parent_id',
                    'parent_id',
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'status'
                ],
                'integer'
            ],
            [['url', 'model', 'model_key', 'email', 'username', 'content', 'language', 'ip'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $commentClass = CommentsModule::getInstance()->commentModelClass;
        $query = $commentClass::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'main_parent_id' => $this->main_parent_id,
            'parent_id' => $this->parent_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'model', $this->model])
            ->andFilterWhere(['like', 'model_key', $this->model_key])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'language', $this->language])
            ->andFilterWhere(['like', 'ip', $this->ip]);

        return $dataProvider;
    }
}
