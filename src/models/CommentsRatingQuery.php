<?php

namespace ogheo\comments\models;

/**
 * This is the ActiveQuery class for [[CommentsRating]].
 * @see CommentsRating
 * @package ogheo\comments\models
 */
class CommentsRatingQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return CommentsRating|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @inheritdoc
     * @return CommentsRating[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }
}
