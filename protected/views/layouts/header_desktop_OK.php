
<header class="header-desktop">
  <div id='poa-pulse'>
    <button class="pulse-button" ></button>
  </div>
    <div class="section__content section__content--p30">
        <div class="container-fluid">
            <div class="header-wrap">
			<?php if (!Yii::app()->user->isGuest) { ?>
                <form class="form-header" action="" method="POST">
                    <div id="orologio"></div>
                </form>
                <div class="header-button">
                    <div class="noti-wrap">
                        <?php  include ('header_notify.php'); ?>
                    </div>

                    <div class="account-wrap">
                        <div class="account-item clearfix js-item-menu">
                            <div class="content">
                                <a class="js-acc-btn" href="#">
                                  <?php
                                  echo (Yii::app()->user->objUser['name'] <> '' ? Yii::app()->user->objUser['name'] : Yii::app()->user->objUser['username'])
                                  ?>
                                </a>
                            </div>
                            <div class="account-dropdown js-dropdown">
                                <div class="info clearfix">
                                    <!-- <div class="image">
                                        <a href="#"><i class="fa fa-user" style="font-size: 4em;"></i></a>
                                    </div>
                                    <div class="content">
                                        <h5 class="name">
                                            <?php echo Yii::app()->user->objUser['name'].chr(32).Yii::app()->user->objUser['surname']; ?>
                                        </h5>
                                        <span class="email"><?php echo Yii::app()->user->name; ?></span>
                                    </div> -->
                                    <?php //include 'header_account.php'; ?>
                                </div>
                                <div class="account-dropdown__body">
                                    <?php if (Yii::app()->user->objUser['facade'] != 'pos'){ ?>
                                        <div class="account-dropdown__item">
                                            <a href="<?php echo Yii::app()->createUrl('users/view').'&id='.crypt::Encrypt(Yii::app()->user->objUser['id_user']);?>">
                                                <i class="zmdi zmdi-face" style="font-size: 1.5em;"></i></i><?php echo Yii::t('lang','Profile');?></a>
                                        </div>
                                    <?php } ?>



                                    <div class="account-dropdown__item" >
                    						<a href="<?php echo Yii::app()->createUrl('settings/index',array('id'=>crypt::Encrypt(Yii::app()->user->objUser['id_user'])));?>">
                    							 <i class="fa fa-gear"></i><?php echo Yii::t('lang','Settings');?></a>
                                    </div>

                                </div>
                                <div class="account-dropdown__footer delete-serviceWorker">
                                    <a href="<?php echo Yii::app()->createUrl('site/logout');?>" >
            							 <i class="fa fa-power-off"></i><?php echo Yii::t('lang','Logout');?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
			<?php } ?>
            </div>
        </div>
    </div>
</header>
