<div class="center">
    <div class="cont-log">
        <div class="ins">
            <h2>Preencha com o código que foi enviado em seu e-mail</h2>
            <form method="POST" class="ajax" action="<?php echo BASE?>ajax/ajaxUser.php">
                <input type="text" name="code" placeholder="Código" <?php if(isset($_GET['code'])){echo 'value='.$_GET['code'].'';} ?>>
                <input type="hidden" name="type-action" value="resetPassCode">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>">
                <button class="btn-hover">Resetar</button>
            </form>
        </div>
     </div>
</div>