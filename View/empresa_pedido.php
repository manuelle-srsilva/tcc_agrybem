<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Model\Order;
use Model\Produto;

// determine empresa id from session
$empresa_id = null;
$keys = ['empresa_id', 'id_empresa', 'empresa', 'empreendimento_id', 'id_empreendimento'];
foreach ($keys as $k) {
    if (isset($_SESSION[$k])) {
        $empresa_id = $_SESSION[$k];
        break;
    }
}

$orderModel = new Order();
$produtoModel = new Produto();
$orders = [];
if ($empresa_id) {
    $orders = $orderModel->getOrdersByEmpresa($empresa_id);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agrybem - Pedidos Recebidos (Empreendedor)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/empresa_pedido.css">

</head>

<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
                <a href="../View/painel_profissional_empresa.php" class="nav-button">Voltar</a>
            </nav>
        </div>
    </header>

    <main class="orders-page-content">
        <div class="container">
            <h1 class="orders-title">Pedidos Recebidos</h1>

            <div class="orders-filters">
                <div class="filter-item">
                    <label for="filter-date">Selecionar Data:</label>
                    <input type="date" id="filter-date">
                </div>
                <div class="filter-item">
                    <label for="filter-status">Status:</label>
                    <select id="filter-status">
                        <option value="all" selected>Todos</option>
                        <option value="pending">Pendentes</option>
                        <option value="finished">Entregues</option>
                        <option value="canceled">Cancelados</option>
                    </select>
                </div>
            </div>

            <div class="orders-list">
                <?php
                if (empty($orders)) {
                    echo '<p>Sem pedidos para sua empresa no momento.</p>';
                } else {
                    // group by pickup_date (or created_at)
                    $groups = [];
                    foreach ($orders as $o) {
                        $date = $o['pickup_date'] ?: substr($o['created_at'], 0, 10);
                        $groups[$date][] = $o;
                    }

                    foreach ($groups as $date => $group) {
                        echo '<div class="order-group">';
                        echo '<h2 class="group-title">📅 Pedidos de ' . date('d/m/Y', strtotime($date)) . '</h2>';
                        foreach ($group as $o) {
                            $status = $o['status'];
                            $statusBadge = 'status-pendente';
                            $statusLabel = 'Pendente';
                            if ($status === 'pending') {
                                $statusBadge = 'status-pendente';
                                $statusLabel = 'Pendente';
                            } elseif ($status === 'in_transit') {
                                $statusBadge = 'status-transito';
                                $statusLabel = 'Em Trânsito';
                            } elseif ($status === 'delivered' || $status === 'finished') {
                                $statusBadge = 'status-entregue';
                                $statusLabel = 'Finalizado';
                            } elseif ($status === 'canceled') {
                                $statusBadge = 'status-cancelado';
                                $statusLabel = 'Cancelado';
                            }

                            $order_date_attr = date('Y-m-d', strtotime($o['pickup_date'] ?: $o['created_at']));
                            $order_status_attr = htmlspecialchars($o['status']);
                            echo '<div class="order-card" data-order-status="' . $order_status_attr . '" data-order-date="' . $order_date_attr . '">';
                            echo '<div class="card-header">';
                            echo '<h3 class="card-title">Pedido #' . htmlspecialchars($o['id']) . '</h3>';
                            // status dropdown (no centro do card removido; apenas select estilizado)
                            // render select with data-order-id so JS can be called with the element
                            $selPending = ($status === 'pending') ? 'selected' : '';
                            $selFinished = ($status === 'finished' || $status === 'delivered') ? 'selected' : '';
                            $selectClass = ($status === 'pending') ? 'status-select status-pending' : 'status-select status-finished';
                            $isDisabled = ($status === 'canceled') ? 'disabled' : '';
                            echo '<div class="status-dropdown-container">';
                            if ($status === 'canceled') {
                                echo '<span class="status-badge status-cancelado" style="    background-color: #cd1818ff; color: white;
    font-size: 14px;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 5px;
    white-space: nowrap;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    border: none;
    outline: none;
    background-repeat: no-repeat;
    background-position: right 5px center;
    padding-right: 25px;">🔴 Cancelado</span>';
                            } else {
                                echo '<select data-order-id="' . intval($o['id']) . '" class="' . $selectClass . '" onchange="updateStatus(this)" ' . $isDisabled . '>';
                                echo '<option value="pending" ' . $selPending . '>🟡 Pendente</option>';
                                echo '<option value="finished" ' . $selFinished . '>🟢 Entregue</option>';
                                echo '</select>';
                            }
                            echo '</div>';

                            echo '</div>';
                            echo '<div class="card-meta">';
                            echo '<span class="meta-item"><span class="meta-label">Cliente:</span> ' . htmlspecialchars($o['cliente_nome'] ?? 'Cliente') . '</span>';
                            echo '<span class="meta-item"><span class="meta-label">Data de Retirada:</span> ' . date('d/m/Y', strtotime($o['pickup_date'] ?? $o['created_at'])) . '</span>';
                            echo '<span class="meta-item"><span class="meta-label">Horário:</span> ' . htmlspecialchars($o['pickup_time'] ?? '') . '</span>';
                            echo '</div>';

                            echo '<ul class="card-items-list">';
                            echo '<li class="item-list-title">Produtos:</li>';
                            foreach ($o['items'] as $it) {
                                $prodName = 'Produto #' . $it['produto_id'];
                                try {
                                    $p = $produtoModel->getProdutoInfo($it['produto_id']);
                                    if (!empty($p) && !empty($p['nome'])) $prodName = $p['nome'];
                                } catch (Exception $e) {
                                }

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
                        echo '</div>'; // order-group
                    }
                }
                ?>
            </div>

        </div>
    </main>

    <script src="js/pedido.js"></script>
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

    <script>
        // Filters: data and status for empresa_pedido.php
        (function() {
            const dateInput = document.getElementById('filter-date');
            const statusSelect = document.getElementById('filter-status');
            if (!dateInput && !statusSelect) return;

            function applyFilters() {
                const groups = document.querySelectorAll('.order-group');
                const selDate = dateInput ? dateInput.value : '';
                const selStatus = statusSelect ? statusSelect.value : 'all';

                groups.forEach(group => {
                    let anyVisible = false;
                    const cards = group.querySelectorAll('.order-card');
                    cards.forEach(card => {
                        const cardDate = card.dataset.orderDate || '';
                        const cardStatus = card.dataset.orderStatus || '';

                        let matchDate = true;
                        if (selDate) {
                            matchDate = (cardDate === selDate);
                        }

                        let matchStatus = true;
                        if (selStatus && selStatus !== 'all') {
                            matchStatus = (cardStatus === selStatus);
                        }

                        if (matchDate && matchStatus) {
                            card.style.display = '';
                            anyVisible = true;
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    group.style.display = anyVisible ? '' : 'none';
                });
            }

            // force status select to 'all' on load to avoid browser-preserved state
            if (statusSelect) statusSelect.value = 'all';
            if (dateInput) dateInput.addEventListener('change', applyFilters);
            if (statusSelect) statusSelect.addEventListener('change', applyFilters);
            applyFilters();
        })();
    </script>
    <script>
        async function updateStatus(a, b) {
            // Accept either (orderId, status) or (selectElement)
            let orderId, status, selectEl;
            if (a && typeof a === 'object' && a.tagName === 'SELECT') {
                selectEl = a;
                orderId = selectEl.dataset.orderId;
                status = selectEl.value;
            } else {
                orderId = a;
                status = b;
                // try to find select element
                selectEl = document.querySelector('select[data-order-id="' + orderId + '"]');
            }

            if (!orderId || !status) return alert('Parâmetros inválidos');

            try {
                const resp = await fetch('../Controller/OrderController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'update_status',
                        order_id: orderId,
                        status: status
                    })
                });
                const data = await resp.json();
                if (data.success) {
                    // update select class to reflect new status
                    if (selectEl) {
                        if (status === 'pending') {
                            selectEl.className = 'status-select status-pending';
                        } else if (status === 'finished' || status === 'delivered') {
                            selectEl.className = 'status-select status-finished';
                        }
                    }
                } else {
                    alert('Erro ao atualizar status: ' + (data.message || ''));
                }
            } catch (e) {
                alert('Erro de rede ao atualizar status');
            }
        }
    </script>
</body>

</html>