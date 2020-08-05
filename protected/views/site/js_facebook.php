<?php
$checkFacebookAuthorization = Yii::app()->createUrl('facebook/CheckAuthorization');

$facebook = <<<JS

	// Fetch the user profile data from facebook
	function getFbUserData(){
	    FB.api('/me', {locale: 'en_US', fields: 'id,first_name,last_name,email,link,gender,locale,picture'},
	    function (user) {
			console.log('[FB userData]', user);

			$.ajax({
				url:'{$checkFacebookAuthorization}',
				type: "POST",
				data:{
					'email'		: user.email,
					'first_name': user.first_name,
					'last_name'	: user.last_name,
					'id'		: user.id,
					'username'	: user.first_name+'.'+user.last_name,
					'picture'	: user.picture.data.url,
				},
				dataType: "json",
				success:function(data){
					console.log('FB Saving userdata',data);
                    //$('.socialResponseData').html("<pre>"+JSON.stringify(data,null,' ')+"</pre>");
					if (data.success){
						$("#LoginForm_username").hide();
            $("#LoginForm_password").hide();
						$("#LoginForm_username").val(data.email);
						$("#LoginForm_password").val(data.id);
            $("#LoginForm_oauth_provider").val(data.oauth_provider);
						//$('#login-form').submit();
            check2fa(data.email,data.oauth_provider);
					}
				},
				error: function(j){
					console.log(j);
				}
			});


	        // Save user data
	        //saveUserData(response);
	    });
	}


JS;
Yii::app()->clientScript->registerScript('facebook', $facebook, CClientScript::POS_END);
?>
