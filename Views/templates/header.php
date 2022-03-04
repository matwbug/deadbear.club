<html>
    <head>
        <title><?php echo $this->info['titulo'];?></title>
        <meta name="description" content="<?php echo $this->info['desc'];?>">
        <meta name="keywords" content="<?php echo $this->info['tags'];?>">
        <meta name="author" content="matwcode">
        <link rel="shortcut icon" href="<?php echo BASE ?>favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
        
        <link rel="stylesheet" type="text/css" href="<?php echo BASE;?>views/style/style.css">
        <link rel="stylesheet" type="text/css" href="<?php echo BASE;?>views/style/particles.css">
        <link rel="stylesheet" type="text/css" href="<?php echo BASE;?>views/style/materialize.min.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        <BASE base="<?php echo BASE?>">
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-T11Z8QM230"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-T11Z8QM230');
        </script>
<?php
        Site::loadJs('user','user',
            'all', //paginas que o jquery sera carregado
            'jquery' // arquivo que sera carregado
        );  
        Site::loadJs('user','user',
        array('minhaconta'), // paginas que o chat sera carregado
        'jquery.mask' 
        ); 
        Site::loadJs(
                'user',
                'user',
                'all', 
                'menu'
                );   
        Site::loadJs('user','user',
            'all', // paginas que as constants vao ser carregadas
            'constants' 
        );
        Site::loadJs('user','user',
        array('item'), // paginas que as constants vao ser carregadas
        'changeTitle' 
        );
        Site::loadJs('user','user',
        'all', // paginas que as constants vao ser carregadas
        'function' 
        );
        /*
        Site::loadJs('user','user',
        array('','login','registro'), // paginas que as constants vao ser carregadas
        'slick' 
        );
        Site::loadJs('user','user',
        array('','login','registro'), // paginas que as slider vao ser carregadas
        'slider' 
        );
        */
        Site::loadJs('user','user',
        'all', // paginas que o chat sera carregado
        'jquery.form' 
        );
        Site::loadJs('user','user',
        array('chat','chat'), // paginas que o chat sera carregado
        'chat' 
        );
        Site::loadJs('user','user',
        array('carrinho','item'), // paginas que o chat sera carregado
        'carrinho' 
        );
        Site::loadJs('user','user',
        array('login','registrar'), // paginas que o chat sera carregado
        'ajaxLogin' 
        );
        Site::loadJs('user','user',
        array('minhaconta'), // paginas que o chat sera carregado
        'myaccount' 
        );
        Site::loadJs('user','user',
        array('termos'), // paginas que o chat sera carregado
        'termos' 
        );
        Site::loadJs('user','user',
        array('item'), // paginas que o chat sera carregado
        'produtos' 
        );
        Site::loadJs('user','user',
        array('feedbacks'), // paginas que o chat sera carregado
        'feedback' 
        );
        /*
        Site::loadJs('user','user',
        'all', // paginas que o chat sera carregado
        'bg' 
        );
        */
        
        
        /*
        Site::loadJs('user','user',
        array('chat'), // paginas que o chat sera carregado
        'filepond.min' 
        );
        Site::loadJs('user','user',
        array('chat'), // paginas que o chat sera carregado
        'filepond.jquery' 
        );
        */
    ?>
    </head>
    <body>
        <div class="page-bg"></div>
        <div class="animation-wrapper">
            <div class="particle particle-1"></div>
            <div class="particle particle-2"></div>
            <div class="particle particle-3"></div>
            <div class="particle particle-4"></div>
        </div>
        <header>
                <nav class="direction-row">
                    <div class="tabsMobile">
                        <span style="font-size:25px;" class="material-icons">menu</span>
                    </div>
    
                    <div class="logo"></div>
                    <div class="tabsHeader">
                        <div class="tab"><a href="<?php echo BASE?>"><p>Ínicio</p></a></div>
                        <div class="tab"><a href="<?php echo BASE?>"><p>Suporte</p></a></div>
                        <div class="tab"><a href="<?php echo BASE?>termos"><p>Termos</p></a></div>
                    </div>
                    <div class="avatarAreaWrapper">
                        <?php if(\Models\ChatModels::userHaveasTicketOn()){ ?>
                        <div class="chat flex-center">
                            <button link="chat/" class="flex-center"> 
                                <!--<span>Chat <?php /*echo \Models\ChatModels::mensagensNaolidas(); */?></span> -->
                                <span class="material-icons" style="margin:0 3px; font-size:20px;">chat_bubble</span>
                            </button>
                        </div>
                        <?php } ?>
                        <div class="cart" style="margin:0 10px;">
                            <?php if(Site::logado()){ ?><button js="openCart"><span class="material-icons" style="font-size:20px;"> shopping_cart </span></button> <?php } ?>
                            <div class="cartinside">
                                <div class="ajax-loading" style="text-align:center; margin:10px;"><i style="font-size:30px;" class="fa-spin fas fa-spinner" aria-hidden="true"></i></div>
                                <div class="fot">
                                    <span class="totalCart"><div class="ajax-loading" style="text-align:center; margin:10px;"><i style="font-size:20px;" class="fa-spin fas fa-spinner" aria-hidden="true"></i></div></span>
                                    <a href="<?php echo BASE?>carrinho"><button>Finalizar compra</button></a>
                                </div>
                            </div>
                        </div>
                        <div class="avatarArea">
                        <?php if(Site::logado()){?>
                           
                            <div class="avatar">
                                <img src="<?php echo BASE?>data/images/upload/<?php echo  Site::getImageUser(Site::getUserInfo($_COOKIE['loginToken'])['id']) ?>">
                                <span><?php echo Site::getUserInfo($_COOKIE['loginToken'])['username'] ?></spabn>
                            </div>
                            <?php }else{?>
                            <div class="notlogged">
                                <span class="material-icons">person</span>   
                               <button link="login/"><span>Entrar / Registrar</span></button>
                            </div>
                        <?php }?>
                            <div class="switchmode">
                                <div class="material-icons" style="transform:rotate(220deg)">nightlight_round</div>
                            </div>
                        </div>
                    </div>
                </nav>
                <div class="message-alert-box">
                    <h2>Notificação</h2>
                    <div class="content-Alert"></div>
                </div>
                <div id="fountainG">
                    <div style="position:relative; left:20%; top:30%">
                        <div id="fountainG_1" class="fountainG"></div>
                        <div id="fountainG_2" class="fountainG"></div>
                        <div id="fountainG_3" class="fountainG"></div>
                        <div id="fountainG_4" class="fountainG"></div>
                        <div id="fountainG_5" class="fountainG"></div>
                    </div>
                </div>
                <div class="black-fade"></div>
                <div class="menu">
                    <ul>
                        <li><div class="tab"><a href="<?php echo BASE?>"><p>Ínicio</p></a></div></li>
                        <li><div class="tab"><a href="<?php echo BASE?>item"><p>Produtos</p></a></div></li>
                        <li><div class="tab"><a href="<?php echo BASE?>termos"><p>Termos</p></a></div></li>
                        <hr>
                        <li><div class="tab"><a href="<?php echo BASE?>chat"><p>Chat</p></a></div></li>
                        <li><div class="tab"><a href="<?php echo BASE?>chat"><p>Carrinho</p></a></div></li>
                    </ul>
                    <?php 
                    if(!Site::logado()){
                    ?>
                        <div class="login mobile">
                            <button><a href="<?php echo BASE?>login"><span>Entrar</span></a></button>
                            <button><a href="<?php echo BASE?>registrar"><span>Registrar</span></a></button>
                        </div>
                    <?php } ?>
                </div>
                <div class="menuHoverAvatar">
                    <a href="<?php echo BASE?>minhaconta"><div class="tab">Meu perfil</div></a>
                    <?php if(\Models\ChatModels::userHaveasTicketOn()){?><a href="<?php echo BASE?>chat"><div class="tab"><span>Chat <?php echo \Models\ChatModels::mensagensNaolidas(); ?></span></div></a><?php }?>
                    <?php if(Site::logado()){ ?><a href="<?php echo BASE?>carrinho"><div class="tab"><span>Carrinho </span></div></a> <?php } ?>
                    <a href="<?php echo BASE?>?logout"><div class="tab">Sair </div></a>
                </div>
        </header>
        
        <div class="contentSite">
        
