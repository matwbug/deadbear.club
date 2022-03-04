<div class="center">
    <div class="container-main">
        <div class="cont-log">
            <div class="img">
                <h2 class="text">Bem vindo a nossa loja.<br> Contas totalmente <br> personalizadas do jeito que <br> você preferir.</h2>
                <img src="<?php echo BASE?>data/images/slider/login.png">  
            </div>
            <div class="ins">
                <h2>DEADBEAR.CLUB</h2>
                <p>Entre na sua conta ou registre-se.</p>
                <form class="ajax" mode='registro' action="<?php echo BASE?>ajax/ajaxUser.php" method="POST">
                    <input required type="text" name="email" placeholder="Email">
                    <input required type="text" name="username" placeholder="Usuário">
                    <input required type="password" name="password" placeholder="Senha">
                    <input type="hidden" name="type-action" value='registrar'>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>">
                    <input style="margin-top:20px;" type="submit" name="acao" value="Entrar">
                </form>
            </div>
        </div>
    </div>
</div>