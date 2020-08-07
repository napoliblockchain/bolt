<div class="form">
<?php
Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );


// Telegram variables
$settings = Settings::load();
$signup = Yii::app()->createUrl('site/signup'); // sign up new user
$URLRecoveryPassword = Yii::app()->createUrl('site/recoverypassword');
$URLContactForm = Yii::app()->createUrl('site/contactForm');

include ('js_login.php');
include ('js_google.php');
include ('js_facebook.php');
// include ('js_twitter.php');


require_once Yii::app()->params['libsPath'] . '/OAuth/oauth-telegram/login.php';
$checkTelegramAuthorization = Yii::app()->createUrl('telegram/CheckAuthorization');
$bot_username = Settings::load()->telegramBotName;
$bot_token = Settings::load()->telegramToken;

require_once Yii::app()->params['libsPath'] . '/OAuth/oauth-google/login.php';
$checkGoogleAuthorization = Yii::app()->createUrl('google/CheckAuthorization');

require_once Yii::app()->params['libsPath'] . '/OAuth/oauth-fb/login.php';
$facebookAppID = $settings->facebookAppID;
$facebookAppVersion = $settings->facebookAppVersion;
$sourceLanguage = explode('_',Yii::app()->sourceLanguage);
$lingua = $sourceLanguage[0];
$paese = strtoupper($sourceLanguage[1]);


$this->pageTitle=Yii::app()->name . ' - Login';
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));

?>
<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
			<div class="input-group">
				<div class="input-group-btn">
					<div class="btn-group">
						<div class="row form-group">
							<div class="col col-md-12">
								<div class="input-group">
									<div class="input-group-btn">
										<div class="btn-group">
											<button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle btn btn-primary"><?php echo Yii::t('lang','Language'); ?></button>
											<div tabindex="-1" aria-hidden="true" role="menu" class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
												<a class="input-group float-right" href="index.php?r=site/changelanguage&lang=it">
													<button type="button" tabindex="0" class="dropdown-item"><?php echo Yii::t('lang','Italian'); ?></button>
												</a>
												<a class="input-group float-right" href="index.php?r=site/changelanguage&lang=en">
													<button type="button" tabindex="0" class="dropdown-item"><?php echo Yii::t('lang','English'); ?></button>
												</a>
											</div>
										</div>
									</div>

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<!-- <a href="https://bitbucket.org/jambtc/bolt/issues/new" target="_blank"> -->
					<a href="<?php echo $URLContactForm; ?>" target="_blank">
						 <?php echo Yii::t('lang','Did you discover a bug? Please compile this form.');?></a>
				</div>
			</div>

			<div class="form-group">
				<p id="privateBrowsingAlert" class="alert alert-danger" style="display:none;"></p>
			</div>

		</div>

		<div class="login-form">
			<!-- BUGFIX x Chrome che riempie in automatico i campi successivi -->
			<input style="display:none">
			<input type="password" style="display:none">
			<?php echo $form->hiddenField($model,'oauth_provider',array('value'=>'email')); ?>

			<!-- end bugfix -->
				<div class="form-group">
					<div class="input-group">
                        <div class="input-group-addon">
							<img style="height:25px;" src="css/images/ic_account_circle.svg">
                        </div>
						<?php echo $form->textField($model,'username',array('placeholder'=>Yii::t('lang','Email address'),'class'=>'form-control','style'=>'height:45px;','value'=>'')); ?>

					</div>
					<?php echo $form->error($model,'username',array('class'=>'alert alert-danger')); ?>
				</div>
				<div class="form-group">
					<div class="input-group">
                        <div class="input-group-addon">
							<img style="height:25px;" src="css/images/ic_vpn_key.svg">
                        </div>
						<?php echo $form->passwordField($model,'password',array('placeholder'=>Yii::t('lang','Password'),'class'=>'form-control','style'=>'height:45px;','value'=>'')); ?>

                    </div>
					<?php echo $form->error($model,'password',array('class'=>'alert alert-danger')); ?>
				</div>
				<div class="form-group">
					<div class="input-group">
						<a href="<?php echo $URLRecoveryPassword; ?>"><?php echo Yii::t('lang','Forget password?'); ?></a>
	                </div>
				</div>


				<?php echo CHtml::submitButton(Yii::t('lang','sign in'), array('class' => 'au-btn au-btn--block au-btn--blue m-b-20','id'=>'accedi-button')); ?>

				<div class="social-signin-div"><div class="social-divider"><span><?php echo Yii::t('lang','or'); ?></span></div></div>

				<div class="form-group">

						<center>
							<div class="row">
								<div class="col col-lg-6">
									<button id='facebook-login-button' dis abled type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalFacebook" style="min-width:120px; max-height:46px;">
										<img src="css/images/facebook.svg" alt="Facebook" width="32" height="32"><span style="margin-left:12px;">Facebook</span>
									</button>
								</div>
								<div class="col col-lg-6">
									<button id='telegram-login-button' type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTelegram" style="min-width:120px; max-height:46px;">
										<img src="css/images/telegram.svg" alt="Telegram" width="32" height="32"><span style="margin-left:12px;">Telegram</span>
									</button>
								</div>
							</div>

							<div class="row">
								<div class="col col-lg-6">
									<button id='google-login-button' dis abled onclick='resetGoogleCookies();' type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalGoogle" style="min-width:125px; max-height:46px; margin-top: 15px;">
										<img src="css/images/google.svg" alt="Google" width="32" height="32"><span style="margin-left:12px;">Google</span>
									</button>
								</div>
								<div class="col col-lg-6">
									<!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTwitter" style="min-width:125px; max-height:46px; margin-top: 15px;">
										<img src="css/images/twitter.svg" alt="Twitter" width="32" height="32"><span style="margin-left:12px;">Twitter</span>
									</button> -->
									<div class="input-group">
										<?php //echo Yii::t('lang','Don\'t have an account yet?');?>
										<!-- <span style="margin-left:20px;">
											<a href="<?php echo $signup; ?>">
												<button type="button" class="btn btn-primary" style="min-width:125px; max-height:46px; margin-top: 15px;">
													<?php echo Yii::t('lang','Sign Up!'); ?>
												</button>

											</a>
										</span> -->
					                </div>
								</div>
							</div>

							<div class="row">
								<div class="col col-lg-6">
									<p style="margin-top:14px;"><?php echo Yii::t('lang','Don\'t have an account yet?');?></p>
								</div>
								<div class="col col-lg-6">
										<a href="<?php echo $signup; ?>">
											<button type="button" class="btn btn-primary" style="min-width:125px; max-height:46px; margin-top: 15px;">
												<?php echo Yii::t('lang','Sign Up!'); ?>
											</button>
										</a>
				                </div>
							</div>
						</center>

				</div>






				<div class="form-group">
					<div class="input-group">
						<span><a href="https://www.iubenda.com/privacy-policy/7935688"><?php echo Yii::t('lang','Read our Privacy policy'); ?></a></span>
					</div>
				</div>



				<?php echo Logo::footer(); ?>
		</div>

	</div>
</div>
<!-- Twitter access -->
<div class="modal fade" id="modalTwitter" tabindex="-1" role="dialog" aria-labelledby="TwitterModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content alert-primary">
			<div class="modal-header">
				<div class="modal-title text-light" id="TwitterModalLabel"><?php echo Yii::t('lang','Sign in with Twitter'); ?></div>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<center>
						<!-- Display Twitter sign-in button -->
						<a href='#' onclick='twitterAuth();' >
						<button class='btn btn-primary'>Login with twotter</button>
						</a>
				</center>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Google access -->
<div class="modal fade" id="modalGoogle" tabindex="-1" role="dialog" aria-labelledby="GoogleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content alert-primary">
			<div class="modal-header">
				<div class="modal-title text-light" id="GoogleModalLabel"><?php echo Yii::t('lang','Sign in with Google'); ?></div>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<center>
						<?php
						$loginGoogle = new \jambtc\google\Login($checkGoogleAuthorization);
						echo $loginGoogle->loginButton();
						?>
					</center>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Facebook access -->
<div class="modal fade" id="modalFacebook" tabindex="-1" role="dialog" aria-labelledby="FacebookModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content alert-primary">
			<div class="modal-header">
				<div class="modal-title text-light" id="FacebookModalLabel"><?php echo Yii::t('lang','Sign in with Facebook'); ?></div>
			</div>
			<div class="modal-body">
				<div class="form-group" style="width:100%;">
					<center>
						<?php
						$loginFB = new \jambtc\facebook\Login($facebookAppID,$facebookAppVersion,$lingua,$paese);
						echo $loginFB->loginButton();
						?>
					<!-- <fb:login-button
						class="fb-login-button"
						data-size="large"
						data-button-type="login_with"
						data-use-continue-as="true"
  					scope="public_profile,email"
  					onlogin="checkLoginState();">
					</fb:login-button> -->

					<!-- <div class="socialResponseData" class='text-light'></div> -->

				</center>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Telegram access -->
<div class="modal fade" id="modalTelegram" tabindex="-1" role="dialog" aria-labelledby="TelegramModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content alert-primary">
			<div class="modal-header">
				<div class="modal-title text-light" id="TelegramModalLabel"><?php echo Yii::t('lang','Sign in with Telegram'); ?></div>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<center>
						<?php
						$loginTelegram = new \jambtc\telegram\Login($bot_username,$bot_token);
						echo $loginTelegram->loginButton($checkTelegramAuthorization,'large');
						?>
						<!-- <script async src="https://telegram.org/js/telegram-widget.js?2" data-telegram-login="<?php echo $bot_username; ?>" data-size="large" data-onauth="onTelegramAuth(user)" data-request-access="write"></script> -->
						<!-- <div class="socialResponseData" class='text-light'></div> -->
					</center>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- RICHIESTA 2FA -->
<div class="modal fade" id="2faModal" tabindex="-1" role="dialog" aria-labelledby="2faModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content alert-dark text-light">
			<div class="modal-header">
				<h3 class="modal-title" id="2faModalLabel"><?php echo Yii::t('lang','Two factors authentication'); ?></h3>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="input-group">
            <div class="input-group-addon">
              <img style="height:25px;" src="css/images/ic_account_google2fa.png">
            </div>
						<?php echo $form->numberField($model,'ga_cod',array('placeholder'=>Yii::t('lang','Google 2FA'),'class'=>'form-control','style'=>'height:45px;')); ?>
          </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo Yii::t('lang','cancel'); ?></button>
				<button type="button" class="btn btn-primary" id='Conferma2fa' style="min-width:90px;"><?php echo Yii::t('lang','confirm'); ?></button>
			</div>
		</div>
	</div>
</div>
<?php $this->endWidget(); ?>
</div><!-- form -->
