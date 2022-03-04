<html>
    <head>
        <title><?php echo $this->info['titulo'];?></title>
        <meta name="description" content="<?php echo $this->info['desc'];?>">
        <meta name="keywords" content="<?php echo $this->info['tags'];?>">
        <meta name="author" content="matwcode">
        <link rel="shortcut icon" href="<?php echo BASE ?>favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
        <link rel="stylesheet" type="text/css" href="<?php echo BASE;?>Views/style/style.css">
        <link rel="stylesheet" type="text/css" href="<?php echo BASE;?>Views/style/slick.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
        <BASE base="<?php echo BASE?>">
        <script src="https://kit.fontawesome.com/dc1f02ef1e.js" crossorigin="anonymous"></script>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-T11Z8QM230"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-T11Z8QM230');
        </script>
    </head>
    <body>
        <div class="page-bg"></div>
        <div class="animation-wrapper">
            <div class="particle particle-1"></div>
            <div class="particle particle-2"></div>
            <div class="particle particle-3"></div>
            <div class="particle particle-4"></div>
        </div>
        <div class="contentSite">
        <div class="center">
            <div class="container-main flex-center" style="min-height: 500px;border: 3px solid var(--border-color);border-radius: 3px;">
                <div class="flex-center" style="flex-direction:column; max-width:400px; text-align:center;">
                    <div class="w100 flex-center" style="margin:10px 0;"><i class="fas fa-ban" style="margin: 0 auto;font-size:100px;"></i></div>
                    <p>Informamos que sua conta foi permanentemente banida do nosso site e você não terá mais acesso a ela. 😢😢</p>
                    <span style="font-size:10px; margin: 10px 0;">Acredita que cometemos um erro? entre no nosso <a href="https://discord.gg/4aSCSM6">discord</a> e justifique o que aconteceu.</span>
                </div>
            </div>
        </div>
        </div>
        <footer>
            <div class="center" style="padding: 2% 6.5%; position:relative; bottom:0;left:0;">
                    <div class="body">
                        <div class="col">
                                <div class="deadbear">
                                    <div class="logo left" style="margin:0"></div>
                                    <span>DEADBEAR.CLUB</span>
                                </div>
                                <span>Nos acompanhe nas redes sociais<br>
                                e fique por dentro das promoções.</span>
                        </div>
                        
                        <div class="col">
                                <span>Suporte</span>
                                <a href="<?php echo BASE?>discord"><span>Discord</span></a>
                                <a href="<?php echo BASE?>instagram"><span>Instagram</span></a>
                                <a href="<?php echo BASE?>termos"><span>Twitter</span></a>
                        </div>
                    </div>
            </div>
        </footer>
    </body>
</html>