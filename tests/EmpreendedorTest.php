<?php

use PHPUnit\Framework\TestCase;
use Controller\EmpreendedorController;

// Incluir o autoload para que as classes sejam encontradas
require_once __DIR__ . '/../vendor/autoload.php'; 

// Classe auxiliar para permitir a injeção de dependência do Model mockado
class TestableEmpreendedorController extends EmpreendedorController {
    // A propriedade $empreendedorModel é privada no Controller original,
    // o que impede a injeção direta. Para fins de teste, assumimos que
    // o Controller foi refatorado para permitir a injeção no construtor.
    protected $empreendedorModel;

    public function __construct($empreendedorModel = null) {
        // Se um mock for passado, use-o. Caso contrário, use o Model real (comportamento original).
        if ($empreendedorModel) {
            // Usa Reflection para injetar o mock na propriedade privada/protegida
            $reflection = new \ReflectionClass($this);
            $property = $reflection->getProperty('empreendedorModel');
            $property->setAccessible(true);
            $property->setValue($this, $empreendedorModel);
        }
    }
}

class EmpreendedorTest extends TestCase
{
    private TestableEmpreendedorController $empreendedorController;
    private $mockEmpreendedorModel;

    private const VALID_PASSWORD = 'senha_valida_123';
    private const HASHED_PASSWORD = '$2y$10$42Hn/S3.3j1c3x4y5z6a1O5r/T5c0p1r8L1m2o7F4P3k0J1s2i'; // Senha de exemplo hasheada

    protected function setUp(): void
    {
        // Mock do Model\Empreendedor para isolar o Controller
        $this->mockEmpreendedorModel = $this->createMock(\Model\Empreendedor::class);
        
        // Injetar o mock no Controller Testável
        $this->empreendedorController = new TestableEmpreendedorController($this->mockEmpreendedorModel);
        
        // Limpar a sessão antes de cada teste
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Limpar a sessão após cada teste
        $_SESSION = [];
    }

    // =========================================================================
    // Testes para cadastroEmpreendedor($nome, $email, $telefone, $senha)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_a_valid_empreendedor_successfully($nome, $email, $senha, $cnpj_cpf)
    {
        $nome = "Empreendedor Teste";
        $email = "empreendedor@teste.com";
        $senha = "senha";
        $cnpj_cpf = "11987654321";

        $this->mockEmpreendedorModel
            ->expects($this->once())
            ->method('registerEmpreendedor')
            ->with($nome, $email, $senha, $cnpj_cpf)
            ->willReturn(true);

        $result = $this->empreendedorController->salvarEmpreendedor($nome, $email, $senha, $cnpj_cpf);
        $this->assertTrue($result, "Deve retornar true para um cadastro válido.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_empreendedor_with_missing_fields($nome, $email, $senha, $cnpj_cpf)
    {
        // Teste com nome faltando
        $result = $this->empreendedorController->salvarEmpreendedor("", "email@teste.com", "senha", "11987654321");
        $this->assertFalse($result, "Deve retornar false se o nome estiver vazio.");
        
        // Teste com email faltando
        $result = $this->empreendedorController->salvarEmpreendedor("Nome", "", "senha", "11987654321");
        $this->assertFalse($result, "Deve retornar false se o email estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_empreendedor_when_model_returns_false($nome, $email, $senha, $cnpj_cpf)
    {
        $this->mockEmpreendedorModel
            ->expects($this->once())
            ->method('registerEmpreendedor')
            ->willReturn(false); // Simula falha no banco de dados (ex: email duplicado)

        $result = $this->empreendedorController->salvarEmpreendedor($nome, $email, $senha, $cnpj_cpf);
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao registrar.");
    }

    // =========================================================================
    // Testes para checkEmpreendedorByEmail($email)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_empreendedor_by_existing_cnpj_cpf($cnpj_cpf)
    {
        $existingCNPJ_CPF = '12345678900';
        $userData = ['id' => 1, 'email' => $existingCNPJ_CPF, 'nome' => 'Empreendedor Existente'];

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('getEmpreendedorByCNPJ_CPF')
            ->with($existingCNPJ_CPF)
            ->willReturn($userData);

        $result = $this->empreendedorController->getEmpreendedorByCNPJ_CPF($cnpj_cpf);
        $this->assertIsArray($result, "Deve retornar um array para um CNPJ ou CPF existente.");
        $this->assertEquals($existingCNPJ_CPF, $result['email']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_empreendedor_by_non_existing_cnpj_cpf($cnpj_cpf)
    {
        $nonExistingCNPJ_CPF = '00000000000';

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('getEmpreendedorByCNPJ_CPF')
            ->with($nonExistingCNPJ_CPF)
            ->willReturn(null);

        $result = $this->empreendedorController->getEmpreendedorByCNPJ_CPF($cnpj_cpf);
        $this->assertNull($result, "Deve retornar null para um CNPJ ou CPF não existente.");
    }

    // =========================================================================
    // Testes para loginEmpreendedor($email, $senha)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_in_empreendedor_with_correct_credentials($cnpj_cpf, $password)
    {
        $cnpj_cpf = 'login_ok@email.com';
        $password = self::VALID_PASSWORD;
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $empreendedor = [
            "id" => 10,
            "nome" => "Empreendedor Logado",
            "email" => $cnpj_cpf,
            "telefone" => "11999998888",
            "senha" => $hashed
        ];

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('getEmpreendedorByCNPJ_CPF')
            ->with($cnpj_cpf)
            ->willReturn($empreendedor);

        $result = $this->empreendedorController->loginEmpreendedor($cnpj_cpf, $password);
        $this->assertTrue($result, "Deve retornar true para login bem-sucedido.");
        $this->assertEquals(10, $_SESSION['id'], "A SESSION['id'] deve ser definida corretamente.");
        $this->assertEquals($cnpj_cpf, $_SESSION['email'], "A SESSION['email'] deve ser definida corretamente.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_login_with_incorrect_password($cnpj_cpf, $password)
    {
        $cnpj_cpf = '12345678900';
        $this->mockEmpreendedorModel->expects($this->once())
            ->method('getEmpreendedorByCNPJ_CPF')
            ->with($cnpj_cpf)
            ->willReturn([
                "id" => 1,
                "nome" => "Teste",
                "email" => $cnpj_cpf,
                "telefone" => "11987654321",
                "senha" => self::HASHED_PASSWORD
            ]);

        $result = $this->empreendedorController->loginEmpreendedor($cnpj_cpf, $senha = 'senha_incorreta');
        $this->assertFalse($result, "Deve retornar false para senha incorreta.");
        $this->assertArrayNotHasKey('id', $_SESSION, "A SESSION['id'] não deve ser definida.");
    }

    // =========================================================================
    // Testes para isLoggedIn()
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_true_when_empreendedor_is_logged_in($id)
    {
        $_SESSION['id_empreendedor'] = 1;
        $this->assertTrue($this->empreendedorController->isLoggedIn(), "Deve retornar true se SESSION['id_empreendedor'] estiver definida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_when_empreendedor_is_not_logged_in($id)
    {
        $this->assertFalse($this->empreendedorController->isLoggedIn(), "Deve retornar false se SESSION['id'] não estiver definida.");
    }

    // =========================================================================
    // Testes para updateEmpreendedor($id, $nome, $email, $telefone)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_empreendedor_info_successfully($id, $nome, $email, $cnpj_cpf)
    {
        $id = 1;
        $nome = "Novo Nome";
        $email = "novo@email.com";
        $cnpj_cpf = "11900001111";

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('updateEmpreendedor')
            ->with($id, $nome, $email, $cnpj_cpf)
            ->willReturn(true);

        $result = $this->empreendedorController->updateEmpreendedor($id, $nome, $email, $cnpj_cpf);
        $this->assertTrue($result, "Deve retornar true para atualização bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_empreendedor_with_missing_fields($id, $nome, $email, $cnpj_cpf)
    {
        // Teste com ID faltando
        $result = $this->empreendedorController->updateEmpreendedor(null, "Nome", "email@teste.com", "11987654321");
        $this->assertFalse($result, "Deve retornar false se o ID estiver vazio.");
        
        // Teste com nome faltando
        $result = $this->empreendedorController->updateEmpreendedor(1, "", "email@teste.com", "11987654321");
        $this->assertFalse($result, "Deve retornar false se o nome estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_empreendedor_when_model_returns_false($id, $nome, $email, $cnpj_cpf)
    {
        $id = 1;
        $nome = "Nome";
        $email = "email@teste.com";
        $cnpj_cpf = "11987654321";

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('updateEmpreendedor')
            ->willReturn(false); // Simula falha no banco de dados

        $result = $this->empreendedorController->updateEmpreendedor($id, $nome, $email, $cnpj_cpf);
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao atualizar.");
    }

    // =========================================================================
    // Testes para deleteEmpreendedor($id)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_empreendedor_successfully($id)
    {
        $id = 1;

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('deleteEmpreendedor')
            ->with($id)
            ->willReturn(true);

        $result = $this->empreendedorController->deleteEmpreendedor($id);
        $this->assertTrue($result, "Deve retornar true para exclusão bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_delete_empreendedor_with_missing_id($id)
    {
        $result = $this->empreendedorController->deleteEmpreendedor(null);
        $this->assertFalse($result, "Deve retornar false se o ID estiver vazio.");
    }

    // =========================================================================
    // Testes para resetPasswordByEmail($email, $senha)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_resets_password_successfully($email, $senha)
    {
        $email = "reset@email.com";
        $newPassword = "nova_senha_123";

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('updatePasswordByEmail')
            ->with($email, $newPassword)
            ->willReturn(true);

        $result = $this->empreendedorController->resetPasswordByEmail($email, $newPassword);
        $this->assertTrue($result, "Deve retornar true para redefinição de senha bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_reset_password_with_invalid_email($email, $senha)
    {
        $result = $this->empreendedorController->resetPasswordByEmail("email_invalido", "nova_senha_123");
        $this->assertFalse($result, "Deve retornar false para email inválido.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_reset_password_with_short_password($email, $senha)
    {
        $result = $this->empreendedorController->resetPasswordByEmail("email@valido.com", "curta");
        $this->assertFalse($result, "Deve retornar false para senha com menos de 6 caracteres.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_reset_password_when_model_returns_false($email, $senha)
    {
        $email = "reset@email.com";
        $newPassword = "nova_senha_123";

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('updatePasswordByEmail')
            ->willReturn(false);

        $result = $this->empreendedorController->resetPasswordByEmail($email, $newPassword);
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao atualizar a senha.");
    }
    
    // =========================================================================
    // Testes para getEmpreendedorName e getEmpreendedorInfo (Controller não tem lógica)
    // =========================================================================
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getEmpreendedorName_on_model($id, $nome)
    {
        $id = 1;
        $nome = "Nome";
        $expectedResult = ['nome' => $nome];

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('getEmpreendedorName')
            ->with($id, $nome)
            ->willReturn($expectedResult);

        $result = $this->empreendedorController->getEmpreendedorName($id, $nome);
        $this->assertEquals($expectedResult, $result, "Deve retornar o resultado do Model.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getEmpreendedorInfo_on_model($id, $nome, $email, $senha, $cnpj_cpf)
    {
        $id = 1;
        $nome = "Nome";
        $email = "email@teste.com";
        $senha = "senha";
        $cnpj_cpf = "12345678900";
        $expectedResult = ['nome' => $nome, 'email' => $email];

        $this->mockEmpreendedorModel->expects($this->once())
            ->method('getEmpreendedorInfo')
            ->with($id, $nome, $email, $cnpj_cpf, $senha)
            ->willReturn($expectedResult);

        $result = $this->empreendedorController->getEmpreendedorInfo($id);
        $this->assertEquals($expectedResult, $result, "Deve retornar o resultado do Model.");
    }
}
?>
