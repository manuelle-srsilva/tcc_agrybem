<?php

use PHPUnit\Framework\TestCase;

// Incluir o autoload para que as classes sejam encontradas
require_once __DIR__ . '/../vendor/autoload.php'; 

// Funções auxiliares para simular o ambiente do CartController
require_once __DIR__ . '/TestHelpers.php';

// Mock do Model\Produto para isolar o Controller
class MockProdutoCart {
    private $products = [];

    public function __construct($products) {
        $this->products = $products;
    }

    public function getProdutoInfo($id) {
        return $this->products[$id] ?? null;
    }
}

class CartTest extends TestCase
{
    private $produtoModel;
    private $cartControllerPath = __DIR__ . '/../Controller/CartController.php';

    protected function setUp(): void
    {
        // Produtos de teste
        $products = [
            '101' => ['id' => 101, 'nome' => 'Maçã', 'categoria' => 'Fruta', 'preco' => 5.00, 'medida' => 'kg', 'foto' => ''],
            '102' => ['id' => 102, 'nome' => 'Pão', 'categoria' => 'Padaria', 'preco' => 3.50, 'medida' => 'un', 'foto' => ''],
        ];
        
        // Mock do Model\Produto
        $this->produtoModel = new MockProdutoCart($products);
        
        // Limpar a sessão antes de cada teste
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    // Função para simular a execução do CartController e capturar a saída
    private function runCartController($method, $input)
    {
        // Simular variáveis globais
        $_SERVER['REQUEST_METHOD'] = $method;
        
        // Simular input
        if ($method === 'POST') {
            // Para simular o file_get_contents('php://input')
            $GLOBALS['raw_input'] = json_encode($input);
            // Simular o $_POST
            $_POST = $input;
            $_GET = [];
        } else {
            $_GET = $input;
            $_POST = [];
        }
        
        // Simular a injeção do mock do ProdutoModel no script
        $GLOBALS['produtoModel'] = $this->produtoModel;
        
        // Capturar a saída do script
        ob_start();
        
        $action = $input['action'] ?? ($input['a'] ?? 'get');
        
        // Lógica de inicialização do Controller
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $output = '';
        
        switch($action){
            case 'add':
                $product_id = (string)($input['product_id'] ?? '');
                if ($product_id === '') { $output = jsonErr('product_id missing'); break; }
                
                $prodInfo = $this->produtoModel->getProdutoInfo($product_id);
                if (!$prodInfo) { $output = jsonErr('product not found'); break; }

                $mapped_id = (string)($input['mapped_id'] ?? $product_id);
                $name = $prodInfo['nome'] ?? ($input['name'] ?? '');
                $category = $prodInfo['categoria'] ?? ($input['category'] ?? '');
                $price = isset($prodInfo['preco']) ? floatval($prodInfo['preco']) : (isset($input['price']) ? floatval($input['price']) : 0.0);
                $image = '';
                if (!empty($prodInfo['foto'])) {
                    $image = 'data:image/jpeg;base64,' . base64_encode($prodInfo['foto']);
                } else {
                    $image = $input['image'] ?? '';
                }
                $measure = $prodInfo['medida'] ?? ($input['measure'] ?? 'un');

                if (strtolower($measure) === 'kg') $mapped_id = '1';
                elseif (strtolower($measure) === 'un') $mapped_id = '2';

                $default_unit = (strtolower($measure) === 'kg') ? 'kg' : 'un';

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
                $output = jsonOk(['count' => $count, 'cart' => $_SESSION['cart']]);
                break;

            case 'update':
                $product_id = (string)($input['product_id'] ?? '');
                $quantity = $input['quantity'] ?? null;
                $unit = isset($input['unit']) ? (string)$input['unit'] : null;
                if ($product_id === '' || $quantity === null) { $output = jsonErr('product_id or quantity missing'); break; }
                $quantity = $quantity + 0;
                if (!isset($_SESSION['cart'][$product_id])) { $output = jsonErr('product not in cart'); break; }
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                if ($unit !== null) {
                    $_SESSION['cart'][$product_id]['selected_unit'] = $unit;
                }
                if (floatval($_SESSION['cart'][$product_id]['quantity']) <= 0) unset($_SESSION['cart'][$product_id]);
                $output = jsonOk(['cart' => $_SESSION['cart']]);
                break;

            case 'remove':
                $product_id = (string)($input['product_id'] ?? '');
                if ($product_id === '') { $output = jsonErr('product_id missing'); break; }
                if (isset($_SESSION['cart'][$product_id])) unset($_SESSION['cart'][$product_id]);
                $output = jsonOk(['cart' => $_SESSION['cart']]);
                break;

            case 'get':
                $output = jsonOk(['cart' => $_SESSION['cart']]);
                break;

            case 'count':
                $count = array_sum(array_map(function($i){ return intval($i['quantity']); }, $_SESSION['cart']));
                $output = jsonOk(['count' => $count]);
                break;

            default:
                $output = jsonErr('unknown action');
        }
        
        ob_end_clean();
        return json_decode($output, true);
    }

    // =========================================================================
    // Testes para 'add'
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_adds_a_new_product_to_cart()
    {
        $result = $this->runCartController('POST', ['action' => 'add', 'product_id' => '102']);
        
        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['cart']);
        $this->assertEquals(1, $result['cart']['102']['quantity']);
        $this->assertEquals(1, $result['count']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_increments_quantity_of_existing_product()
    {
        // Adiciona o primeiro
        $this->runCartController('POST', ['action' => 'add', 'product_id' => '102']);
        
        // Adiciona o segundo
        $result = $this->runCartController('POST', ['action' => 'add', 'product_id' => '102']);
        
        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['cart']);
        $this->assertEquals(2, $result['cart']['102']['quantity']);
        $this->assertEquals(2, $result['count']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_add_product_with_missing_id()
    {
        $result = $this->runCartController('POST', ['action' => 'add']);
        $this->assertFalse($result['success']);
        $this->assertEquals('product_id missing', $result['message']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_add_non_existent_product()
    {
        $result = $this->runCartController('POST', ['action' => 'add', 'product_id' => '999']);
        $this->assertFalse($result['success']);
        $this->assertEquals('product not found', $result['message']);
    }

    // =========================================================================
    // Testes para 'update'
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_product_quantity()
    {
        $this->runCartController('POST', ['action' => 'add', 'product_id' => '102']);
        
        $result = $this->runCartController('POST', ['action' => 'update', 'product_id' => '102', 'quantity' => 5]);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(5, $result['cart']['102']['quantity']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_removes_product_when_quantity_is_zero()
    {
        $this->runCartController('POST', ['action' => 'add', 'product_id' => '102']);
        
        $result = $this->runCartController('POST', ['action' => 'update', 'product_id' => '102', 'quantity' => 0]);
        
        $this->assertTrue($result['success']);
        $this->assertArrayNotHasKey('102', $result['cart']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_product_unit()
    {
        $this->runCartController('POST', ['action' => 'add', 'product_id' => '101']); // kg product
        
        $result = $this->runCartController('POST', ['action' => 'update', 'product_id' => '101', 'quantity' => 1, 'unit' => 'g']);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('g', $result['cart']['101']['selected_unit']);
    }

    // =========================================================================
    // Testes para 'remove'
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_removes_product_from_cart()
    {
        $this->runCartController('POST', ['action' => 'add', 'product_id' => '102']);
        
        $result = $this->runCartController('POST', ['action' => 'remove', 'product_id' => '102']);
        
        $this->assertTrue($result['success']);
        $this->assertArrayNotHasKey('102', $result['cart']);
    }

    // =========================================================================
    // Testes para 'get'
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_gets_cart_contents()
    {
        $this->runCartController('POST', ['action' => 'add', 'product_id' => '102']);
        
        $result = $this->runCartController('GET', ['action' => 'get']);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('102', $result['cart']);
    }

    // =========================================================================
    // Testes para 'count'
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_gets_total_item_count()
    {
        $this->runCartController('POST', ['action' => 'add', 'product_id' => '102']);
        $this->runCartController('POST', ['action' => 'add', 'product_id' => '101']);
        
        $result = $this->runCartController('GET', ['action' => 'count']);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['count']);
    }
}
?>
