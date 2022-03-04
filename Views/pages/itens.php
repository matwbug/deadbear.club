<div class="container-main">
    <div class="center">
        <div class="controladorPage">
            <a href="<?php echo BASE?>"><span>Ínicio</span></a>
            <span>/</span>
            <a><span>Item</span> </a>
        </div>
        <div class="categoriapage">
            <div class="head">
            </div>
            <div class="body">
                <?php \Models\ProductsDefault::getProducts(); ?>
            </div>
        </div>
    </div>
</div>
