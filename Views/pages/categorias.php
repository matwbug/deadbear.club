<div class="container-main">
    <div class="center">
        <div class="controladorPage">
            <a href="https://deadbear.club/"><span>Ínicio</span></a>
            <span>/</span>
            <a><span>Categorias</span> </a>
        </div>
        <div class="categoriapage">
            <div class="head">
            </div>
            <div class="body">
                <?php \Models\ProductsDefault::getCategorias();?>
            </div>
        </div>
    </div>
</div>