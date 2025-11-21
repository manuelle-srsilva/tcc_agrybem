<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agrybem - Finalizar Compra</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/loja.css"> 
    <link rel="stylesheet" href="../templates/assets/css/checkout.css">
    <!-- Não incluímos os scripts, pois é apenas uma simulação estática -->
</head>
<body>
    <!-- Header (Reutilizado do carrinho.html) -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
                <a href="carrinho.php" class="nav-button">Voltar ao carrinho</a>
            </nav>
        </div>
    </header>

    <!-- Conteúdo do Checkout -->
    <main class="checkout-page-content">
        <div class="container">
            <?php if(isset($_GET['success']) && $_GET['success']=='1'){
                $orders = isset($_GET['orders']) ? htmlspecialchars($_GET['orders']) : '';
                header('Location: loja.php?order_confirmed=1&orders=' . urlencode($orders));
                exit();
            } elseif(isset($_GET['success']) && $_GET['success']=='0'){
                echo '<div class="checkout-error">Erro ao criar pedido. Tente novamente.</div>';
            }
            ?>
            <h1 class="checkout-title">Finalizar Pedido</h1>

            <div class="checkout-grid">
                <!-- Card 1: Resumo do Pedido (Adaptado para o formato de nota fiscal com imagem) -->
                <div class="checkout-card order-summary-card">
                    <div class="order-card-header">
                        <span class="store-name">Cesta Rural</span>
                        <h2 class="card-title">Seu Pedido</h2>
                    </div>
                    <div id="order-items-container" class="order-items-scrollable">
                        <?php
                        $cart = $_SESSION['cart'] ?? [];
                        $grandTotal = 0.0;
                        if(empty($cart)){
                            echo '<p>Seu carrinho está vazio.</p>';
                        } else {
                            foreach($cart as $pid => $it){
                                $name = htmlspecialchars($it['name'] ?? '');
                                $category = htmlspecialchars($it['category'] ?? '');
                                $unitPrice = floatval($it['price'] ?? 0);
                                $unit = htmlspecialchars($it['selected_unit'] ?? ($it['measure']==='kg'?'kg':'un'));
                                $qty = floatval($it['quantity'] ?? 0);
                                $img = htmlspecialchars($it['image'] ?? '');
                                $itemTotal = ($unit === 'g') ? $unitPrice * ($qty/1000) : $unitPrice * $qty;
                                $grandTotal += $itemTotal;
                                $unitLabel = ($unit === 'kg' || $unit === 'g') ? $unit : 'un';
                                echo '<div class="order-item-detail">';
                                echo '<div class="item-info-group">';
                                echo '<img src="' . $img . '" alt="' . $name . '" class="order-item-image">';
                                echo '<div class="item-text-details">';
                                echo '<span class="item-name">' . $name . '</span>';
                                echo '<span class="item-category">' . $category . '</span>';
                                echo '<span class="item-base-price">Preço base: R$ ' . number_format($unitPrice,2,',','.') . ' / ' . $unitLabel . '</span>';
                                echo '</div></div>';
                                echo '<div class="item-quantity-price-group">';
                                echo '<span class="item-quantity">' . $qty . ' ' . $unitLabel . '</span>';
                                echo '<span class="item-total-price">R$ ' . number_format($itemTotal,2,',','.') . '</span>';
                                echo '</div></div>';
                            }
                        }
                        ?>
                    </div>
                    <div class="summary-total-line">
                        <span class="summary-total-label">Total Geral:</span>
                        <span class="summary-total-value" id="checkout-grand-total">R$ <?php echo number_format($grandTotal,2,',','.'); ?></span>
                    </div>
                </div>

                <!-- Card 2: Agendamento de Retirada (Mantido como estava) -->
                <div class="checkout-card schedule-card">
                    <h2 class="card-title">Agendamento de Retirada</h2>
                    <form id="schedule-form" method="post" action="../Controller/OrderController.php">
                        <div class="form-group">
                            <label for="pickup-date">Data de Retirada:</label>
                            <input type="date" id="pickup-date" name="pickup_date" required>
                        </div>
                        <div class="form-group">
                            <label for="pickup-time">Hora de Retirada:</label>
                            <input type="time" id="pickup-time" name="pickup_time" required>
                        </div>
                        <button type="submit" name="action" value="place" class="confirm-order-btn">
                            Confirmar Pedido
                        </button>

                  
                    </form>
                </div>
            </div>
        </div>
    </main>

     <!-- Api Vlibras -->
    <div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>
</body>
</html>
///