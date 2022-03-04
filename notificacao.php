<?php 
    if(isset($_REQUEST['collection_id'])){
        $accstoken = 'TEST-4168286644451011-022323-0f7567c2469bc7c4d3d04dd2d9c7a636-548051592';
        $curl = curl_init();
        $collection_id = $_REQUEST['collection_id'];
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/'.$collection_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0, 
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$accstoken
            ),

        ));

        $paymentInfo = json_decode(curl_exec($curl), true);
        curl_close($curl);
        print_r($paymentInfo);
    }
?>