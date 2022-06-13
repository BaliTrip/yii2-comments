<?php

use common\modules\comments\Comments as CommentsModule;
use common\modules\comments\Comments;
use common\modules\comments\components\CommentsHelper;
use common\modules\comments\models\Comment;
use common\modules\comments\widgets\CommentsForm;
use yii\timeago\TimeAgo;
use yii\widgets\ListView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model common\modules\comments\models\Comment */
$commentsPage = Yii::$app->getRequest()->get("comment-page", 1);
$cacheKey = 'comment' . $model . $model_id . $commentsPage;
$cacheProperties = CommentsHelper::getCacheProperties($model, $model_id);
?>
<div class="comments">
    <?php if ($this->beginCache($cacheKey . '-count', $cacheProperties)) : ?>
        <h5><?= Comments::t('comments', 'All Comments') ?> (<?= Comment::activeCount($model, $model_id) ?>)</h5>
        <?php $this->endCache(); ?>
    <?php endif; ?>

    <?php if (!Comments::getInstance()->onlyRegistered || !Yii::$app->user->isGuest): ?>
        <div class="comments-main-form">
            <?= CommentsForm::widget(); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->beginCache($cacheKey, $cacheProperties)) : ?>
        <?php
        Pjax::begin();

        echo ListView::widget([
            'dataProvider' => $dataProvider,
            'emptyText' => CommentsModule::t('comments', 'No Comments'),
            'itemView' => function ($model, $key, $index, $widget) {
                $nested_level = 1;
                return $this->render('comment', compact('model', 'widget', 'nested_level'));
            },
            'options' => ['class' => 'comments'],
            'itemOptions' => ['class' => 'comment'],
            'layout' => '{items}<div class="text-center">{pager}</div>',
            'pager' => [
                'class' => yii\widgets\LinkPager::className(),
                'options' => ['class' => 'uk-pagination uk-flex-center'],
            ],
        ]);

        Pjax::end();

        $this->endCache();
        ?>
    <?php else: ?>
        <?php TimeAgo::widget(); ?>
    <?php endif; ?>
</div>
