<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Bolt Wallet">
    <meta name="author" content="Bolt Wallet">
    <meta name="keywords" content="Bolt Wallet">

    <!-- Progressive Web App -->
        <link rel="manifest" href="<?php echo Yii::app()->request->baseUrl; ?>/manifest.json">

        <!-- ORIGIN TRIAL NFC -->
        <meta http-equiv="origin-trial" content="Aiv30KSa/JcjasZLqElHmU6GmDxhAhbApu8MsRCRS0SzZdcFKhj6hyGAzTpqmRitjkaL96aa/TQSlN4iQ5nfYQoAAABHeyJvcmlnaW4iOiJodHRwOi8vbG9jYWxob3N0OjgwIiwiZmVhdHVyZSI6IldlYk5GQyIsImV4cGlyeSI6MTU4NTU3NDY0M30=">

        <!-- iOS -->
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="<?php echo CHtml::encode($this->pageTitle); ?>">
        <link rel="apple-touch-icon" href="<?php echo Yii::app()->request->baseUrl; ?>/src/images/icons/apple-icon-76x76.png" sizes="76x76">
        <link rel="apple-touch-icon" href="<?php echo Yii::app()->request->baseUrl; ?>/src/images/icons/apple-icon-144x144.png" sizes="144x144">

        <!-- iExplorer -->
        <meta name="msapplication-TileImage" content="<?php echo Yii::app()->request->baseUrl; ?>/src/images/icons/apple-icon-144x144.png" sizes="144x144">
        <meta name="msapplication-TileColor" content="#fff">
        <meta name="theme-color" content="#3f51b5">

    <!-- Title Page-->
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<link rel="icon" href="<?php echo Yii::app()->request->baseUrl; ?>/css/favicon.ico" type="image/x-icon" />

    <!-- Fontfaces CSS-->
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/css/font-face.css" rel="stylesheet" media="all">
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">

    <!-- Vendor CSS-->
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet" media="all">
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/select2/select2.min.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/css/theme.css" rel="stylesheet" media="all">

    <!-- NUMPAD -->
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/css/numpad.css" rel="stylesheet" media="all" >

    <!-- NEW CSS-->
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/css/wallet.css" rel="stylesheet" media="all" >
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/css/jamburger.css" rel="stylesheet" media="all" >

    <!-- Bootstrap CSS from bootswatch.com -->
    <link href="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/css/solar.css" rel="stylesheet" media="all">

    <!-- Jquery JS-->
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/chartjs/Chart.bundle.min.js"></script>


</head>

<body class="animsition">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
            <?php include ('header_mobile.php'); ?>
        <!-- END HEADER MOBILE-->

        <!-- MENU SIDEBAR-->
            <?php
			include ('menu_aside.php');
			?>
        <!-- END MENU SIDEBAR-->

        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <!-- HEADER DESKTOP-->
            <?php
            if (!Yii::app()->user->isGuest)
                include ('header_desktop.php');
            ?>
            <!-- HEADER DESKTOP-->

            <!-- MAIN CONTENT-->
            <div class="main-content">
                <?php echo $content; ?>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>

    </div>



    <!-- Bootstrap JS-->
    <!-- <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/bootstrap-4.1/popper.min.js"></script> -->
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/bootstrap-4.3/js/bootstrap.min.js"></script>

    <!-- Vendor JS       -->
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/slick/slick.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/wow/wow.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/animsition/animsition.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/counter-up/jquery.waypoints.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/counter-up/jquery.counterup.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/circle-progress/circle-progress.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/vendor/select2/select2.min.js"></script>

    <!-- Main JS-->
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/themes/cool/js/main.js"></script>

    <!-- call qrcode camera -->
    <!-- <script src="<?php echo Yii::app()->request->baseUrl; ?>/protected/extensions/webcodecamjs-master/js/qrcodelib.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/protected/extensions/webcodecamjs-master/js/webcodecamjs.js"></script> -->

    <!-- Call Ethereum Wallet -->
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/ethjs/lightwallet.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/ethjs/aes.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/ethjs/aes-json-format.js"></script>

    <!-- call numpad -->
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/js/easy-numpad.js"></script>

    <!-- Gestione del Pin -->
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/js/pinutility.js"></script>



    <!-- Call Service Worker-->
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/js/promise.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/js/fetch.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/js/idb.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/js/utility.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/src/js/service.js"></script>

<?php
include ('js_main.php'); // main blockchain
include ('js_backend.php'); // main blockchain
include ('js_sw.php');  // service worker
include ('js_validatepassword.php'); // validate password
?>

<!-- <input type='hidden' id='countedNews' value='<?php //echo (isset($countedNews)) ? $countedNews : 0; ?>'> -->
</body>
</html>
<!-- end document-->
