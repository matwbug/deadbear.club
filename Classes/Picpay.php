<?php 
  class PicPay{
	 

    public function __construct($picpaytoken,$sellertoken){
        $this->x_picpay_token = $picpaytoken;
        $this->sellerToken = $sellertoken;
    }
	 /*
	  *@var type String: $urlCallBack
	  */
	  private $urlCallBack = BASE."notification.php";
	 
	  /*
	   *@var type String: $urlReturn
	   */
	  private $urlReturn = BASE."chat";
	 
	
	 
	 //Função que faz a requisição
	 public function criarCobPix($idpedido,$valor){
        $cliente = Site::getUserInfo($_COOKIE['loginToken']);
		$reffId = 'DB_'.$idpedido.'_'.Site::generateRandomString(6);
		 
        $data = array(
                'referenceId' => $reffId,
                'callbackUrl' => $this->urlCallBack,
                'returnUrl'   => $this->urlReturn.'?paymentConfirmed='.$idpedido,
                'value'       => $valor,
                'buyer'       => [
                        'firstName' => $cliente['nome'],
                        'lastName'  => $cliente['sobrenome'],
                        'document'  => $cliente['cpf'],
                        'email'     => $cliente['email'],
                        'phone'     => $cliente['telefone']
                    ],
                );
		 
		$ch = curl_init('https://appws.picpay.com/ecommerce/public/payments');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-picpay-token: '.$this->x_picpay_token));
		
		$res = curl_exec($ch);
		curl_close($ch);
		$return = json_decode($res);

		$sql = MySql::conectar()->prepare("SELECT * FROM `pedido.txid` WHERE `id.pedido` = ?"); $sql->execute(array($idpedido));
		try{
			if($sql->rowCount() <= 0){
				$sql = MySql::conectar()->prepare("INSERT INTO `pedido.txid` VALUES(null,?,null,'PICPAY','pendente',?,$valor,?,?,null,'carrinho',?)");
				if($sql->execute(array($idpedido,$reffId,$return->qrcode->content,$return->qrcode->base64,date('Y-m-d H:i:s')))){return $return;}
				
			}
		}catch(Exception $e){
			return $e;
		}
	 }
	 
	 
	 
	 // Notificação PicPay
	 public function PagamentoStatus($reffId){
		 
		 if(isset($reffId)){
			$ch = curl_init('https://appws.picpay.com/ecommerce/public/payments/'.$reffId.'/status');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-picpay-token: '.$this->x_picpay_token)); 
			$res = curl_exec($ch);
			curl_close($ch);
			$notification = json_decode($res); 
			return $notification;
		}
		return false;

	 

	 
	 
  }
}



  
  
  
?>
