<?php
include ('js_pin.php');
include ('js_cgridview.php');
include ('js_add.php');

$search = $model->search();
if (strlen($model->username) <3 && (!isset($_GET['id']))){
  $criteria = new CDbCriteria();
  	$criteria->compare('id_social',0,false);
   $search = new CActiveDataProvider('Socialusers', array(
     'criteria'=>$criteria,
     'pagination' => array(
       'pageSize' => 10,
     ),
   ));
}

?>
<style>
table.items {
    width: 100%;
}
</style>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row search-form">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad bg-overlay--semitransparent">
					<div class="card-body card-block">
						<?php $this->renderPartial('_search', array('model'=>$model)); ?>
					</div>
				</div>
			</div>
		</div><!-- search-form -->

		<div class="row">
			<div class="col-lg-12">
				</br></br>
				<div class="au-card au-card--no-shadow au-card--no-pad bg-overlay--semitransparent">
					<div class="card-header text-light">
						<i class="fa fa-users"></i> <?php echo Yii::t('lang','Contacts Found');?>
					</div>
					<div class="card-body card-block">
						<div class="table-responsive table--no-card m-b-40">
							<?php

							$widget = $this->widget('zii.widgets.grid.CGridView', array(
								'id' => 'contacts-grid',
								'htmlOptions' => array('class' => 'table table-borderless table-data4 table-wallet text-light'),
								'hideHeader' => true,
								'ajaxUpdate'=> true, //'contacts-grid',
								//'ajaxType' => 'GET',
								'afterAjaxUpdate' => 'stopPropagation("contacts-grid",true)', //funzinoe da richiamare dopo update
								'dataProvider' => $search,
								//'filter' => $model,
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
									array(
										'name'=>'',
										'type' => 'raw',
										//'value'=>'"<div class=\"account-item clearfix \">". webRequest::checkUrl( Socialusers::model()->findByPk($data->id_social)->picture ) ? CHtml::image(Socialusers::model()->findByPk($data->id_social)->picture : CHtml::image("css/images/anonymous.png"), "img",array("class"=>"image img-cir img-120")) ."</div>" . CHtml::image("css/images/".Socialusers::model()->findByPk($data->id_social)->oauth_provider.".svg", "img", array("class"=>"imgProvider image img-cir img-40"))',
                    'value'=>'"<div class=\"account-item clearfix \">". CHtml::image(webRequest::checkUrl( Socialusers::model()->findByPk($data->id_social)->picture ) ? Socialusers::model()->findByPk($data->id_social)->picture : "css/images/anonymous.png", "img",array("class"=>"image img-cir img-120")) . "</div>". CHtml::image("css/images/".Socialusers::model()->findByPk($data->id_social)->oauth_provider.".svg", "img", array("class"=>"imgProvider image img-cir img-40"))',
										'htmlOptions'=>array('style'=>'max-width: 60px; max-height:45px;'),
									),
									array(
										'name'=>'',
										'type' => 'raw',
										'value'=>'isset($data->first_name) ? $data->first_name ." ". $data->last_name ."<br><small class=\"text-secondary\">". $data->username ."</small>" : $data->email',
									),
									array(
										'name'=>'',
										'type' => 'raw',
										'value' => 'WebApp::addButton($data->id_social)',
										'htmlOptions'=>array('style'=>'width:80px;')
									),
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
<!-- VISUALIZZA NEW USER -->
<div class="modal fade " id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content alert-primary text-light ">
			<div class="modal-header">
				<h5 class="modal-title" id="addUserModalLabel"><?php echo Yii::t('lang','Add contact');?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="col-lg-12">
					<div class="card bg-transparent">
						<div class="card-header">
							<strong class="card-title mb-3"><?php echo Yii::t('lang','Profile Card');?></strong>
						</div>
						<div class="card-body">
							<div class="mx-auto d-block">
                            	<div class="account-item clearfix">
                                    <div class="image img-cir img-120" style="height:90px; width:90px;">
                                        <img id="avatarImage" class="ro unded-circle mx-auto d-block avatar-image" src="" alt="<?php echo Yii::t('lang','Card image');?>" width="100" height="100">
                                      </div>
                                    <img style="position:relative; width:30px; top:55px; left:-23px;" id="avatarProvider" src="" alt="image" class="imgProvider image img-cir img-40">
                                    <span style="position: relative; ">
                                      <h5 id="avatarName" class="mt-2 mb-1"></h5>
                                      <p id="avatarUsername"></p>
                                      <!-- <p id="avatarEmail"></p> -->
                                    </span>

                              </div>
								 <hr>
								<div class="location text-center text-success">
                                    <p style="word-wrap: break-word;" id="avatarAddress"></p>
  							     </div>
								<input type='hidden' id='avatarUserId' value="">
								<input type='hidden' id='avatarTGUserId' value="">
							</div>
						</div>
                    </div>
                </div>
            </div>


			<div class="modal-footer">
				<div class="form-group">
                    <button type="button" class="btn alert-secondary text-light" data-dismiss="modal" style="min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;">
                        <i class="fa fa-backward"></i> <?php echo Yii::t('lang','back');?>
                    </button>
					<button name="addUserButton" type="button" class="btn alert-success text-light" id="addUserButton" style="min-width: 100px; padding:2.5px 10px 2.5px 10px; height:30px;">
                        <i class="fa fa-user"></i> <?php echo Yii::t('lang','Add');?>
                    </button>

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
