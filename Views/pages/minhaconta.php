<?php 
    if(isset($_GET['tab']) && $_GET['tab'] == 'meusdados'){
        Site::scriptJS('tabDataUser();');
    }else if(isset($_GET['tab']) && $_GET['tab'] == 'refs'){
        Site::scriptJS('tabRefTab();');
    }else{
        Site::scriptJS('tabOrderUser();');
    }
    $info = Site::getUserInfo($_COOKIE['loginToken'])
?>
<script>
    function changeImage(){
        var imgUrl = $('[name="photo"]').prop('files')[0];
        var formData = new FormData();
        formData.append('file',imgUrl)
        formData.append('type-action','changeImage');
        formData.append('token',sessionStorage.getItem('token'))
        $.ajax({
            url:BASE+'ajax/ajaxUser.php',
            method:'post',
            contentType:false,
            cache:false,
            processData:false,
            data:formData
        }).done(function(data){
            data = JSON.parse(data)
            alertar(data.msg,'')
            $('.img').find('img').prop('src',data.newImage)
        })
    }
</script>
<div class="container-main">
    <div class="center">
        <div class="controladorPage">
            <a href="<?php echo BASE?>"><span>Ínicio</span></a>
            <span>/</span>
            <span>Minha Conta</span>
        </div>
        <div class="myaccount">
            <div class="slidebar">
                <div class="content">
                    <div class="img">
                        <img src="<?php echo BASE?>data/images/upload/<?php echo  Site::getImageUser(Site::getUserInfo($_COOKIE['loginToken'])['id']) ?>">
                        <div class="userOnline" style="width: 20px;height: 20px;"></div>
                        <label class="photo">
                            <i class="material-icons">file_upload</i>
                            <input onchange="changeImage();" type="file" name="photo" accept="image/png, image/gif, image/jpeg">
                        </label>
                    </div>
                    <div class="body">
                        <p>Nome de usuário</p>
                        <div class="user"> 
                            <div><span><?php echo $info['username'] ?></span></div>
                        </div>
                        <p>Email</p>
                        <div class="email"> 
                            <div><span><?php echo $info['email'] ?></span></div>
                        </div>
                        <p>Senha</p>
                        <div class="pass"> 
                            <div><span>********</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="body">
                <div class="buttons-area flex-center">
                    <button class="my-orders-tab flex-center"><span class="material-icons">shopping_basket</span> <span>Meus pedidos</span></button>
                    <button class="my-data-user flex-center"><span class="material-icons">person</span>  <span>Meus dados </span></button>
                    <button class="my-ref-tab flex-center"><span class="material-icons">share</span>  <span>Minhas referências</span> </button>
                </div>
                <div class="content-myaccount">
                </div>
        </div>
    </div>
</div>
<?php 
    if(isset($_GET['verpedido'])){
        Site::scriptJS('
            $.ajax({
                url:BASE+"ajax/ajaxUser.php",
                method:"post",
                data:{"type-action":"getInfoOrder","id":'.$_GET['verpedido'].'}
            }).done(function(data){
                data = JSON.parse(data)
                if(data.sucesso){
                    modal()
                    $(".ticketstatus").append(data.statusticket)
                    $(".feedbackModal").append(data.feedback)
                    $(".garantia").append(data.garantia)
                }
            })
        ');
        Site::scriptJS("$('html, body').scrollTop(500)");
    }
    Site::scriptJS('$(".back").click(function(){
        $(".modal-box").empty().animate({"left":"-4000px","opacity":"0"}, "slow")
    })');
    
?>