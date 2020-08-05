
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
                        <div class="account-item clearfix js-item-menu"></div>
                    </div>
                </div>
			<?php } ?>
            </div>
        </div>
    </div>
</header>
