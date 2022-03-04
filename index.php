<?php   
    include('config.php');
    $autoload = function($class){
        $class = str_replace('\\','/',$class);
        if(file_exists($class.'.php')){
            include($class.'.php');
        }if(file_exists('Classes/'.$class.'.php')){
            include('Classes/'.$class.'.php');
        }
    };
    
    spl_autoload_register($autoload);
    require('vendor/autoload.php');

    $app = new Application();
    $app->run();

   \Site::gerar_token();

?>
<script>
    sessionStorage.setItem('token','<?php echo \Site::gerar_token(); ?>');
    
</script>