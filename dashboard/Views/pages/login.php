<html>
    <head>
        <title><?php echo $this->info['titulo'];?></title>
        <meta name="description" content="<?php echo $this->info['desc'];?>">
        <meta name="keywords" content="<?php echo $this->info['tags'];?>">
        <meta name="author" content="matwcode">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="<?php echo BASE;?>dashboard/Views/styles/style.css">
        <link rel="shortcut icon" href="<?php echo BASE;?>favicon.ico">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap" rel="stylesheet">
        <BASE base="<?php echo BASE?>">
        <script src="https://kit.fontawesome.com/dc1f02ef1e.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class="message-alert-box">
            <h2>Notificação</h2>
            <div class="content-Alert"></div>
        </div>
        <div id="fountainG" style="top:0;">
            <div style="position:relative; left:20%; top:0">
                <div id="fountainG_1" class="fountainG"></div>
                <div id="fountainG_2" class="fountainG"></div>
                <div id="fountainG_3" class="fountainG"></div>
                <div id="fountainG_4" class="fountainG"></div>
                <div id="fountainG_5" class="fountainG"></div>
            </div>
        </div>
        <div class="center">
            <div class="container-login">
                <img src="<?php echo BASE?>data/images/logo.webp">
                <h2>Faça login para continuar</h2>
                <form class="ajax" method="post" action="<?php echo BASE?>ajax/Admin.php">
                    <input type="text" name="username" placeholder="login" required />
                    <input type="password" name="password" placeholder="password" required />
                    <input type="hidden" name="type-action" value="login">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['tokenAdmin'] ?>">
                    <button type="submit">Entrar</button>
                </form>
            </div>
        </div>


<?php
      Site::loadJs(
      'admin',
      'user',
      array(''), 
      'jquery'
      );
      Site::loadJs(
      'admin',
      'admin',
      array(''), 
      'functions'
      );
      Site::loadJs(
        'admin',
        'user',
        array(''), 
        'constants'
        );
    Site::loadJs(
        'admin',
        'admin',
        array(''), 
        'adminlogin'
        );        
    
    
?>
</body>
</html>