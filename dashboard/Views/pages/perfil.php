<div class="container-contentWrapper">
    <div class="container-content">
        <div class="infoPerfil">
            <p>Perfil</p>
            <div class="img">
                <img src="<?php echo Admin::getProfilePhoto()?>">
                <label class="photo" style="display: none;">
                    <i class="fas fa-upload" aria-hidden="true"></i>
                    <input onchange="changeImage();" style="visibility:hidden; opacity:0; display:none;" type="file" name="photo" accept="image/png, image/gif, image/jpeg">
                </label>
            </div>
            <div class="simpleInfo usuario"><div><span>Nome de usuário</span></div><span>mtw</span> <button id="editarUsername"><i class="fas fa-edit"></i></button></div>
            <div class="simpleInfo senha"><div><span>Senha</span></div><span>**********</span> <button id="editarPassword"><i class="fas fa-edit"></i></button></div>
            <div class="simpleInfo email"><div><span>E-mail</span></div><span>matthewbugado@gmail.com</span> <button id="editarEmail"><i class="fas fa-edit"></i></button></div>
        </div><!--infoPerfil-->
    </div><!--container-content-->
</div><!--container-contentWrapper-->