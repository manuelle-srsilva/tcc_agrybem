<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agrybem - Carrinho de Compras</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/carrinho.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
                <a href="../View/loja.php" class="nav-button">Voltar à Loja</a>
            </nav>
        </div>
    </header>

    <!-- Conteúdo do Carrinho -->
    <main class="cart-page-content">
        <div class="container">
            <h1 class="cart-title">Seu Carrinho</h1>

            <!-- Container de Itens do Carrinho -->
            <div id="cart-items-container">
                <?php
                $cart = $_SESSION['cart'] ?? [];
                if (empty($cart)) {
                    echo '<p>Seu carrinho está vazio.</p>';
                } else {
                    foreach ($cart as $pid => $item) {
                        $mapped = htmlspecialchars($item['mapped_id'] ?? $pid);
                        $name = htmlspecialchars($item['name'] ?? '');
                        $category = htmlspecialchars($item['category'] ?? '');
                        $price = number_format(floatval($item['price'] ?? 0), 2, ',', '.');
                        $image = htmlspecialchars($item['image'] ?? '');
                        $measure = htmlspecialchars($item['measure'] ?? 'un');
                        $quantity = $item['quantity'] ?? 1;
                        $dataUnitPrice = htmlspecialchars($item['price'] ?? 0);
                        echo "<div class=\"cart-item\" data-product-id=\"{$mapped}\" data-original-product-id=\"{$pid}\" data-unit-price=\"{$dataUnitPrice}\" data-measure=\"{$measure}\">";
                        echo "<img src=\"{$image}\" alt=\"{$name}\" class=\"cart-item-image\">";
                        echo "<div class=\"cart-item-details\">";
                        echo "<span class=\"cart-item-name\">{$name}</span>";
                        echo "<span class=\"cart-item-category\">{$category}</span>";
                        echo "<span class=\"cart-item-base-price\">Preço base: R$ {$price}</span>";
                        echo "</div>";
                        echo "<div class=\"cart-item-controls\">";
                        echo "<div class=\"quantity-unit-group\">";
                        $selectedUnit = htmlspecialchars($item['selected_unit'] ?? ($measure === 'kg' ? 'kg' : 'un'));
                        if ($measure === 'kg') {
                            echo "<input type=\"number\" min=\"0.1\" step=\"0.1\" value=\"{$quantity}\">";
                            echo "<select>";
                            echo "<option value=\"kg\"" . ($selectedUnit === 'kg' ? ' selected' : '') . ">kg</option>";
                            echo "<option value=\"g\"" . ($selectedUnit === 'g' ? ' selected' : '') . ">g</option>";
                            echo "</select>";
                        } else {
                            echo "<input type=\"number\" min=\"1\" step=\"1\" value=\"{$quantity}\">";
                            echo "<select>";
                            echo "<option value=\"un\"" . ($selectedUnit === 'un' ? ' selected' : '') . ">un</option>";
                            echo "</select>";
                        }
                        echo "</div>";
                        echo "<span class=\"item-total-price\">R$ 0,00</span>";
                        echo "<button class=\"remove-item-btn\">Remover</button>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>

            <!-- Resumo do Carrinho -->
            <div class="cart-summary">
                <div class="summary-details">
                    <span class="summary-total-label">Total Geral:</span>
                    <span class="summary-total-value" id="grand-total">R$total_preco(soma dos preços)</span>
                </div>
                <!-- O botão de finalizar pedido agora é um link para simular a navegação -->
                <a href="../View/checkout.php" class="checkout-btn" id="checkout-btn">Finalizar Pedido</a>
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
    <script>
    (function(){
        async function post(data){
            const res = await fetch('../Controller/CartController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return res.json();
        }

        function formatPrice(v){ return 'R$ ' + Number(v).toFixed(2).replace('.',','); }

        async function updateQuantity(originalId, qty, unit){
            // send update to server, but don't reload; update DOM on success
            try{
                const payload = { action: 'update', product_id: originalId, quantity: qty };
                if(typeof unit !== 'undefined' && unit !== null) payload.unit = unit;
                const res = await post(payload);
                return res;
            }catch(e){ console.error(e); return null; }
        }

        async function removeItem(originalId){
            try{
                const res = await post({ action: 'remove', product_id: originalId });
                return res;
            }catch(e){ console.error(e); return null; }
        }

        document.addEventListener('DOMContentLoaded', function(){
            // Atualiza preços iniciais
            document.querySelectorAll('.cart-item').forEach(div=>{
                const unitPrice = parseFloat(div.dataset.unitPrice || '0') || 0;
                const measure = div.dataset.measure || 'un';
                const input = div.querySelector('input[type=number]');
                const select = div.querySelector('select');
                const totalSpan = div.querySelector('.item-total-price');
                const orig = div.dataset.originalProductId;
                function compute(){
                    let qty = Number(input.value) || 0;
                    const unit = select.value;
                    let total = 0;
                    if(measure === 'kg'){
                        total = (unit === 'g') ? unitPrice * (qty/1000) : unitPrice * qty;
                    } else {
                        total = unitPrice * qty;
                    }
                    // update item total display and store total in data-price
                    totalSpan.textContent = formatPrice(total);
                    div.dataset.price = String(total);
                }

                compute();

                // Use input event with debounce for better UX (updates immediately, syncs after pause)
                let debounceTimer = null;
                input.addEventListener('input', function(){
                    let qty = Number(this.value) || 0;
                    if(measure === 'kg' && qty < 0.1) qty = 0.1;
                    if(measure !== 'kg' && qty < 1) qty = 1;
                    this.value = qty;
                    // immediate UI update
                    compute();
                    updateGrandTotal();
                    // debounce server update
                    if(debounceTimer) clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(async ()=>{
                        const res = await updateQuantity(orig, input.value, select.value);
                        // optional: handle server error
                        if(!(res && res.success)){
                            console.error('Failed to update quantity on server', res);
                        }
                    }, 600);
                });

                select.addEventListener('change', async function(){
                    // update UI immediately
                    compute();
                    updateGrandTotal();
                    // persist selected unit to server
                    const res = await updateQuantity(orig, input.value, select.value);
                    if(!(res && res.success)){
                        console.error('Failed to persist unit selection', res);
                    }
                });

                const removeBtn = div.querySelector('.remove-item-btn');
                if(removeBtn){
                    removeBtn.addEventListener('click', async function(){
                        const res = await removeItem(orig);
                        if(res && res.success){
                            div.remove();
                            // if no items left, show empty message
                            const any = document.querySelectorAll('.cart-item').length;
                            if(!any) document.getElementById('cart-items-container').innerHTML = '<p>Seu carrinho está vazio.</p>';
                            updateGrandTotal();
                        }
                    });
                }
            });
            // compute grand total initially
            updateGrandTotal();
        });

        function updateGrandTotal(){
            let grand = 0;
            document.querySelectorAll('.cart-item').forEach(div => {
                const price = parseFloat(div.dataset.price || '0') || 0;
                grand += price;
            });
            const grandEl = document.getElementById('grand-total');
            if(grandEl) grandEl.textContent = formatPrice(grand);
        }
    })();
    </script>
            <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
            <script>
            new window.VLibras.Widget('https://vlibras.gov.br/app');
            </script>
</body>
</html>