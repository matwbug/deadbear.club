<?php 
    $url = explode('/',Site::getCurrentUrl()); $base = $url[1] == 'db' ? 'local' : 'online'; $key = $base == 'local' ? 2 : 1 ;
    $slug = explode('/',Site::getCurrentUrl())[$key];
    $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `slug` = ?");$sql->execute(array($slug));
    $info = $sql->fetch();
?>
<div id="cat" cat="<?php echo $info['nome'];?>"></div>
<script>
    var cat = $('#cat').attr('cat')
    document.title = document.title.replace('${categoria}',cat)
</script>
<div class="container-main">
    <div class="center">
        <div class="controladorPage">
            <a href="<?php echo BASE?>"><span>Ínicio</span></a>
            <span>/</span>
            <a><span><?php echo $info['nome'];?></span> </a>
        </div>
        <div class="categoriapage">
            <div class="head">
            </div>
            <div class="body">
                <?php \Models\ProductsDefault::getProductsCategory(); ?>
            </div>
        </div>
    </div>
</div>
