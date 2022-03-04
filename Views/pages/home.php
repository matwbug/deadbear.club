<div class="container-main">
    <div class="center">
        <section id="produtos">
            <div class="box-produtos">
                
                <?php  $produtos = \Models\ProductsDefault::getCategoriasHome();
                        foreach($produtos as $key => $value){
                            echo $value;
                        }
                ?>
            </div><!--box-produtos-->
        </section>
        
        <?php \Models\FeedbackModels::getFeedbackHome(); ?>
    </div>
</div><!--container-main-->