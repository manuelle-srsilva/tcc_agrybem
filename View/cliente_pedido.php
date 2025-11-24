<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agrybem - Meus Pedidos (Cliente)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Referência ao novo CSS com os estilos unificados -->
    <link rel="stylesheet" href="../templates/assets/css/cliente_pedido.css"> 
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
                <a href="../View/cliente_pag_principal.php" class="nav-button">Voltar</a>
            </nav>
        </div>
    </header>

    <main class="orders-page-content">
        <div class="container">
            <h1 class="orders-title">Meus Pedidos</h1>

            <div class="orders-filters">
                <div class="filter-item">
                    <label for="filter-date">Selecionar Data:</label>
                    <input type="date" id="filter-date">
                </div>
                <div class="filter-item">
                    <label for="filter-status">Status:</label>
                    <select id="filter-status">
                        <option value="all" selected>Todos</option>
                        <option value="retirada">Retirado</option>
                        <option value="enviado">Enviado</option>
                    </select>
                </div>
            </div>
            <div class="orders-list">
            <?php
            require_once __DIR__ . '/../vendor/autoload.php';
            use Model\Order;
            use Model\Produto;
            session_start();

            // determine client id from session (try several keys)
            $cliente_id = null;
            $keys = ['cliente_id','id_cliente','id','cliente','user','user_id','usuario'];
            foreach($keys as $k){
                if(isset($_SESSION[$k])){
                    if(is_array($_SESSION[$k]) && isset($_SESSION[$k]['id'])){ $cliente_id = $_SESSION[$k]['id']; break; }
                    $cliente_id = $_SESSION[$k]; break;
                }
            }

            $orderModel = new Order();
	            $total_pedido = 0; // Inicializa a variável para o total do pedido
            $produtoModel = new Produto();
            $orders = [];
            if($cliente_id){
                $orders = $orderModel->getOrdersByCliente($cliente_id);
            }

            if(empty($orders)){
                echo '<p>Você ainda não tem pedidos.</p>';
            } else {
                // group by pickup_date (or created_at if null)
                $groups = [];
                foreach($orders as $o){
                    // Pula pedidos cancelados para não exibir
                    if ($o['status'] === 'canceled') {
                        continue;
                    }
                    $date = $o['pickup_date'] ?: substr($o['created_at'],0,10);
                    $groups[$date][] = $o;
                }
                
                // Se não houver pedidos após filtrar os cancelados
                if (empty($groups)) {
                    echo '<p>Você ainda não tem pedidos.</p>';
                } else {
                    foreach($groups as $date => $group){
                        echo '<div class="order-group">';
                        echo '<h2 class="group-title">📅 Pedidos para ' . date('d/m/Y', strtotime($date)) . '</h2>';
                        foreach($group as $o){
                            // Map DB status to client-friendly badge with classes
                            $order_date_attr = date('Y-m-d', strtotime($o['pickup_date'] ?? $o['created_at']));
                            $order_status_attr = htmlspecialchars($o['status']);
                            echo '<div class="order-card compact" data-order-id="' . intval($o['id']) . '" data-order-status="' . $order_status_attr . '" data-order-date="' . $order_date_attr . '">';
                            echo '<div class="card-header">';
                            echo '<h3 class="card-title">Pedido #' . htmlspecialchars($o['id']) . '</h3>';
                            
                            // NOVA DIV para agrupar status e botão de cancelar
                            echo '<div class="status-actions" style="display: flex; align-items: center; gap: 10px;">';
                            
                            if($o['status'] === 'pending'){
                                echo '<span class="card-status status-retirada">🟡 Enviado</span>';
                            } elseif($o['status'] === 'finished' || $o['status'] === 'delivered'){
                                echo '<span class="card-status status-enviado">🟢 Retirado</span>';
                            } else {
                                // fallback
                                echo '<span class="card-status status-retirada">🟡 ' . htmlspecialchars(ucfirst($o['status'])) . '</span>';
                            }
                            // show cancel button for cancelable statuses (pending, in_transit)
                            if(in_array($o['status'], ['pending','in_transit'])){
                                echo '<button class="cancel-order-btn" data-order-id="' . intval($o['id']) . '" title="Cancelar pedido" style="    background-color: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 5px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 0.3s; white-space: nowrap;">Cancelar</button>';
                            }
                            
                            echo '</div>'; // status-actions
                            echo '</div>'; // card-header
                            
                            echo '<div class="card-meta compact-meta">';
                            echo '<span class="meta-item"><strong>Loja:</strong> ' . htmlspecialchars($o['empresa_nome'] ?? 'Loja') . '</span>';
                            echo '<span class="meta-item"><strong>Data:</strong> ' . date('d/m/Y', strtotime($o['pickup_date'] ?? $o['created_at'])) . '</span>';
echo '<span class="meta-item"><strong>Hora:</strong> ' . htmlspecialchars($o['pickup_time'] ?? '') . '</span>';
	                            
	                            // Calcula o total do pedido
	                            $total_pedido = 0;
	                            foreach($o['items'] as $item){
	                                $preco = 0;
	                                try{
	                                    $p = $produtoModel->getProdutoInfo($item['produto_id']);
	                                    if(!empty($p) && isset($p['preco'])) $preco = (float)$p['preco'];
	                                } catch(Exception $e){ }
	                                
	                                $quantidade = (float)($item['quantidade'] ?? 0);
	                                $total_pedido += $preco * $quantidade;
	                            }
	                            
	                            // Formata o total para exibição
	                            $total_formatado = 'R$ ' . number_format($total_pedido, 2, ',', '.');
	                            
	                            // Exibe o campo Total
	                            echo '<span class="meta-item"><strong>Total:</strong> ' . $total_formatado . '</span>';
	                            
	                            echo '</div>'; // card-meta
	                            echo '<ul class="card-items-list compact-list">';
	                            echo '<li class="item-list-title">Itens Solicitados:</li>';
	                            
	                            foreach($o['items'] as $it){
                                // get product name from Produto model
$prodName = 'Produto #' . $it['produto_id'];
	                                $precoUnitario = 0;
	                                try{
	                                    $p = $produtoModel->getProdutoInfo($it['produto_id']);
	                                    if(!empty($p) && !empty($p['nome'])) $prodName = $p['nome'];
	                                    if(!empty($p) && isset($p['preco'])) $precoUnitario = (float)$p['preco'];
	                                } catch(Exception $e){ }
	                                
	                                $precoFormatado = 'R$ ' . number_format($precoUnitario, 2, ',', '.');
	                                $prodName .= ' (' . $precoFormatado . ')'; // Adiciona o preço ao nome do produto

                                // format quantity: kg/g -> 2 decimals max, un -> 0 decimals
                                $unit = $it['unidade'] ?? '';
                                $qty = $it['quantidade'] ?? 0;
                                $dec = ($unit === 'un') ? 0 : 2;
                                $qtyFormatted = number_format((float)$qty, $dec, ',', '');

echo '<li class="card-item">';
	                                echo '<span class="item-name">' . htmlspecialchars($prodName) . '</span>';
	                                echo '<span class="item-quantity">' . htmlspecialchars($qtyFormatted) . ' ' . htmlspecialchars($unit) . '</span>';
	                                echo '</li>';
                            }
                            echo '</ul>';
                            echo '</div>'; // order-card
                        }
                        echo '</div>'; // group
                    }
                }
            }
            ?>
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

<script>
// Filters: data and status for cliente_pedido.php
(function(){
    const dateInput = document.getElementById('filter-date');
    const statusSelect = document.getElementById('filter-status');
    if(!dateInput && !statusSelect) return;

    function mapDbStatusToClientLabel(dbStatus){
        if(!dbStatus) return '';
        if(dbStatus === 'pending') return 'enviado';
        if(dbStatus === 'finished' || dbStatus === 'delivered') return 'retirada';
        // other statuses fallback to their raw name
        return dbStatus;
    }

    function applyFilters(){
        const groups = document.querySelectorAll('.order-group');
        const selDate = dateInput ? dateInput.value : '';
        const selStatus = statusSelect ? statusSelect.value : 'all';

        groups.forEach(group => {
            let anyVisible = false;
            const cards = group.querySelectorAll('.order-card');
            cards.forEach(card => {
                const cardDate = card.dataset.orderDate || '';
                const cardDbStatus = card.dataset.orderStatus || '';
                const cardClientStatus = mapDbStatusToClientLabel(cardDbStatus);

                let matchDate = true;
                if(selDate){ matchDate = (cardDate === selDate); }

                let matchStatus = true;
                if(selStatus && selStatus !== 'all'){
                    // status options: 'retirada' or 'enviado'
                    matchStatus = (cardClientStatus === selStatus);
                }

                if(matchDate && matchStatus){
                    card.style.display = '';
                    anyVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });
            // hide group if no visible cards
            group.style.display = anyVisible ? '' : 'none';
        });
    }

    // force status select to 'all' on load to avoid browser-preserved state
    if(statusSelect) statusSelect.value = 'all';
    if(dateInput) dateInput.addEventListener('change', applyFilters);
    if(statusSelect) statusSelect.addEventListener('change', applyFilters);
    // initial apply
    applyFilters();
})();
</script>

<script>
// Cancel order handler
(function(){
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.cancel-order-btn');
        if(!btn) return;
        e.preventDefault();
        const orderId = btn.dataset.orderId;
        if(!orderId) return;
        if(!confirm('Deseja cancelar este pedido?')) return;

        fetch('../Controller/OrderController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ action: 'client_cancel', order_id: parseInt(orderId,10) })
        }).then(r=>r.json()).then(data=>{
            if(data && data.success){
                // **NOVA LÓGICA: Exibe mensagem de sucesso antes de remover**
                alert('Pedido #' + orderId + ' cancelado com sucesso.');
                
                // remove card from DOM
                const card = document.querySelector('.order-card[data-order-id="'+orderId+'"]');
                if(card){
                    const group = card.closest('.order-group');
                    card.remove();
                    // if no more cards in group, remove group
                    if(group && group.querySelectorAll('.order-card').length === 0){
                        group.remove();
                    }
                }
            } else {
                // **CORREÇÃO: Usa data.message para exibir o erro do servidor**
                alert('Não foi possível cancelar o pedido: ' + (data.message || 'Erro desconhecido.'));
            }
        }).catch(err=>{
            console.error(err);
            alert('Erro na requisição. Tente novamente.');
        });
    });
})();
</script>