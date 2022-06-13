<?php

use common\modules\comments\assets\CommentsAsset;
use common\modules\comments\Comments;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\captcha\Captcha;

/* @var $this yii\web\View */
/* @var $model common\modules\comments\models\Comment */

if(isset($lang)){
    Yii::$app->language = $lang;
}

?>

<?php
$commentsAsset = CommentsAsset::register($this);
Comments::getInstance()->commentsAssetUrl = $commentsAsset->baseUrl;

$col12 = Comments::getInstance()->gridColumns;
$col6 = (int) ($col12 / 2);
$col4 = (int) ($col12 / 3);

$formID = 'comment-form' . (($comment->parent_id) ? '-' . $comment->parent_id : '');
$replyClass = ($comment->parent_id) ? 'comment-form-reply' : '';
?>

<div class="comment-form <?= $replyClass ?> clearfix">

    <?php
    $form = ActiveForm::begin([
        'action' => NULL,
        'validateOnBlur' => FALSE,
        'validationUrl' => Url::to(['/' . Comments::getInstance()->commentsModuleID . '/validate/index']),
        'id' => $formID,
        'class' => 'com-form'
    ]);

    if ($comment->parent_id) {
        echo $form->field($comment, 'parent_id')->hiddenInput()->label(false);
    }
    ?>
    <?php if (Comments::getInstance()->displayAvatar): ?>
        <div class="avatar">
            <img src="<?= Comments::getInstance()->renderUserAvatar(Yii::$app->user->id) ?>"/>
        </div>
    <?php endif; ?>
    <div class="comment-fields<?= (Comments::getInstance()->displayAvatar) ? ' display-avatar' : '' ?>">

        <div class="row">
            <div class="col-lg-<?= $col12 ?>">
                <?= $form->field($comment, 'content')->textarea([
                    'class' => 'uk-textarea',
                    'rows' => 3,
                    'style' => 'resize: none;',
                    'placeholder' => Comments::t('comments', 'Share your thoughts...')
                ])->label(false) ?>
            </div>
        </div>

        <div class="row comment-fields-more">
            <div class="col-lg-<?= $col12 ?>">
                <div class="buttons text-right">
                    <?= Html::button(Comments::t('comments', 'Cancel'), ['class' => 'uk-button uk-button-default reply-cancel  uk-button-small']) ?>
                    <?= Html::submitButton(($comment->parent_id) ? Comments::t('comments', 'Reply') : Comments::t('comments', 'Post'), ['class' => 'uk-button uk-button-primary uk-button-small', 'disabled' => true]) ?>
                </div>
                <div class="fields uk-margin-small-top">
                    <div class="uk-grid uk-grid-small">
                        <?php if (Yii::$app->user->isGuest): ?>
                            <div class="uk-width-1-2">
                                <?= $form->field($comment, 'username', ['enableClientValidation' => false, 'enableAjaxValidation' => true])->textInput([
                                    'maxlength' => true,
                                    'class' => 'uk-form-small uk-input',
                                    'placeholder' => Comments::t('comments', 'Your name')
                                ])->label(false) ?>
                            </div>
                            <div class="uk-width-1-2">
                                <?= $form->field($comment, 'email')->textInput([
                                    'maxlength' => true,
                                    'email' => true,
                                    'class' => 'uk-form-small uk-input',
                                    'placeholder' => Comments::t('comments', 'Your email')
                                ])->label(false) ?>
                            </div>
                        <?php /*
                            <div class="col-lg-<?= $col4 ?>">
                                <?= $form->field($comment, 'verifyCode', ['enableClientValidation' => false, 'enableAjaxValidation' => true])->widget(Captcha::className(), [
                                    //'captchaAction' => Url::to(['/site/captcha']),
                                    'template' => '{image}{input}',
                                ])->label(false) ?>
                            </div>
                         */ ?>
                        <?php else: ?>
                            <div class="uk-width-1-2">
                                <?= (($comment->parent_id) ? Comments::t('comments', 'Reply as') : Comments::t('comments', 'Post as')) . ' <b>' . Yii::$app->user->identity->username . '</b>'; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
//if (Yii::$app->getRequest()->post()) {
//$options    = Json::htmlEncode($form->getClientOptions());
//$attributes = Json::htmlEncode($form->attributes);
//\yii\widgets\ActiveFormAsset::register($this);
//$this->registerJs("jQuery('#$formID').yiiActiveForm($attributes, $options);");
//}

$js = <<<JS
    $(document)
    .on('keyup', '.comment-form textarea', function()
    {
        if($(this).val().length > 5){
            $(this).closest('.comment-form').find('button[type="submit"]').removeAttr('disabled');
        } else {
            $(this).closest('.comment-form').find('button[type="submit"]').attr('disabled', true);
        }
    });

JS;
if(!Yii::$app->request->isAjax){
    $this->registerJs($js, $this::POS_READY);
}
