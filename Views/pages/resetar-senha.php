<div class="center">
    <div class="cont-log">
        <div class="ins">
            <h2>Preencha com seu e-mail.</h2>
            <span>Você receberá as instruções de como trocar sua senha em seu e-mail.</span>
            <form method="POST" class="ajax" action="<?php echo BASE?>ajax/ajaxUser.php">
                <input type="text" name="email" placeholder="E-mail">
                <input type="hidden" name="type-action" value="resetPassword">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>">
                <button class="btn-hover">Resetar</button>
            </form>
        </div>
    </div>
</div>