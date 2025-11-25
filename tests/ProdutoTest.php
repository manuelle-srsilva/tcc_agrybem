<?php

use PHPUnit\Framework\TestCase;
use Controller\ProdutoController;

// Incluir o autoload para que as classes sejam encontradas
require_once __DIR__ . '/../vendor/autoload.php'; 

// Classe auxiliar para permitir a injeção de dependência do Model mockado
class TestableProdutoController extends ProdutoController {
    protected $produtoModel;

    public function __construct($produtoModel = null) {
        if ($produtoModel) {
            // Usa Reflection para injetar o mock na propriedade privada/protegida
            $reflection = new \ReflectionClass($this);
            $property = $reflection->getProperty('produtoModel');
            $property->setAccessible(true);
            $property->setValue($this, $produtoModel);
        }
    }
}

class ProdutoTest extends TestCase
{
    private TestableProdutoController $produtoController;
    private $mockProdutoModel;

    protected function setUp(): void
    {
        $this->mockProdutoModel = $this->createMock(\Model\Produto::class);
        $this->produtoController = new TestableProdutoController($this->mockProdutoModel);
    }

    // =========================================================================
    // Testes para cadastroProduto($nome, $preco, $categoria, $medida, $foto, $id_empreendimento)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_a_product_successfully()
    {
        $args = ["Produto Teste", 10.50, "Categoria", "Unidade", "foto_binaria", 1];

        $this->mockProdutoModel
            ->expects($this->once())
            ->method('registerProduto')
            ->with(...$args)
            ->willReturn(true);

        $result = $this->produtoController->cadastroProduto(...$args);
        $this->assertTrue($result, "Deve retornar true para um cadastro válido.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_product_with_missing_fields()
    {
        $args = ["Produto Teste", 10.50, "Categoria", "Unidade", "foto_binaria", 1];
        
        // Teste com nome faltando
        $args[0] = "";
        $result = $this->produtoController->cadastroProduto(...$args);
        $this->assertFalse($result, "Deve retornar false se o nome estiver vazio.");
        
        // Teste com preço faltando
        $args[0] = "Produto Teste";
        $args[1] = null;
        $result = $this->produtoController->cadastroProduto(...$args);
        $this->assertFalse($result, "Deve retornar false se o preço estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_product_when_model_returns_false()
    {
        $args = ["Produto Teste", 10.50, "Categoria", "Unidade", "foto_binaria", 1];

        $this->mockProdutoModel
            ->expects($this->once())
            ->method('registerProduto')
            ->willReturn(false);

        $result = $this->produtoController->cadastroProduto(...$args);
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao registrar.");
    }

    // =========================================================================
    // Testes para updateproduto($id, $nome, $preco)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_product_successfully()
    {
        $id = 1;
        $nome = "Novo Nome";
        $preco = 20.99;

        $this->mockProdutoModel->expects($this->once())
            ->method('updateProduto')
            ->with($id, $nome, $preco)
            ->willReturn(true);

        $result = $this->produtoController->updateproduto($id, $nome, $preco);
        $this->assertTrue($result, "Deve retornar true para atualização bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_product_with_missing_fields()
    {
        // Teste com nome faltando
        $result = $this->produtoController->updateproduto(1, "", 20.99);
        $this->assertFalse($result, "Deve retornar false se o nome estiver vazio.");
        
        // Teste com preço faltando
        $result = $this->produtoController->updateproduto(1, "Nome", null);
        $this->assertFalse($result, "Deve retornar false se o preço estiver vazio.");
    }

    // =========================================================================
    // Testes para deleteProduto($id)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_product_successfully()
    {
        $id = 1;

        $this->mockProdutoModel->expects($this->once())
            ->method('deleteProduto')
            ->with($id)
            ->willReturn(true);

        $result = $this->produtoController->deleteProduto($id);
        $this->assertTrue($result, "Deve retornar true para exclusão bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_delete_product_with_missing_id()
    {
        $result = $this->produtoController->deleteProduto(null);
        $this->assertFalse($result, "Deve retornar false se o ID estiver vazio.");
    }

    // =========================================================================
    // Testes para getProdutoInfo e getProdutosByEmpreendimento (Controller não tem lógica)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getProdutoInfo_on_model()
    {
        $id = 1;
        $expectedResult = ['id' => $id, 'nome' => 'Produto'];

        $this->mockProdutoModel->expects($this->once())
            ->method('getProdutoInfo')
            ->with($id)
            ->willReturn($expectedResult);

        $result = $this->produtoController->getProdutoInfo($id);
        $this->assertEquals($expectedResult, $result, "Deve retornar o resultado do Model.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getProdutosByEmpreendimento_on_model()
    {
        $idEmpreendimento = 5;
        $expectedResult = [['id' => 1, 'nome' => 'Produto 1'], ['id' => 2, 'nome' => 'Produto 2']];

        $this->mockProdutoModel->expects($this->once())
            ->method('getProdutosByEmpreendimento')
            ->with($idEmpreendimento)
            ->willReturn($expectedResult);

        $result = $this->produtoController->getProdutosByEmpreendimento($idEmpreendimento);
        $this->assertEquals($expectedResult, $result, "Deve retornar a lista de produtos do empreendimento.");
    }
}
?>'''
