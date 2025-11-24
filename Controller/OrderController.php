<?php
namespace Controller;

require_once __DIR__ . '/../vendor/autoload.php';

use Model\Order;
use Model\Produto;

session_start();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$input = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? $_POST;
} else {
    $input = $_GET;
}

$action = $input['action'] ?? ($input['a'] ?? 'place');

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function jsonOk($data = []){ echo json_encode(array_merge(['success' => true], $data)); exit; }
function jsonErr($msg = 'error') { echo json_encode(['success' => false, 'message' => $msg]); exit; }

$orderModel = new Order();
$produtoModel = new Produto();

switch($action){
    case 'place':
        // Expect pickup_date, pickup_time, cliente_id may come from session
        // accept both snake_case and dash-style field names from forms
        $pickup_date = $input['pickup_date'] ?? ($input['pickup-date'] ?? null);
        $pickup_time = $input['pickup_time'] ?? ($input['pickup-time'] ?? null);

        // Determine cliente_id from session using several common keys
        $cliente_id = null;
        $possibleKeys = [
            'cliente_id','id_cliente','id','clienteId','cliente','user','user_id','userId','id_usuario','user_id'
        ];
        foreach($possibleKeys as $k){
            if(isset($_SESSION[$k])){
                if(is_array($_SESSION[$k])){
                    if(isset($_SESSION[$k]['id'])){ $cliente_id = $_SESSION[$k]['id']; break; }
                    if(isset($_SESSION[$k]['cliente_id'])){ $cliente_id = $_SESSION[$k]['cliente_id']; break; }
                    if(isset($_SESSION[$k]['id_cliente'])){ $cliente_id = $_SESSION[$k]['id_cliente']; break; }
                } else {
                    $cliente_id = $_SESSION[$k];
                    break;
                }
            }
        }

        $cart = $_SESSION['cart'] ?? [];
        if(empty($cart)) jsonErr('cart empty');

        // Enforce pickup_date present and at least 3 days from today
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'],'application/json') !== false);

        if(empty($pickup_date)){
            if($isAjax) jsonErr('pickup_date_required');
            header('Location: ../View/checkout.php?success=0&error=pickup_date_required');
            exit;
        }

        $d = \DateTime::createFromFormat('Y-m-d', $pickup_date);
        if(!$d || $d->format('Y-m-d') !== $pickup_date){
            if($isAjax) jsonErr('invalid_pickup_date');
            header('Location: ../View/checkout.php?success=0&error=invalid_pickup_date');
            exit;
        }

        $today = new \DateTime('today');
        $minDate = (clone $today)->modify('+3 days');
        if($d < $minDate){
            if($isAjax) jsonErr('pickup_date_too_soon');
            header('Location: ../View/checkout.php?success=0&error=pickup_date_too_soon');
            exit;
        }

        // Group items by empresa (id_empreendimento)
        $groups = [];
        foreach($cart as $pid => $item){
            // fetch product to get id_empreendimento
            $p = $produtoModel->getProdutoInfo($pid);
            $empresa_id = $p['id_empreendimento'] ?? null;
            if(!isset($groups[$empresa_id])) $groups[$empresa_id] = [];
            $groups[$empresa_id][$pid] = $item;
        }

        $created = [];
        foreach($groups as $empresa_id => $items){
            $total = 0.0;
            $orderItems = [];
            foreach($items as $pid => $it){
                $unit = $it['selected_unit'] ?? ($it['measure'] === 'kg' ? 'kg' : 'un');
                $qty = floatval($it['quantity'] ?? 0);
                $unitPrice = floatval($it['price'] ?? 0);
                // compute item total accounting for g
                $itemTotal = ($unit === 'g') ? $unitPrice * ($qty/1000) : $unitPrice * $qty;
                $total += $itemTotal;
                $orderItems[] = [
                    'produto_id' => $pid,
                    'quantidade' => $qty,
                    'unidade' => $unit,
                    'preco_unitario' => $unitPrice,
                    'total_item' => $itemTotal
                ];
            }

            $orderId = $orderModel->createOrder($cliente_id, $empresa_id, $total, $pickup_date, $pickup_time);
            if(!$orderId) continue;
            $ok = $orderModel->createOrderItems($orderId, $orderItems);
            if($ok) $created[] = $orderId;
        }

        // remove those items from session cart
        foreach($created as $orderId){
            // For simplicity, clear entire cart if any order created
            // (alternatively could remove only items that were ordered)
            $_SESSION['cart'] = [];
        }

        if(!empty($created)){
            // if request was a normal form POST, redirect to checkout with success
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'],'application/json') !== false);
            if($isAjax){
                jsonOk(['orders' => $created]);
            } else {
                $ordersStr = implode(',', $created);
                header('Location: ../View/checkout.php?success=1&orders=' . urlencode($ordersStr));
                exit;
            }
        }
        // failed
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'],'application/json') !== false);
        if($isAjax) jsonErr('failed to create orders');
        header('Location: ../View/checkout.php?success=0');
        exit;
        
    case 'update_status':
        // expects order_id and status (AJAX)
        $order_id = isset($input['order_id']) ? intval($input['order_id']) : 0;
        $status = isset($input['status']) ? $input['status'] : null;
        if(!$order_id || !$status) jsonErr('missing params');

        // Check that current session user is the empresa owner of this order
        $order = $orderModel->getOrderById($order_id);
        if(!$order) jsonErr('order not found');

        // determine empresa id from session (like in view)
        $empresa_id = null;
        $possibleEmpresaKeys = ['empresa_id','id_empresa','empresa','empreendimento_id','id_empreendimento'];
        foreach($possibleEmpresaKeys as $k){ if(isset($_SESSION[$k])){ $empresa_id = $_SESSION[$k]; break; } }

        if($empresa_id && intval($order['empresa_id']) !== intval($empresa_id)){
            jsonErr('not allowed');
        }

        // Only allow certain statuses
        $allowed = ['pending','in_transit','delivered','finished'];
        if(!in_array($status, $allowed)) jsonErr('invalid status');

        $ok = $orderModel->updateStatus($order_id, $status);
        if($ok) jsonOk(['order_id'=>$order_id,'status'=>$status]);
        jsonErr('failed to update');

    case 'client_cancel':
        // client requests to cancel their own order
        $order_id = isset($input['order_id']) ? intval($input['order_id']) : 0;
        if(!$order_id) jsonErr('missing order_id');
        $order = $orderModel->getOrderById($order_id);
        if(!$order) jsonErr('order not found');

        // determine cliente id from session using similar keys as above
        $cliente_id = null;
        $possibleKeys = [
            'cliente_id','id_cliente','id','clienteId','cliente','user','user_id','userId','id_usuario','user_id'
        ];
        foreach($possibleKeys as $k){
            if(isset($_SESSION[$k])){
                if(is_array($_SESSION[$k])){
                    if(isset($_SESSION[$k]['id'])){ $cliente_id = $_SESSION[$k]['id']; break; }
                    if(isset($_SESSION[$k]['cliente_id'])){ $cliente_id = $_SESSION[$k]['cliente_id']; break; }
                } else {
                    $cliente_id = $_SESSION[$k];
                    break;
                }
            }
        }

        if(!$cliente_id || intval($order['cliente_id']) !== intval($cliente_id)){
            jsonErr('not allowed');
        }

        // allow cancellation only from certain current statuses
        $cancelable = ['pending','in_transit'];
        if(!in_array($order['status'], $cancelable)) jsonErr('cannot_cancel');

        $ok = $orderModel->updateStatus($order_id, 'canceled');
        if($ok) jsonOk(['order_id'=>$order_id,'status'=>'canceled', 'message' => 'Pedido cancelado com sucesso.']);
        jsonErr('failed to cancel');

    default:
        jsonErr('unknown action');
}

?>