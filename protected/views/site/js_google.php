<?php

$resetGoogleCookies = Yii::app()->createUrl('google/resetCookies');

$google = <<<JS
// Reset cookies for fresh google login
function resetGoogleCookies() {
    $.get('{$resetGoogleCookies}');
}




JS;
Yii::app()->clientScript->registerScript('google', $google, CClientScript::POS_END);
?>
