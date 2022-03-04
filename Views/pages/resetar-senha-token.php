<?php 
    \Models\RegisterModels::checkTokenResetPass();
?>
<div class="center">
    <div class="cont-log">
        <div class="ins">
            <h2>Digite sua nova senha</h2>
            <form class="ajax" action="<?php echo BASE?>ajax/ajaxUser.php" method="POST">
                    <input type="password" name="password" placeholder="Senha">
                    <input type="hidden" name="type-action" value="changePass">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>">
                    <button style="margin-top:10px;"type="submit">Alterar senha</button>
            </form>
        </div>
    </div>
</div>