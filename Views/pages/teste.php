

<?php

    MercadoPago\SDK::setAccessToken('TEST-4168286644451011-022323-0f7567c2469bc7c4d3d04dd2d9c7a636-548051592');   

    // Cria um objeto de preferência
    $idpedido = 75;
    $preference = new MercadoPago\Preference();
    $itens = explode(',',Site::getInfoDB('pedido.cart.users','`id`='.$idpedido)['carts.id']);
    
    foreach($itens as $key => $value){
        $item = new MercadoPago\Item();
        $cartInfo = Site::getInfoDB('cart.users','`id`='.$value);
        $itemInfo = Site::getInfoDB('produtos.default','`id`='.$cartInfo['id.item']);
        $item->title = $itemInfo['nome'];
        $item->unit_price = $itemInfo['preco'];
        $item->quantity = $cartInfo['quantidade'];
    }
    $preference->back_urls = array(
        "success" => BASE."pagamento/chat?paymentConfirmed=".$idpedido."",
        "failure" => BASE."pagamento/failure",
        "pending" => BASE."pagamento/pending"
    );
    
    $preference->items = array($item);
    var_dump($preference->items);
    $preference->notification_url = BASE.'notificacao.php';
    $preference->external_reference = $idpedido;
    $preference->save();
    //Site::redirecionar(BASE);

?>

<script
  src="https://www.mercadopago.com.br/integrations/v1/web-payment-checkout.js"
  data-preference-id="<?php echo $preference->id; ?>">
</script>