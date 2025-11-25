<?php

use PHPUnit\Framework\TestCase;
use Controller\EmpreendimentoController;

// Incluir o autoload para que as classes sejam encontradas
require_once __DIR__ . '/../vendor/autoload.php'; 

// Classe auxiliar para permitir a injeção de dependência do Model mockado
class TestableEmpreendimentoController extends EmpreendimentoController {
    protected $empreendimentoModel;

    public function __construct($empreendimentoModel = null) {
        if ($empreendimentoModel) {
            // Usa Reflection para injetar o mock na propriedade privada/protegida
            $reflection = new \ReflectionClass($this);
            $property = $reflection->getProperty('empreendimentoModel');
            $property->setAccessible(true);
            $property->setValue($this, $empreendimentoModel);
        }
    }
}

class EmpreendimentoTest extends TestCase
{
    private TestableEmpreendimentoController $empreendimentoController;
    private $mockEmpreendimentoModel;

    protected function setUp(): void
    {
        $this->mockEmpreendimentoModel = $this->createMock(\Model\Empreendimento::class);
        $this->empreendimentoController = new TestableEmpreendimentoController($this->mockEmpreendimentoModel);
    }

    // =========================================================================
    // Testes para cadastroEmpreendimento($nome, $cnpj, $id_empreendedor)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_empreendimento_successfully()
    {
        $nome = "Empreendimento Teste";
        $cnpj = "12345678901234";
        $idEmpreendedor = 1;

        $this->mockEmpreendimentoModel
            ->expects($this->once())
            ->method('registerEmpreendimento')
            ->with($nome, $cnpj, $idEmpreendedor)
            ->willReturn(true);

        $result = $this->empreendimentoController->cadastroEmpreendimento($nome, $cnpj, $idEmpreendedor);
        $this->assertTrue($result, "Deve retornar true para um cadastro válido.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_empreendimento_with_missing_fields()
    {
        // Teste com nome faltando
        $result = $this->empreendimentoController->cadastroEmpreendimento("", "12345678901234", 1);
        $this->assertFalse($result, "Deve retornar false se o nome estiver vazio.");
        
        // Teste com CNPJ faltando
        $result = $this->empreendimentoController->cadastroEmpreendimento("Nome", "", 1);
        $this->assertFalse($result, "Deve retornar false se o CNPJ estiver vazio.");
        
        // Teste com ID Empreendedor faltando
        $result = $this->empreendimentoController->cadastroEmpreendimento("Nome", "12345678901234", null);
        $this->assertFalse($result, "Deve retornar false se o ID Empreendedor estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_empreendimento_when_model_returns_false()
    {
        $this->mockEmpreendimentoModel
            ->expects($this->once())
            ->method('registerEmpreendimento')
            ->willReturn(false);

        $result = $this->empreendimentoController->cadastroEmpreendimento("Nome", "12345678901234", 1);
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao registrar.");
    }

    // =========================================================================
    // Testes para updateEmpreendimento($id, $nome, $cnpj)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_empreendimento_successfully()
    {
        $id = 1;
        $nome = "Novo Nome";
        $cnpj = "43210987654321";

        $this->mockEmpreendimentoModel->expects($this->once())
            ->method('updateEmpreendimento')
            ->with($id, $nome, $cnpj)
            ->willReturn(true);

        $result = $this->empreendimentoController->updateEmpreendimento($id, $nome, $cnpj);
        $this->assertTrue($result, "Deve retornar true para atualização bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_empreendimento_with_missing_fields()
    {
        // Teste com ID faltando
        $result = $this->empreendimentoController->updateEmpreendimento(null, "Nome", "43210987654321");
        $this->assertFalse($result, "Deve retornar false se o ID estiver vazio.");
        
        // Teste com nome faltando
        $result = $this->empreendimentoController->updateEmpreendimento(1, "", "43210987654321");
        $this->assertFalse($result, "Deve retornar false se o nome estiver vazio.");
    }

    // =========================================================================
    // Testes para deleteEmpreendimento($id)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_empreendimento_successfully()
    {
        $id = 1;

        $this->mockEmpreendimentoModel->expects($this->once())
            ->method('deleteEmpreendimento')
            ->with($id)
            ->willReturn(true);

        $result = $this->empreendimentoController->deleteEmpreendimento($id);
        $this->assertTrue($result, "Deve retornar true para exclusão bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_delete_empreendimento_with_missing_id()
    {
        $result = $this->empreendimentoController->deleteEmpreendimento(null);
        $this->assertFalse($result, "Deve retornar false se o ID estiver vazio.");
    }

    // =========================================================================
    // Testes para getEmpreendimentoInfo($id, $nome, $cnpj)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getEmpreendimentoInfo_on_model()
    {
        $id = 1;
        $nome = "Nome";
        $cnpj = "12345678901234";
        $expectedResult = ['id' => $id, 'nome' => $nome];

        $this->mockEmpreendimentoModel->expects($this->once())
            ->method('getEmpreendimentoInfo')
            ->with($id, $nome, $cnpj)
            ->willReturn($expectedResult);

        $result = $this->empreendimentoController->getEmpreendimentoInfo($id, $nome, $cnpj);
        $this->assertEquals($expectedResult, $result, "Deve retornar o resultado do Model.");
    }
}
?>
