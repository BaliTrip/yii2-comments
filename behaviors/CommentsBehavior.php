<?php

namespace common\modules\comments\behaviors;

use common\modules\comments\widgets\Comments;
use yii\base\Behavior;

/**
 * Comments Behavior
 *
 * Render comments and form for owner model
 *
 */
class CommentsBehavior extends Behavior
{

    /**
     *
     * @return string the rendering result of the Comments Widget for owner model
     */
    public function displayComments()
    {
        return Comments::widget(['model' => $this->owner]);
    }
}