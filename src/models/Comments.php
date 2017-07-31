<?php

namespace ogheo\comments\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use ogheo\comments\helpers\CommentsHelper;
use ogheo\comments\Module as CommentsModule;

/**
 * Class Comments This is the model class for table "comments".
 *
 * @property mixed id
 * @property mixed url
 * @property mixed model
 * @property mixed model_key
 * @property mixed main_parent_id
 * @property mixed parent_id
 * @property mixed email
 * @property mixed username
 * @property mixed content
 * @property mixed language
 * @property mixed created_by
 * @property mixed updated_by
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed ip
 * @property mixed status
 * @property mixed ratingAggregation
 * @property mixed lastUpdateAuthor
 * @property mixed postedDate
 * @property mixed authorAvatar
 * @property mixed authorName
 * @property mixed authorUrl
 * @property mixed author
 * @property mixed children
 *
 * @package ogheo\comments\models
 */
class Comments extends \yii\db\ActiveRecord
{
    /**
     * Statuses
     */
    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_SPAM = 2;

    /**
     * Scenarios
     */
    const SCENARIO_GUEST = 'guest';
    const SCENARIO_USER = 'user';

    /**
     * Status for newly added comment.
     * By default comments are published without moderation.
     * @var int
     */
    public $newCommentStatus = self::STATUS_PUBLISHED;

    /**
     * Pattern that will be applied for user names on comment form.
     * @var string
     */
    public $usernameRegexp = '/^(\w|\p{L}|\d|_|\-| )+$/ui';

    /**
     * Pattern that will be applied for user names on comment form.
     * It contain regexp that should NOT be in username
     * Default pattern doesn't allow anything having "admin"
     * @var string
     */
    public $usernameBlackRegexp = '/^(.)*admin(.)*$/i';

    /**
     * @var string
     */
    public static $commentsQueryModelClass = 'ogheo\comments\models\CommentsQuery';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comments';
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

        $scenarios[self::SCENARIO_GUEST] = ['username', 'email', 'content', 'parent_id'];
        $scenarios[self::SCENARIO_USER] = ['content', 'parent_id'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['username', 'email'], 'required', 'on' => self::SCENARIO_GUEST],
            [['main_parent_id', 'created_by', 'updated_by', 'created_at', 'updated_at', 'status'], 'integer'],
            [['parent_id'], function ($attribute, $params, $validator) {
                $parent_id = CommentsHelper::decodeId($this->$attribute);
                if ((!intval($parent_id)) || (!self::find()->where(['id' => $parent_id])->exists())) {
                    $this->addError(
                        $attribute, Yii::t('comments', 'Sorry, something went wrong. Please try again later.')
                    );
                }
            }, 'on' => [
                self::SCENARIO_GUEST,
                self::SCENARIO_USER
            ]],
            [['ip'], 'ip'],
            [['ip'], 'string', 'max' => 46],
            [['url'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 10],
            [['model', 'model_key'], 'string', 'max' => 64],
            [['email', 'username'], 'string', 'max' => 128],
            [['username', 'content'], 'string', 'min' => 4],
            ['username', 'match',
                'pattern' => $this->usernameRegexp,
                'on' => self::SCENARIO_GUEST
            ],
            ['username', 'match',
                'not' => true,
                'pattern' => $this->usernameBlackRegexp,
                'on' => self::SCENARIO_GUEST
            ],
            ['username', 'unique',
                'targetClass' => CommentsModule::getInstance()->userModel,
                'targetAttribute' => 'username',
                'on' => self::SCENARIO_GUEST,
            ],
            [['email'], 'email'],
            [['content', 'url', 'model', 'model_key'], 'filter', 'filter' => 'yii\helpers\HtmlPurifier::process'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRating()
    {
        return $this->hasMany(CommentsModule::getInstance()->commentRatingModelClass, ['comment_id' => 'id']);
    }

    /**
     * Declares new relation based on 'rating', which provides aggregation.
     * @return \yii\db\ActiveQuery
     */
    public function getRatingAggregation()
    {
        return $this->getRating()->select(
            [
                'comment_id',
                'likes' => 'SUM(CASE status when 1 then 1 else 0 end)',
                'dislikes' => 'SUM(CASE status when 2 then 1 else 0 end)'
            ]
        )->groupBy('comment_id')->asArray(true);
    }

    /**
     * Get rating based on module settings.
     * @return int|null
     */
    public function getRatingCounter()
    {
        if ($this->isNewRecord) {
            return null;
        }

        if (!empty($this->ratingAggregation)) {
            return $this->ratingAggregation[0]['likes'] - $this->ratingAggregation[0]['dislikes'];
        }

        return 0;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(CommentsModule::getInstance()->userModel, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastUpdateAuthor()
    {
        return $this->hasOne(CommentsModule::getInstance()->userModel, ['id' => 'updated_by']);
    }

    /**
     * Get author name.
     * @return mixed
     */
    public function getAuthorName()
    {
        if ($this->author !== null) {
            if ($this->author->hasMethod('getUsername')) {
                return $this->author->getUsername();
            }

            return $this->author->username;
        }

        return $this->username;
    }

    /**
     * Get author avatar.
     * @return null
     */
    public function getAuthorAvatar()
    {
        if ($this->author !== null) {
            if ($this->author->hasMethod('getAvatar')) {
                return $this->author->getAvatar();
            }
        }

        return null;
    }

    /**
     * Get link options for author url.
     * Return example:
     * ~~~
     * return [
     *     '/profile', 'id' => $this->id
     * ];
     * ~~~
     *
     * @return null|array
     */
    public function getAuthorUrl()
    {
        if ($this->author !== null) {
            if ($this->author->hasMethod('getUrl')) {
                return $this->author->getUrl();
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getPostedDate()
    {
        return Yii::$app->formatter->asRelativeTime($this->created_at);
    }

    /**
     * Get counter for total number of comments
     * @param $params
     * @return CommentsQuery
     */
    public static function getCommentsCounter($params)
    {
        if ($params['model'] !== null) {
            $models = self::find()->byModel([
                'model' => $params['model'],
                'model_key' => $params['model_key']
            ]);
        } else {
            $models = self::find()->byUrl([
                'url' => $params['url']
            ]);
        }

        return $models;
    }

    /**
     * Get comments by model or url
     * @param $params
     * @return CommentsQuery
     */
    public static function getComments($params)
    {
        if ($params['model'] !== null) {
            $models = self::find($params)->byModel([
                'model' => $params['model'],
                'model_key' => $params['model_key']
            ])->withoutChildren()->with('author', 'ratingAggregation');
        } else {
            $models = self::find($params)->byUrl([
                'url' => $params['url']
            ])->withoutChildren()->with('author', 'ratingAggregation');
        }

        return $models;
    }

    /**
     * Check if comment has children
     * @return bool
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }

    /**
     * Get children comment
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set children comment
     * @param $value
     */
    public function setChildren($value)
    {
        $this->children = $value;
    }

    /**
     * @inheritdoc
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->hasAttribute('status')) {
                if ($this->getAttribute('status') === null) {
                    $this->setAttribute('status', $this->newCommentStatus);
                }
            }

            if ($this->hasAttribute('language')) {
                if ($this->getAttribute('language') === null) {
                    $this->setAttribute('language', Yii::$app->language);
                }
            }

            if ($this->hasAttribute('ip')) {
                if ($this->getAttribute('ip') === null) {
                    $this->setAttribute('ip', Yii::$app->request->getUserIP());
                }
            }

            if ($this->hasAttribute('main_parent_id') && $this->hasAttribute('parent_id')) {
                if ($this->scenario !== 'default') {
                    $parent_id = CommentsHelper::decodeId($this->getAttribute('parent_id'));
                    if ($parent_id !== null && $parent_id) {
                        $parent = self::find()->where(['id' => $parent_id])->select('main_parent_id')->one();
                        $main_parent_id = isset($parent->main_parent_id) ? $parent->main_parent_id : $parent_id;
                        $this->setAttribute('main_parent_id', $main_parent_id);
                        $this->setAttribute('parent_id', $parent_id);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     * @param null $params
     * @return CommentsQuery the active query used by this AR class.
     */
    public static function find($params = null)
    {
        $query = Yii::createObject(self::$commentsQueryModelClass, [get_called_class()]);

        if ($params) {
            $query->loadParams = $params;
        }

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        if ($this->scenario === null) {
            return [
                'id' => Yii::t('comments', 'ID'),
                'url' => Yii::t('comments', 'Url'),
                'model' => Yii::t('comments', 'Model'),
                'model_key' => Yii::t('comments', 'Model Key'),
                'main_parent_id' => Yii::t('comments', 'Main Parent ID'),
                'parent_id' => Yii::t('comments', 'Parent ID'),
                'email' => Yii::t('comments', 'Email'),
                'username' => Yii::t('comments', 'Name'),
                'content' => Yii::t('comments', 'Content'),
                'language' => Yii::t('comments', 'Language'),
                'created_by' => Yii::t('comments', 'Created By'),
                'updated_by' => Yii::t('comments', 'Updated By'),
                'created_at' => Yii::t('comments', 'Created At'),
                'updated_at' => Yii::t('comments', 'Updated At'),
                'ip' => Yii::t('comments', 'Ip'),
                'status' => Yii::t('comments', '
                    0-pending,
                    1-published,
                    2-spam
                '),
            ];
        } else {
            return [
                'email' => Yii::t('comments', 'Email'),
                'username' => Yii::t('comments', 'Name'),
                'content' => Yii::t('comments', 'Share your thoughts...')
            ];
        }
    }
}
