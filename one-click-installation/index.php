<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width">
	<title>One Click Installation</title>
	<link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/jquery.fancybox-1.3.4.css">
	<link rel="stylesheet" href="css/jquery-ui-1.9.2.custom.min.css">
	<script src="js/modernizr-2.5.3-respond-1.1.0.min.js"></script>
	<script src="js/jquery-1.8.2.min.js"></script>
	<script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.fancybox-1.3.4.pack.js"></script>
	<script src="js/jquery-ui-1.9.2.custom.min.js"></script>
    </script>
</head>
<body>

    <div id="header-container">
        <header id="header">
            <a id="logo" href="index.php"><img src="images/logo.png" /></a>
            <nav id="main-menu">
                <ul>
        	        <li class="active"><a href="index.php">One Click Installation</a></li>
        	        <li><a href="http://www.one-click-forum.com" target="_blank">Visit Product Page</a></li>
        	        <li><a href="http://www.one-click-forum.com/support/" target="_blank">Support Center</a></li>
                    <li><a id="twitter" href="http://twitter.com/creative8dreams" target="_blank"><img src="images/twitter-24.png" /></a></li>
        	        <li><a id="envato" href="http://codecanyon.net/user/CreativeDreams" target="_blank"><img src="images/envato-24.png" /></a></li>
                </ul>
            </nav>
        </header>
    </div>

    <div id="main-container">

        <div id="main" class="box">

            <header>
                <h1>One Click Installation</h1>
            </header>

            <div id="content">
                <?php

                if (version_compare(phpversion(), '5.2.0') < 0){

                    echo '<h3>PHP version too small</h3><p>You are running PHP version '.phpversion().'. Vanilla requires PHP 5.2.0 or greater. You must upgrade PHP before you can continue.</p>';

                }elseif('PHP'=='not installed'){
                ?>

                <p>You need a PHP server to view this page!</p>

                <p>Please go to Documentation > <a href="documentation/01_installation.html">Instalation</a> to see how you can achieve that.</p>

                <?php }else{

                    include_once('installation.php');

                } ?>
            </div>

        </div>

    </div>

</body>
</html>