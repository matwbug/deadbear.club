<div class="container-main">
    <div class="center" style="display: flex;align-content: center;flex-wrap: wrap;justify-content: center;flex-direction: column;">
        <div class="cart-one">
            <div class="cart-container">
                <div class="single-cart">
                    <?php \Models\CarrinhoModels::getCarrinho();?>
                </div>
            </div>
        </div>
        <div class="cart-two">
            <div class="box-methodpayments">
                <div class="head">
                    <h3 class="flex-center"><span style="margin:0 5px;" class="material-icons">payment</span>FORMAS DE PAGAMENTO</h3>
                </div>
                <div class="body flex-center" style="flex-direction:column">
                    <div class="single-method w100 flex-center">
                        <input type="radio" id="pix" name="method" value="PIX">
                        <label for="pix"><span>PIX</span>
                        <b style="margin: 0 5px;">(A vista)</b></label>
                    </div>
                    <div class="single-method w100 flex-center">
                        <input type="radio" id="picpay" name="method" value="PICPAY">
                        <label for="picpay"><span class="material-icons">credit_card</span><span>PICPAY</span>
                        <b style=" margin: 0 5px;">(Em até 12x no cartão)</b></label>
                    </div>
                </div>
                
            </div>
        </div>
        <label for="terms">
            <input style="margin-left:10px;" type="checkbox" id="terms" name="terms" value="terms">
            <span>Estou de acordo com os seguintes <a target="_blank" href="<?php echo BASE?>termos">termos</a> e quero prosseguir minha compra.</span>
        </label>
        <div class="actionsCart"><button type="submit" class="jsCloseOrder">Fechar pedido</button></div>

        

    </div>
</div>