<?php

    if($info = \Models\ProductsDefault::getInfoProduct()){

    //$imagem = \Models\ProductsDefault::getImageProductFromID($info['id']) ? 'upload/'.\Models\ProductsDefault::getImageProductFromID($info['id'])[0]['name'] : 'nada.webp';
?>
<div class="center">
    <div class="controladorPage">
        <a href="<?php echo BASE?>"><span>Ínicio</span></a>
        <span>/</span>
        <a href="<?php echo BASE?>contas-csgo"><span>Contas CS:GO</span> </a>
        <span>/</span>
        <span><?php echo ucfirst($info['nome'])?></span>
    </div>    

    <div class="container-main">
        <div class="ajax-load" style="margin:0 auto; text-align:center; font-size:20px;"><i class="fa-spin fas fa-spinner" aria-hidden="true"></i></div>
        <?php \Models\ProductsDefault::moreProducts();?>
    </div>
</div>

<?php 
    }else{?>
    <div class="center">
    <div class="container-main">
        <div class="ajax-load" style="margin:0 auto; text-align:center; font-size:20px;"><i class="fa-spin fas fa-spinner" aria-hidden="true"></i></div>
        <?php \Models\ProductsDefault::moreProducts();?>
    </div>
</div>

<?php }?>