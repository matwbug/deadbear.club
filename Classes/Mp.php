<?php 
    class Mp{
        public function __construct($publicKey,$AcessToken,$clientId=null,$clientSecret=null){
            $this->publicKey = $publicKey;
            $this->accessToken = $AcessToken;
            $this->clientId = $clientId;
            $this->clientSecret = $clientSecret;
        }


        public function criarCob($idpedido){
            MercadoPago\SDK::setAccessToken($this->accessToken);   

            // Cria um objeto de preferência
            $preference = new MercadoPago\Preference();
            $itens = explode(',',Site::getInfoDB('pedido.cart.users','`id`='.$idpedido)['carts.id']);
            $item = new MercadoPago\Item();
            foreach($itens as $key => $value){
                $cartInfo = Site::getInfoDB('cart.users','`id`='.$value);
                $itemInfo = Site::getInfoDB('produtos.default','`id`='.$cartInfo['id.item']);
                $item->title = $itemInfo['nome'];
                $item->quantity = $cartInfo['quantidade'];
                $item->unit_price = $itemInfo['preco'];
            }
            print_r($item);
            $preference->items = array($item);
            $preference->save();

            return $preference;
            
        }
        
    }

?>