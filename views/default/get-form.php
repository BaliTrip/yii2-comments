<?php

use common\modules\comments\Comments;
use common\modules\comments\widgets\CommentsForm;

?>

<?php if (!Comments::getInstance()->onlyRegistered || !Yii::$app->user->isGuest): ?>
    <?= CommentsForm::widget(compact('reply_to', 'lang')) ?>
<?php endif; ?>