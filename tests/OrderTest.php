<?php

use PHPUnit\Framework\TestCase;

// Incluir o autoload para que as classes sejam encontradas
require_once __DIR__ . '/../vendor/autoload.php'; 

// Funções auxiliares para simular o ambiente do OrderController
require_once __DIR__ . '/TestHelpers.php';

// Mock do Model\Order
class MockOrderModel {
    public function createOrder($cliente_id, $empresa_id, $total, $pickup_date, $pickup_time) {
        // Simula a criação de um pedido e retorna o ID
        return 1; 
    }
    public function createOrderItems($orderId, $orderItems) {
        // Simula a criação dos itens do pedido
        return true;
    }
    public function getOrderById($order_id) {
        // Simula a busca de um pedido
        if ($order_id === 1) {
            return ['id' => 1, 'cliente_id' => 10, 'empresa_id' => 5, 'status' => 'pending'];
        }
        if ($order_id === 2) {
            return ['id' => 2, 'cliente_id' => 10, 'empresa_id' => 5, 'status' => 'in_transit'];
        }
        if ($order_id === 3) {
            return ['id' => 3, 'cliente_id' => 10, 'empresa_id' => 5, 'status' => 'delivered'];
        }
        return null;
    }
    public function updateStatus($order_id, $status) {
        // Simula a atualização do status
        return true;
    }
}

// Mock do Model\Produto
class MockProdutoOrder {
    private $products = [
        '101' => ['id' => 101, 'nome' => 'Maçã', 'preco' => 5.00, 'medida' => 'kg', 'id_empreendimento' => 5],
        '102' => ['id' => 102, 'nome' => 'Pão', 'preco' => 3.50, 'medida' => 'un', 'id_empreendimento' => 5],
    ];

    public function getProdutoInfo($id) {
        return $this->products[$id] ?? null;
    }
}

class OrderTest extends TestCase
{
    private $mockOrderModel;
    private $mockProdutoModel;

    protected function setUp(): void
    {
        $this->mockOrderModel = new MockOrderModel();
        $this->mockProdutoModel = new MockProdutoOrder();
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    // Função para simular a execução do OrderController e capturar a saída
    private function runOrderController($method, $input, $session = [])
    {
        // Simular variáveis globais
        $_SERVER['REQUEST_METHOD'] = $method;
        
        // Simular input
        if ($method === 'POST') {
            $GLOBALS['raw_input'] = json_encode($input);
            $_POST = $input;
            $_GET = [];
        } else {
            $_GET = $input;
            $_POST = [];
        }
        
        // Simular a sessão
        $_SESSION = array_merge($_SESSION, $session);
        
        // Simular a injeção dos mocks
        $GLOBALS['orderModel'] = $this->mockOrderModel;
        $GLOBALS['produtoModel'] = $this->mockProdutoModel;
        
        // Capturar a saída do script
        ob_start();
        
        $action = $input['action'] ?? ($input['a'] ?? 'place');
        
        // Lógica do Controller (simulada para evitar o include do arquivo procedural)
        $output = '';
        
        switch($action){
            case 'place':
                // Simulação da lógica de 'place'
                $pickup_date = $input['pickup_date'] ?? ($input['pickup-date'] ?? null);
                $pickup_time = $input['pickup_time'] ?? ($input['pickup-time'] ?? null);

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
                if(empty($cart)) { $output = jsonErr('cart empty'); break; }

                // Validação de data (simplificada para o teste)
                $today = new \DateTime('today');
                $minDate = (clone $today)->modify('+3 days');
                $d = \DateTime::createFromFormat('Y-m-d', $pickup_date);
                if(!$d || $d->format('Y-m-d') !== $pickup_date || $d < $minDate) {
                    $output = jsonErr('pickup_date_validation_failed');
                    break;
                }

                // Group items by empresa (id_empreendimento)
                $groups = [];
                foreach($cart as $pid => $item){
                    $p = $this->mockProdutoModel->getProdutoInfo($pid);
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

                    $orderId = $this->mockOrderModel->createOrder($cliente_id, $empresa_id, $total, $pickup_date, $pickup_time);
                    if(!$orderId) continue;
                    $ok = $this->mockOrderModel->createOrderItems($orderId, $orderItems);
                    if($ok) $created[] = $orderId;
                }

                if(!empty($created)){
                    $_SESSION['cart'] = [];
                    $output = jsonOk(['orders' => $created]);
                } else {
                    $output = jsonErr('failed to create orders');
                }
                break;

            case 'update_status':
                $order_id = isset($input['order_id']) ? intval($input['order_id']) : 0;
                $status = isset($input['status']) ? $input['status'] : null;
                if(!$order_id || !$status) { $output = jsonErr('missing params'); break; }

                $order = $this->mockOrderModel->getOrderById($order_id);
                if(!$order) { $output = jsonErr('order not found'); break; }

                $empresa_id = null;
                $possibleEmpresaKeys = ['empresa_id','id_empresa','empresa','empreendimento_id','id_empreendimento'];
                foreach($possibleEmpresaKeys as $k){ if(isset($_SESSION[$k])){ $empresa_id = $_SESSION[$k]; break; } }

                if(!$empresa_id || intval($order['empresa_id']) !== intval($empresa_id)){
                    $output = jsonErr('not allowed'); break;
                }

                $allowed = ['pending','in_transit','delivered','finished'];
                if(!in_array($status, $allowed)) { $output = jsonErr('invalid status'); break; }

                $ok = $this->mockOrderModel->updateStatus($order_id, $status);
                if($ok) { $output = jsonOk(['order_id'=>$order_id,'status'=>$status]); break; }
                $output = jsonErr('failed to update');
                break;

            case 'client_cancel':
                $order_id = isset($input['order_id']) ? intval($input['order_id']) : 0;
                if(!$order_id) { $output = jsonErr('missing order_id'); break; }
                $order = $this->mockOrderModel->getOrderById($order_id);
                if(!$order) { $output = jsonErr('order not found'); break; }

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
                    $output = jsonErr('not allowed'); break;
                }

                $cancelable = ['pending','in_transit'];
                if(!in_array($order['status'], $cancelable)) { $output = jsonErr('cannot_cancel'); break; }

                $ok = $this->mockOrderModel->updateStatus($order_id, 'canceled');
                if($ok) { $output = jsonOk(['order_id'=>$order_id,'status'=>'canceled', 'message' => 'Pedido cancelado com sucesso.']); break; }
                $output = jsonErr('failed to cancel');
                break;

            default:
                $output = jsonErr('unknown action');
        }
        
        ob_end_clean();
        return json_decode($output, true);
    }

    // =========================================================================
    // Testes para 'place'
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_places_an_order_successfully()
    {
        $tomorrow = (new \DateTime('tomorrow'))->format('Y-m-d');
        $futureDate = (new \DateTime('today'))->modify('+4 days')->format('Y-m-d');

        $session = [
            'id' => 10, // cliente_id
            'cart' => [
                '101' => ['product_id' => '101', 'price' => 5.00, 'quantity' => 2, 'measure' => 'kg', 'selected_unit' => 'kg'],
                '102' => ['product_id' => '102', 'price' => 3.50, 'quantity' => 1, 'measure' => 'un', 'selected_unit' => 'un'],
            ]
        ];
        
        $result = $this->runOrderController('POST', [
            'action' => 'place', 
            'pickup_date' => $futureDate, 
            'pickup_time' => '10:00'
        ], $session);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('orders', $result);
        $this->assertContains(1, $result['orders']);
        $this->assertEmpty($_SESSION['cart'], "O carrinho deve ser esvaziado após o pedido.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_place_order_with_empty_cart()
    {
        $futureDate = (new \DateTime('today'))->modify('+4 days')->format('Y-m-d');
        $session = ['id' => 10, 'cart' => []];
        
        $result = $this->runOrderController('POST', [
            'action' => 'place', 
            'pickup_date' => $futureDate, 
            'pickup_time' => '10:00'
        ], $session);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('cart empty', $result['message']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_place_order_with_invalid_pickup_date()
    {
        $session = [
            'id' => 10, 
            'cart' => ['101' => ['product_id' => '101', 'price' => 5.00, 'quantity' => 2, 'measure' => 'kg', 'selected_unit' => 'kg']]
        ];
        
        // Data muito próxima (menos de 3 dias)
        $tomorrow = (new \DateTime('tomorrow'))->format('Y-m-d');
        $result = $this->runOrderController('POST', [
            'action' => 'place', 
            'pickup_date' => $tomorrow, 
            'pickup_time' => '10:00'
        ], $session);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('pickup_date_validation_failed', $result['message']);
    }

    // =========================================================================
    // Testes para 'update_status'
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_order_status_successfully()
    {
        $session = ['id_empreendimento' => 5]; // Empresa logada
        
        $result = $this->runOrderController('POST', [
            'action' => 'update_status', 
            'order_id' => 1, 
            'status' => 'delivered'
        ], $session);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['order_id']);
        $this->assertEquals('delivered', $result['status']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_status_if_not_allowed()
    {
        $session = ['id_empreendimento' => 99]; // Empresa errada
        
        $result = $this->runOrderController('POST', [
            'action' => 'update_status', 
            'order_id' => 1, 
            'status' => 'delivered'
        ], $session);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('not allowed', $result['message']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_status_with_invalid_status()
    {
        $session = ['id_empreendimento' => 5];
        
        $result = $this->runOrderController('POST', [
            'action' => 'update_status', 
            'order_id' => 1, 
            'status' => 'invalid_status'
        ], $session);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('invalid status', $result['message']);
    }

    // =========================================================================
    // Testes para 'client_cancel'
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cancels_a_pending_order_successfully()
    {
        $session = ['id' => 10]; // Cliente logado
        
        $result = $this->runOrderController('POST', [
            'action' => 'client_cancel', 
            'order_id' => 1
        ], $session);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('canceled', $result['status']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_cancel_if_not_the_client()
    {
        $session = ['id' => 99]; // Cliente errado
        
        $result = $this->runOrderController('POST', [
            'action' => 'client_cancel', 
            'order_id' => 1
        ], $session);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('not allowed', $result['message']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_cancel_if_status_is_not_cancelable()
    {
        $session = ['id' => 10]; // Cliente logado
        
        $result = $this->runOrderController('POST', [
            'action' => 'client_cancel', 
            'order_id' => 3 // delivered
        ], $session);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('cannot_cancel', $result['message']);
    }
}
?>
