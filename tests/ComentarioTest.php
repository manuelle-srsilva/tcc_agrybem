<?php

use PHPUnit\Framework\TestCase;
use Controller\ComentarioController;

// Incluir o autoload para que as classes sejam encontradas
require_once __DIR__ . '/../vendor/autoload.php'; 

// Classe auxiliar para permitir a injeção de dependência do Model mockado
class TestableComentarioController extends ComentarioController {
    public function __construct($comentarioModel = null) {
        // Se um mock for passado, use-o. Caso contrário, use o Model real (comportamento original).
        if ($comentarioModel) {
            // Usa Reflection para injetar o mock na propriedade privada/protegida
            $reflection = new \ReflectionClass($this);
            $property = $reflection->getProperty('comentarioModel');
            $property->setAccessible(true);
            $property->setValue($this, $comentarioModel);
        }
    }
    
    // Sobrescreve o construtor original que não aceita argumentos
    public function __construct_original() {
        // Não chama parent::__construct() para evitar inicialização do Model real
    }
}

class ComentarioTest extends TestCase
{
    private TestableComentarioController $comentarioController;
    private $mockComentarioModel;

    protected function setUp(): void
    {
        // Mock do Model\Comentario para isolar o Controller
        $this->mockComentarioModel = $this->createMock(\Model\Comentario::class);
        
        // Injetar o mock no Controller Testável
        $this->comentarioController = new TestableComentarioController($this->mockComentarioModel);
    }

    // =========================================================================
    // Testes para cadastroComentario($comentario, $id_cliente, $id_empreendimento = null)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_a_comment_successfully_without_empreendimento()
    {
        $comentario = "Ótimo produto!";
        $idCliente = 5;
        $idEmpreendimento = null;

        $this->mockComentarioModel
            ->expects($this->once())
            ->method('registerComentario')
            ->with($comentario, $idCliente, $idEmpreendimento)
            ->willReturn(true);

        $result = $this->comentarioController->cadastroComentario($comentario, $idCliente);
        $this->assertTrue($result, "Deve retornar true para um cadastro válido sem empreendimento.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_a_comment_successfully_with_empreendimento()
    {
        $comentario = "Excelente serviço!";
        $idCliente = 5;
        $idEmpreendimento = 10;

        $this->mockComentarioModel
            ->expects($this->once())
            ->method('registerComentario')
            ->with($comentario, $idCliente, $idEmpreendimento)
            ->willReturn(true);

        $result = $this->comentarioController->cadastroComentario($comentario, $idCliente, $idEmpreendimento);
        $this->assertTrue($result, "Deve retornar true para um cadastro válido com empreendimento.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_comment_with_missing_comentario()
    {
        $result = $this->comentarioController->cadastroComentario("", 5);
        $this->assertFalse($result, "Deve retornar false se o comentário estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_comment_with_missing_id_cliente()
    {
        $result = $this->comentarioController->cadastroComentario("Comentário", null);
        $this->assertFalse($result, "Deve retornar false se o id_cliente estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_comment_when_model_returns_false()
    {
        $this->mockComentarioModel
            ->expects($this->once())
            ->method('registerComentario')
            ->willReturn(false);

        $result = $this->comentarioController->cadastroComentario("Comentário", 5);
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao registrar.");
    }

    // =========================================================================
    // Testes para updateComentario($id, $comentario)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_comment_successfully()
    {
        $id = 1;
        $novoComentario = "Comentário atualizado.";

        $this->mockComentarioModel->expects($this->once())
            ->method('updateComentario')
            ->with($id, $novoComentario)
            ->willReturn(true);

        $result = $this->comentarioController->updateComentario($id, $novoComentario);
        $this->assertTrue($result, "Deve retornar true para atualização bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_comment_with_missing_comentario()
    {
        $result = $this->comentarioController->updateComentario(1, "");
        $this->assertFalse($result, "Deve retornar false se o comentário estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_comment_with_missing_id()
    {
        $result = $this->comentarioController->updateComentario(null, "Comentário");
        $this->assertFalse($result, "Deve retornar false se o ID estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_comment_when_model_returns_false()
    {
        $this->mockComentarioModel->expects($this->once())
            ->method('updateComentario')
            ->willReturn(false);

        $result = $this->comentarioController->updateComentario(1, "Comentário");
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao atualizar.");
    }

    // =========================================================================
    // Testes para deleteComentario($id)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_comment_successfully()
    {
        $id = 1;

        $this->mockComentarioModel->expects($this->once())
            ->method('deleteComentario')
            ->with($id)
            ->willReturn(true);

        $result = $this->comentarioController->deleteComentario($id);
        $this->assertTrue($result, "Deve retornar true para exclusão bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_delete_comment_with_missing_id()
    {
        $result = $this->comentarioController->deleteComentario(null);
        $this->assertFalse($result, "Deve retornar false se o ID estiver vazio.");
    }

    // =========================================================================
    // Testes para listarComentarios($id_empreendimento = null)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_lists_all_comments_when_no_empreendimento_id_is_provided()
    {
        $expectedComments = [
            ['id' => 1, 'comentario' => 'Comentário 1'],
            ['id' => 2, 'comentario' => 'Comentário 2']
        ];

        $this->mockComentarioModel->expects($this->once())
            ->method('listComentarios')
            ->with(null)
            ->willReturn($expectedComments);

        $result = $this->comentarioController->listarComentarios();
        $this->assertEquals($expectedComments, $result, "Deve retornar a lista de todos os comentários.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_lists_comments_by_empreendimento_id()
    {
        $idEmpreendimento = 10;
        $expectedComments = [
            ['id' => 3, 'comentario' => 'Comentário 3', 'id_empreendimento' => $idEmpreendimento]
        ];

        $this->mockComentarioModel->expects($this->once())
            ->method('listComentarios')
            ->with($idEmpreendimento)
            ->willReturn($expectedComments);

        $result = $this->comentarioController->listarComentarios($idEmpreendimento);
        $this->assertEquals($expectedComments, $result, "Deve retornar a lista de comentários para o empreendimento específico.");
    }

    // =========================================================================
    // Testes para countComentarios($id_empreendimento = null)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_counts_all_comments_when_no_empreendimento_id_is_provided()
    {
        $expectedCount = 5;

        $this->mockComentarioModel->expects($this->once())
            ->method('countComentarios')
            ->with(null)
            ->willReturn($expectedCount);

        $result = $this->comentarioController->countComentarios();
        $this->assertEquals($expectedCount, $result, "Deve retornar a contagem total de comentários.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_counts_comments_by_empreendimento_id()
    {
        $idEmpreendimento = 10;
        $expectedCount = 2;

        $this->mockComentarioModel->expects($this->once())
            ->method('countComentarios')
            ->with($idEmpreendimento)
            ->willReturn($expectedCount);

        $result = $this->comentarioController->countComentarios($idEmpreendimento);
        $this->assertEquals($expectedCount, $result, "Deve retornar a contagem de comentários para o empreendimento específico.");
    }
    
    // =========================================================================
    // Testes para getComentario (Controller não tem lógica)
    // =========================================================================
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getComentario_on_model()
    {
        $id = 1;
        $comentario = "Comentário";
        $idCliente = 5;
        $dataPost = "2023-01-01";
        $expectedResult = ['id' => $id, 'comentario' => $comentario];

        $this->mockComentarioModel->expects($this->once())
            ->method('getComentarioInfo')
            ->with($id, $comentario, $idCliente, $dataPost)
            ->willReturn($expectedResult);

        $result = $this->comentarioController->getComentario($id, $comentario, $idCliente, $dataPost);
        $this->assertEquals($expectedResult, $result, "Deve retornar o resultado do Model.");
    }
}
?>
