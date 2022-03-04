<?php 
Site::loadJs(
    'admin',
     'admin',
    'all', 
    'Chart.min'
    );
?>
<div class="container-contentWrapper">
    <div class="container-content" style="display:flex; justify-content: center;flex-wrap: wrap;">
        <div class="info">
            <h2>Total de usuários</h2>
            <div class="disp">
                <p><i class="fas fa-user"></i> <?php echo Admin::getTotalUsers(); ?></p>
            </div>
        </div>
        <div class="info">
            <a href="<?php echo BASE?>dashboard/chat">
                <h2>Total de tickets</h2>
                <div class="disp">
                    <p><i class="fas fa-ticket-alt"></i> <?php echo Admin::getTotalTickets(); ?></p>
                </div>
            </a>
        </div>
    </div><!--container-content-->
</div><!--container-contentWrapper-->
<div class="container-contentWrapper">
    <div class="container-content" style="display:flex;">
    
    </div><!--container-content-->
</div><!--container-contentWrapper-->
