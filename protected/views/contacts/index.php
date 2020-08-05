
<?php
include ('js_pin.php');
include ('js_cgridview.php');
include ('js_delete.php');
?>
<style>
table.items {
    width: 100%;
}
.account-item{
	min-width: 120px;
}
</style>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
        <?php //include ('_search.php'); ?>

				<div class="au-card au-card--no-shadow au-card--no-pad bg-overlay--semitransparent">
					<div class="card-header text-light">
						<i class="fa fa-users"></i>
						<span class="card-title"><?php echo Yii::t('lang','Contacts');?></span>
						<div class="float-right">
							<?php $actionURL = Yii::app()->createUrl('contacts/add'); ?>
							<a href="<?php echo $actionURL;?>">
								<button class="btn alert-success text-light img-cir" style="padding:2.5px; width:30px; height:30px;">
									<i class="fa fa-plus  "></i></button>
							</a>
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card m-b-40">
							<?php

							$this->widget('zii.widgets.grid.CGridView', array(
                                'id' => 'contacts-grid',
								'htmlOptions' => array('class' => 'table table-borderless table-data4 table-wallet text-light'),
								'hideHeader' => true,
								'dataProvider'=>$dataProvider,
                                'enableSorting'=>true,
                                'pager'=>array(
                            			//'header'=>'Go to page:',
                            			//'cssFile'=>Yii::app()->theme->baseUrl
                            			'cssFile'=>Yii::app()->request->baseUrl."/css/yiipager.css",
                            			'prevPageLabel'=>'<',
                            			'nextPageLabel'=>'>',
                            			'firstPageLabel'=>'<<',
                            			'lastPageLabel'=>'>>',
                            	),
								'columns' => array(
									// array(
									// 	// 'name'=>'',
									// 	// 'type' => 'raw',
									// 	// 'value'=>'"<div id=\"contact_01_".$data->id_social."\" class=\"deleteContact account-item clearfix \">". CHtml::image("css/images/".Socialusers::model()->findByPk($data->id_social)->oauth_provider.".svg", "img", array("class"=>"image img-cir img-40")) ."</div>" ',
									// 	// 'htmlOptions'=>array('style'=>'max-width: 60px;'),
									// ),
									array(
										'name'=>'',
										'type' => 'raw',
                    'value'=>'webRequest::checkUrl( $data->picture ) ? "<div id=\"contact_02_".$data->id_social."\" class=\"deleteContact account-item clearfix \">". CHtml::image($data->picture, "img",array("class"=>"image img-cir img-120")) ."</div>" . CHtml::image(Yii::app()->request->baseUrl."/css/images/".$data->oauth_provider.".svg", "img", array("class"=>"imgProvider image img-cir img-40")) : "<div id=\"contact_02_".$data->id_social."\" class=\"deleteContact account-item clearfix \">". CHtml::image("css/images/anonymous.png", "img",array("class"=>"image img-cir img-120")) ."</div>" . CHtml::image(Yii::app()->request->baseUrl."/css/images/".$data->oauth_provider.".svg", "img", array("class"=>"imgProvider image img-cir img-40"))',
										'htmlOptions'=>array('style'=>'max-width: 60px; max-height:45px;'),
									),
									array(
										'name'=>'Name',
										'type' => 'raw',
										//'value'=>'"<div name=\"deleteContact\" class=\"alert alert-primary account-item\" id=\"contact_tg_".$data->id."\" >".$data->first_name ." ". $data->last_name ." <small class=\"text-success\">". $data->username ."</small></div>"',
										'value'=>'"<div id=\"contact_03_".$data->id_social."\" class=\"deleteContact account-item clearfix\">".$data->first_name ." ". $data->last_name ."<br><small class=\"text-secondary\">". $data->username ."</small></div>"',
									),
									[
										'name'=>'',
										'value' =>'',
										'htmlOptions'=>array('style'=>'width: 10%;'),
										]
								)
							));
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php echo Logo::footer(); ?>
	</div>
</div>
<!-- SHOW USER -->
<div class="modal fade " id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg " role="document">
		<div class="modal-content bg-transparent text-light modal-no-border">
			<div class="modal-body">
				<div class="col-lg-12">
					<div class="card alert-primary">
						<div class="card-header text-light">
							<strong class="card-title mb-3"><?php echo Yii::t('lang','Profile Card');?></strong>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            					<span aria-hidden="true">×</span>
            				</button>
						</div>
						<div class="card-body">
							<div class="mx-auto d-block">
                                <div class="account-item clearfix">
                                    <div class="image img-cir img-120" style="height:90px; width:90px;">
                                        <img id="avatarImage" class="ro unded-circle mx-auto d-block avatar-image" src="" alt="<?php echo Yii::t('lang','Card image');?>" width="100" height="100">
                                    </div>
                                    <span style="position: relative; left:20px;">
                                        <h5 id="avatarName" class="mt-2 mb-1"></h5>
                                        <p id="avatarUsername"></p>
                                    </span>
                                    <img style="position:relative; width:30px; top:1px; left:-18px;" id="avatarProvider" src="" alt="image" class="imgProvider image img-cir img-40">
                                </div>
								<hr>

								<div class="location text-center text-success">
                                    <p style="word-wrap: break-word;" id="avatarAddress"></p>
  							    </div>
                                <div class="form-group">
                                    <center>
                                    <a id='linkToAvatarAddress' href='#'>
                                        <button type="button" class="btn alert-primary text-light" style="min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;">
                                            <i class="fa fa-upload"></i> <?php echo Yii::t('lang','Send');?>
                                        </button>
                					</a>

                                    <?php
                                    // BETA TESTER CHECK BALANCE id:
                                    // 6: balsamo (telegram)
                                    // 20: casizzone (google)
                                    // 21: schiattarella (google)
                                    // 23: chiacchio (google)
                                    if (
                                        Yii::app()->user->objUser['id_user'] == 6
                                        || Yii::app()->user->objUser['id_user'] == 19
                                        || Yii::app()->user->objUser['id_user'] == 20
                                        || Yii::app()->user->objUser['id_user'] == 21
                                        || Yii::app()->user->objUser['id_user'] == 23
                                    ){
                                        ?>
                                        <a id='linkToCheckBalances' href='#'>
            								<button class="btn alert-danger btn-sm" >Balance</button>
            							</a>
                                    <?php
                                    }
                                    ?>
                                    </center>
                                </div>
							</div>
						</div>
                        <div class="card-footer">
            				<div class="form-group">
                                <center>
                                    <button type="button" class="btn alert-secondary text-light" data-dismiss="modal" style="min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;">
                    					<i class="fa fa-backward"></i> <?php echo Yii::t('lang','back');?>
                    				</button>

            					<a id="deleteUserButton" href="#">
                                    <button type="button" class="btn alert-warning text-light" data-dismiss="modal" style="min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;">
                    					<i class="fa fa-eraser"></i> <?php echo Yii::t('lang','Remove');?>
                    				</button>
            					</a>
                                </center>
            				</div>
            			</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
<!-- RICHIESTA PIN -->
<div class="modal fade " id="pinRequestModal" tabindex="-1" role="dialog" aria-labelledby="pinRequestModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm" role="document">
		<div class="modal-content alert-dark text-light " style="min-width:360px;">
			<div class="modal-header">
				<h5 class="modal-title" id="pinRequestModalLabel"><?php echo Yii::t('lang','PIN Request');?></h5>
			</div>
			<div class="modal-body ">
				<center>
					<input type='hidden' id='pin_password' class='form-control' readonly="readonly"/>
                    <input type='hidden' id='pin_password_confirm' class='form-control' readonly="readonly"/>
                </center>
                <div class="pin-confirm-numpad pin-newframe-numpad"></div>
			</div>
			<div class="modal-footer">
				<div class="form-group">
                    <button type="button" disabled="disabled" class="btn alert-primary disabled text-light" id="pinRequestButton" style="min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;">
						<i class="fa fa-thumbs-up"></i> <?php echo Yii::t('lang','Confirm');?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
