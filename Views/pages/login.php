<div id="get" get="<?php print_r($_GET)?>"></get>
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
                <form class="ajax" action="<?php echo BASE?>ajax/ajaxUser.php" method="POST">
                    <input type="text" name="username" placeholder="Usuário">
                    <input type="password" name="password" placeholder="Senha">
                    <input type="hidden" name="type-action" value="login">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>">
                    <a href="<?php echo BASE?>login/resetar-senha">Esqueceu sua senha?</a>
                    <input type="submit" name="acao" value="Entrar">
                </form>
                <a href="<?php echo BASE?>registrar">Não tenho conta, quero me registrar.</a>
            </div>
        </div>
    </div>
</div>