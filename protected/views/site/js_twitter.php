<?php
Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );


$requestToken = Yii::app()->createUrl('twitter/request_token');


$twitter = <<<JS


function twitterAuth()
{
    event.preventDefault();
    event.stopPropagation();

    $.ajax({
        url:'{$requestToken}',
        type: "POST",
        dataType: "json",
        success:function(data){
            console.log('twitter Saving userdata',data);
            if (data.success){
                //$("#LoginForm_username").hide();
                $("#LoginForm_username").val(data.email);
                $("#LoginForm_password").val(data.id);
                $("#LoginForm_oauth_provider").val(data.oauth_provider);
                //$('#login-form').submit();
                check2fa();
            }
        },
        error: function(j){
            console.log(j);
        }
    });
}





JS;
Yii::app()->clientScript->registerScript('twitter', $twitter, CClientScript::POS_END);
?>
