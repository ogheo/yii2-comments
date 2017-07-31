<?php

namespace ogheo\comments\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use ogheo\comments\helpers\CommentsHelper;
use ogheo\comments\Module as CommentsModule;

/**
 * This is the model class for table "comments_rating".
 *
 * @property $id
 * @property $comment_id
 * @property $created_by
 * @property $updated_by
 * @property $created_at
 * @property $updated_at
 * @property $ip
 * @property $status
 *
 * @package ogheo\comments\models
 */
class CommentsRating extends \yii\db\ActiveRecord
{
    /**
     * Statuses
     */
    const STATUS_LIKE = 1;
    const STATUS_DISLIKE = 2;

    /**
     * Scenarios
     */
    const SCENARIO_SAVE = 'save';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';

    /**
     * @var string
     */
    public static $commentRatingQueryModelClass = 'ogheo\comments\models\CommentsRatingQuery';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comments_rating';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_SAVE] = ['comment_id', 'status'];
        $scenarios[self::SCENARIO_UPDATE] = ['comment_id', 'status'];
        $scenarios[self::SCENARIO_DELETE] = ['comment_id', 'status'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment_id', 'status'], 'required'],
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'status'], 'integer'],
            [['comment_id'], function ($attribute, $params, $validator) {
                if ($this->scenario === self::SCENARIO_UPDATE) {
                    $comment_id = $this->$attribute;
                } else {
                    $comment_id = CommentsHelper::decodeId($this->$attribute);
                }
                $commentClass = CommentsModule::getInstance()->commentModelClass;
                if ((!intval($comment_id)) || (!$commentClass::find()->where(['id' => $comment_id])->exists())) {
                    $this->addError(
                        $attribute, Yii::t('comments', 'Sorry, something went wrong. Please try again later.')
                    );
                }
            }],
            [['ip'], 'ip'],
            [['ip'], 'string', 'max' => 46]
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->hasAttribute('ip')) {
                if ($this->getAttribute('ip') === null) {
                    $this->setAttribute('ip', Yii::$app->request->getUserIP());
                }
            }

            if ($this->hasAttribute('comment_id')) {
                $comment_id = $this->getAttribute('comment_id');
                if ($comment_id !== null) {
                    if ($this->scenario === self::SCENARIO_UPDATE) {
                        $this->setAttribute('comment_id', $comment_id);
                    } else {
                        $this->setAttribute('comment_id', CommentsHelper::decodeId($comment_id));
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     * @return object CommentsRatingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return Yii::createObject(self::$commentRatingQueryModelClass, [get_called_class()]);
    }
}
