<?php

use PHPUnit\Framework\TestCase;
use Controller\InstituicaoController;

// Incluir o autoload para que as classes sejam encontradas
require_once __DIR__ . '/../vendor/autoload.php'; 

// Classe auxiliar para permitir a injeção de dependência do Model mockado
class TestableInstituicaoController extends InstituicaoController {
    protected $instituicaoModel;

    public function __construct($instituicaoModel = null) {
        if ($instituicaoModel) {
            // Usa Reflection para injetar o mock na propriedade privada/protegida
            $reflection = new \ReflectionClass($this);
            $property = $reflection->getProperty('instituicaoModel');
            $property->setAccessible(true);
            $property->setValue($this, $instituicaoModel);
        }
    }
}

class InstituicaoTest extends TestCase
{
    private TestableInstituicaoController $instituicaoController;
    private $mockInstituicaoModel;

    private const VALID_PASSWORD = 'senha_valida_123';
    private const HASHED_PASSWORD = '$2y$10$42Hn/S3.3j1c3x4y5z6a1O5r/T5c0p1r8L1m2o7F4P3k0J1s2i'; // Senha de exemplo hasheada

    protected function setUp(): void
    {
        $this->mockInstituicaoModel = $this->createMock(\Model\Instituicao::class);
        $this->instituicaoController = new TestableInstituicaoController($this->mockInstituicaoModel);
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    // =========================================================================
    // Testes para salvarInstituicao, salvarInstituicaoDescricao, salvarInstituicaoFoto
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_saves_instituicao_step1_to_session_successfully()
    {
        $nome = "Inst Teste";
        $email = "inst@teste.com";
        $cnpj = "12345678000190";
        $link = "link_wa";

        $result = $this->instituicaoController->salvarInstituicao($nome, $email, self::VALID_PASSWORD, $cnpj, $link);
        
        $this->assertTrue($result);
        $this->assertEquals($nome, $_SESSION['form_instituicao']['instituicao1']['nome']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_saves_instituicao_step2_to_session_successfully()
    {
        $descricao = "Descrição da Instituição";

        $result = $this->instituicaoController->salvarInstituicaoDescricao($descricao);
        
        $this->assertTrue($result);
        $this->assertEquals($descricao, $_SESSION['form_instituicao']['instituicao2']['descricao']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_saves_instituicao_step3_to_session_successfully()
    {
        $foto = "dados_binarios_da_foto";

        $result = $this->instituicaoController->salvarInstituicaoFoto($foto);
        
        $this->assertTrue($result);
        $this->assertEquals($foto, $_SESSION['form_instituicao']['instituicao3']['foto']);
    }

    // =========================================================================
    // Testes para registerInstituicao($nome, $email, $senha, $cnpj, $link_whatsapp, $descricao, $foto, $id_endereco)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_instituicao_successfully()
    {
        $args = ["Nome", "email@teste.com", self::VALID_PASSWORD, "12345678000190", "link_wa", "Descricao", "foto", 1];

        $this->mockInstituicaoModel
            ->expects($this->once())
            ->method('registerInstituicao')
            ->with(...$args)
            ->willReturn(true);

        $result = $this->instituicaoController->registerInstituicao(...$args);
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_instituicao_with_missing_fields()
    {
        $args = ["Nome", "email@teste.com", self::VALID_PASSWORD, "12345678000190", "link_wa", "Descricao", "foto", 1];
        
        // Teste com nome faltando
        $args[0] = "";
        $result = $this->instituicaoController->registerInstituicao(...$args);
        $this->assertFalse($result);
    }

    // =========================================================================
    // Testes para loginInstituicao($cnpj, $senha)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_in_instituicao_with_correct_credentials()
    {
        $cnpj = '12345678000190';
        $password = self::VALID_PASSWORD;
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $instituicao = [
            "id" => 10,
            "nome" => "Inst Logada",
            "cnpj" => $cnpj,
            "senha" => $hashed,
            "id_endereco" => 5
        ];

        $this->mockInstituicaoModel->expects($this->once())
            ->method('getInstituicaoByCNPJ')
            ->with($cnpj)
            ->willReturn($instituicao);

        $result = $this->instituicaoController->loginInstituicao($cnpj, $password);

        $this->assertTrue($result);
        $this->assertEquals(10, $_SESSION['id_instituicao']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_login_with_incorrect_password()
    {
        $cnpj = '12345678000190';
        $this->mockInstituicaoModel->expects($this->once())
            ->method('getInstituicaoByCNPJ')
            ->with($cnpj)
            ->willReturn([
                "id" => 1,
                "cnpj" => $cnpj,
                "senha" => self::HASHED_PASSWORD
            ]);

        $result = $this->instituicaoController->loginInstituicao($cnpj, 'senha_errada');
        $this->assertFalse($result);
        $this->assertArrayNotHasKey('id_instituicao', $_SESSION);
    }

    // =========================================================================
    // Testes para isLoggedIn()
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_true_when_instituicao_is_logged_in()
    {
        $_SESSION['id_instituicao'] = 1;
        $this->assertTrue($this->instituicaoController->isLoggedIn());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_when_instituicao_is_not_logged_in()
    {
        $this->assertFalse($this->instituicaoController->isLoggedIn());
    }

    // =========================================================================
    // Testes para updateInstituicao, updateInstituicaoDescricao, updateInstituicaoFoto
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_instituicao_info_successfully()
    {
        $args = [1, "Novo Nome", "novo@email.com", "12345678000190", "novo_link"];

        $this->mockInstituicaoModel->expects($this->once())
            ->method('updateInstituicao')
            ->with(...$args)
            ->willReturn(true);

        $result = $this->instituicaoController->updateInstituicao(...$args);
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_instituicao_descricao_successfully()
    {
        $this->mockInstituicaoModel->expects($this->once())
            ->method('updateInstituicaoDescricao')
            ->with(1, "Nova Descrição")
            ->willReturn(true);

        $result = $this->instituicaoController->updateInstituicaoDescricao(1, "Nova Descrição");
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_instituicao_foto_successfully()
    {
        $this->mockInstituicaoModel->expects($this->once())
            ->method('updateInstituicaoFoto')
            ->with(1, "nova_foto_binaria")
            ->willReturn(true);

        $result = $this->instituicaoController->updateInstituicaoFoto(1, "nova_foto_binaria");
        $this->assertTrue($result);
    }

    // =========================================================================
    // Testes para deleteInstituicao($id)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_instituicao_successfully()
    {
        $this->mockInstituicaoModel->expects($this->once())
            ->method('deleteInstituicao')
            ->with(1)
            ->willReturn(true);

        $result = $this->instituicaoController->deleteInstituicao(1);
        $this->assertTrue($result);
    }

    // =========================================================================
    // Testes para resetPasswordByEmail($email, $senha)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_resets_password_successfully()
    {
        $email = "reset@email.com";
        $newPassword = "nova_senha_123";

        $this->mockInstituicaoModel->expects($this->once())
            ->method('updatePasswordByEmail')
            ->with($email, $newPassword)
            ->willReturn(true);

        $result = $this->instituicaoController->resetPasswordByEmail($email, $newPassword);
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_reset_password_with_invalid_email()
    {
        $result = $this->instituicaoController->resetPasswordByEmail("email_invalido", "nova_senha_123");
        $this->assertFalse($result);
    }

    // =========================================================================
    // Testes para getters (Controller não tem lógica)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getInstituicaoByCNPJ_on_model()
    {
        $cnpj = "12345678000190";
        $expectedResult = ['cnpj' => $cnpj];

        $this->mockInstituicaoModel->expects($this->once())
            ->method('getInstituicaoByCNPJ')
            ->with($cnpj)
            ->willReturn($expectedResult);

        $result = $this->instituicaoController->getInstituicaoByCNPJ($cnpj);
        $this->assertEquals($expectedResult, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getAllInstituicoes_on_model()
    {
        $expectedResult = [['id' => 1], ['id' => 2]];

        $this->mockInstituicaoModel->expects($this->once())
            ->method('getAllInstituicoes')
            ->willReturn($expectedResult);

        $result = $this->instituicaoController->getAllInstituicoes();
        $this->assertEquals($expectedResult, $result);
    }
}
?>
