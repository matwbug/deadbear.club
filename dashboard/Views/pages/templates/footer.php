<div class="menu">
    <div class="menuWrapper">
            <div class="menuAvatar">
                <img src="<?php echo Admin::getProfilePhoto()?>">
                <h1><?php echo Admin::getUserInfo($_COOKIE['admin_loginToken'])['username'] ?></h1>
                <p><?php echo ucfirst(Admin::getUserInfo($_COOKIE['admin_loginToken'])['role.Name']) ?></p>
                <a href="<?php echo BASE?>dashboard"><button><i class="fas fa-home"></i></button></a>
                <button id="edit"><i class="fas fa-edit"></i></button>
                <button id="logout"><i class="fas fa-sign-out-alt"></i></button>
            </div>
            <div class="menuTabs">
                <ul>
                    <li class="<?php Admin::menuActive('overview') ?>"><a href="<?php echo BASE?>dashboard/overview"><div class="insideTab"><div style="width:40px; text-align:center"><i class="fas fa-compass"></i></div> <span>Geral</span></div></a></li>
                    <li class="<?php Admin::menuActive('admin') ?>"><a href="<?php echo BASE?>dashboard/admin"><div class="insideTab"><div style="width:40px; text-align:center"><i class="fas fa-user-shield"></i></div> <span>Administração</span></div></a></li>
                    <li class="<?php Admin::menuActive('configurar') ?>"><a href="<?php echo BASE?>dashboard/configurar"><div class="insideTab"><div style="width:40px; text-align:center"><i class="fas fa-cog"></i></div> <span>Configurar</span></div></a></li>
                    <li class="<?php Admin::menuActive('chat') ?>"><a href="<?php echo BASE?>dashboard/chat"><div class="insideTab"><div style="width:40px; text-align:center"><i class="fas fa-comment-alt"></i></div> <span>Chats</span></div></a></li>
                </ul>
            </div>
        </div>
    </div>
<footer>
      <script src="https://kit.fontawesome.com/dc1f02ef1e.js" crossorigin="anonymous"></script>
</footer>
</body>
</html>