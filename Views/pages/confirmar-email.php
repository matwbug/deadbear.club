<div class="center">
    <?php 
    if(isset($_COOKIE['tokenSessionReg']) && !isset($_GET['code'])){\Models\RegisterModels::confirmationCode($_COOKIE['tokenSessionReg']);
    }else{
        Site::scriptJS("alertar('Aconteceu algum erro ao validar seu registro tente novamente.',BASE+'registrar')");
    }
    ?>
    <script>
    </script>
    <div class="cont-log">
            <div class="ins">
                <h2>Foi enviado um e-mail de confirmação.</h2>
                <span>Para continuar seu registro verifique sua conta com o código que foi enviado em seu e-mail.</span>
                <form class="ajax" action="<?php echo BASE?>ajax/ajaxUser.php" method="POST">
                        <input type="text" style="margin-bottom:10px; margin-top:20px;" name="code" placeholder="Código"<?php if(isset($_GET['code'])){echo 'value='.$_GET['code'].'';} ?> >
                        <input type="hidden" name="type-action" value="confirmationEmail">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>">
                        <button type="submit">Verificar</button>
                </form>
                <button js="reenviaremail" style="margin:10px 0; background:none; font-size:10px; width:auto;">Reenviar e-mail</button>
            </div>
    </div>
</div>