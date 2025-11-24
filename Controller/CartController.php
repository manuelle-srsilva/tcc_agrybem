<?php
namespace Controller;

require_once __DIR__ . '/../vendor/autoload.php';

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

$action = $input['action'] ?? ($input['a'] ?? 'get');

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function jsonOk($data = []){ echo json_encode(array_merge(['success' => true], $data)); exit; }
function jsonErr($msg = 'error') { echo json_encode(['success' => false, 'message' => $msg]); exit; }

$produtoModel = new Produto();

switch($action){
    case 'add':
        $product_id = (string)($input['product_id'] ?? '');
        if ($product_id === '') jsonErr('product_id missing');
        // validate product exists
        $prodInfo = $produtoModel->getProdutoInfo($product_id);
        if (!$prodInfo) jsonErr('product not found');

        // prefer server-side product info
        $mapped_id = (string)($input['mapped_id'] ?? $product_id);
        $name = $prodInfo['nome'] ?? ($input['name'] ?? '');
        $category = $prodInfo['categoria'] ?? ($input['category'] ?? '');
        $price = isset($prodInfo['preco']) ? floatval($prodInfo['preco']) : (isset($input['price']) ? floatval($input['price']) : 0.0);
        // Normalize image: if foto is stored as blob in DB, convert to data URI base64
        $image = '';
        if (!empty($prodInfo['foto'])) {
            $image = 'data:image/jpeg;base64,' . base64_encode($prodInfo['foto']);
        } else {
            $image = $input['image'] ?? '';
        }
        $measure = $prodInfo['medida'] ?? ($input['measure'] ?? 'un');

        // compute mapped id based on measure
        if (strtolower($measure) === 'kg') $mapped_id = '1';
        elseif (strtolower($measure) === 'un') $mapped_id = '2';

        // determine default selected unit for this product (kg -> kg, un -> un)
        $default_unit = (strtolower($measure) === 'kg') ? 'kg' : 'un';

        // If already in cart, increment
        if (isset($_SESSION['cart'][$product_id])){
            $_SESSION['cart'][$product_id]['quantity'] = intval($_SESSION['cart'][$product_id]['quantity']) + 1;
        } else {
            $_SESSION['cart'][$product_id] = [
                'mapped_id' => $mapped_id,
                'product_id' => $product_id,
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'image' => $image,
                'measure' => $measure,
                'quantity' => 1,
                'selected_unit' => $default_unit
            ];
        }

        $count = array_sum(array_map(function($i){ return intval($i['quantity']); }, $_SESSION['cart']));
        jsonOk(['count' => $count, 'cart' => $_SESSION['cart']]);
        break;

    case 'update':
        $product_id = (string)($input['product_id'] ?? '');
        $quantity = $input['quantity'] ?? null;
        $unit = isset($input['unit']) ? (string)$input['unit'] : null;
        if ($product_id === '' || $quantity === null) jsonErr('product_id or quantity missing');
        $quantity = $quantity + 0;
        if (!isset($_SESSION['cart'][$product_id])) jsonErr('product not in cart');
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        if ($unit !== null) {
            // store the selected unit (e.g., 'kg' or 'g' or 'un')
            $_SESSION['cart'][$product_id]['selected_unit'] = $unit;
        }
        if (floatval($_SESSION['cart'][$product_id]['quantity']) <= 0) unset($_SESSION['cart'][$product_id]);
        jsonOk(['cart' => $_SESSION['cart']]);
        break;

    case 'remove':
        $product_id = (string)($input['product_id'] ?? '');
        if ($product_id === '') jsonErr('product_id missing');
        if (isset($_SESSION['cart'][$product_id])) unset($_SESSION['cart'][$product_id]);
        jsonOk(['cart' => $_SESSION['cart']]);
        break;

    case 'get':
        jsonOk(['cart' => $_SESSION['cart']]);
        break;

    case 'count':
        $count = array_sum(array_map(function($i){ return intval($i['quantity']); }, $_SESSION['cart']));
        jsonOk(['count' => $count]);
        break;

    default:
        jsonErr('unknown action');
}

?>
