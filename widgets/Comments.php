<?php

namespace common\modules\comments\widgets;

use common\modules\comments\assets\CommentsAsset;
use common\modules\comments\Comments as CommentModule;
use common\modules\comments\Comments as CommentsModule;
use common\modules\comments\components\CommentsHelper;
use common\modules\comments\models\Comment;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class Comments extends \yii\base\Widget
{
    public $model;
    public $model_id = 0;

    public function init()
    {
        parent::init();

        if ($this->model instanceof Model) {
            $this->model_id = $this->model->id;
            $this->model = $this->model->tableName();
        }
    }

    public function run()
    {
        $commentsAsset = CommentsAsset::register($this->getView());
        CommentModule::getInstance()->commentsAssetUrl = $commentsAsset->baseUrl;

        $model = $this->model;
        $model_id = $this->model_id;

        $comment = new Comment(compact('model', 'model_id'));
        $comment->scenario = (Yii::$app->user->isGuest) ? Comment::SCENARIO_GUEST : Comment::SCENARIO_USER;
        $comment->status = Yii::$app->user->isGuest ? Comment::STATUS_PENDING : Comment::STATUS_APPROVED;
        $comment->is_new = Yii::$app->user->isGuest ? Comment::IS_NEW_YES : Comment::IS_NEW_NO;

        if ((!CommentModule::getInstance()->onlyRegistered || !Yii::$app->user->isGuest) && $comment->load(Yii::$app->getRequest()->post())) {

            if ($comment->validate() && Yii::$app->getRequest()->validateCsrfToken()
                && Yii::$app->getRequest()->getCsrfToken(true) && $comment->checkSpam() &&  $comment->save()
            ) {
                if (Yii::$app->user->isGuest) {
                    CommentsHelper::setCookies([
                        'username' => $comment->username,
                        'email' => $comment->email,
                    ]);
                }

                Yii::$app->getResponse()->redirect(Yii::$app->request->referrer);
                if(!YII_ENV_DEV){
                    /* Send notify */
                    $message = "New comment from ".($comment->username??'MyBaliTrips')." (".Yii::$app->request->hostInfo."/admin/comments) \r\n"
                        ."Comment: \r\n".$comment->content;
                    Yii::$app->telegram->sendMessageToLogs($message);
                }
                return;
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Comment::find(true)->where([
                'model' => $model,
                'model_id' => $model_id,
                'parent_id' => NULL,
                'status' => Comment::STATUS_PUBLISHED,
            ]),
            'pagination' => [
                'pageSize' => CommentsModule::getInstance()->commentsPerPage,
                'pageParam' => 'comment-page',
                'pageSizeParam' => 'comments-per-page',
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => CommentsModule::getInstance()->orderDirection,
                ]
            ],
        ]);

        return $this->render('comments', compact('model', 'model_id', 'comment', 'dataProvider'));
    }
}