<?php

use PHPUnit\Framework\TestCase;
use Controller\ClienteController;

// Incluir o autoload para que as classes sejam encontradas
require_once __DIR__ . '/../vendor/autoload.php'; 

// O Controller original não aceita injeção de dependência no construtor.
// Para que os testes unitários sejam puros (sem tocar no banco de dados),
// é necessário que o Controller possa receber um Model mockado.
// A classe a seguir é uma versão modificada do Controller que permite a injeção
// do Model mockado para fins de teste.

class TestableClienteController extends ClienteController {
    public function __construct($clienteModel = null) {
        // Se um mock for passado, use-o. Caso contrário, use o Model real (comportamento original).
        if ($clienteModel) {
            // Usa Reflection para injetar o mock na propriedade privada/protegida
            $reflection = new \ReflectionClass($this);
            $property = $reflection->getProperty('clienteModel');
            $property->setAccessible(true);
            $property->setValue($this, $clienteModel);
        }
    }
    
    // Sobrescreve o construtor original que não aceita argumentos
    public function __construct_original() {
        // Não chama parent::__construct() para evitar inicialização do Model real
    }
}

class ClienteTest extends TestCase
{
    private TestableClienteController $clienteController;
    private $mockClienteModel;

    private const VALID_PASSWORD = 'senha_valida_123';
    private const HASHED_PASSWORD = '$2y$10$42Hn/S3.3j1c3x4y5z6a1O5r/T5c0p1r8L1m2o7F4P3k0J1s2i'; // Senha de exemplo hasheada

    protected function setUp(): void
    {
        // Mock do Model\Cliente para isolar o Controller
        $this->mockClienteModel = $this->createMock(\Model\Cliente::class);
        
        // Injetar o mock no Controller Testável
        $this->clienteController = new TestableClienteController($this->mockClienteModel);
        
        // Limpar a sessão antes de cada teste
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Limpar a sessão após cada teste
        $_SESSION = [];
    }

    // =========================================================================
    // Testes para cadastroCliente($nome, $email, $telefone, $senha)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_a_valid_client_successfully()
    {
        $nome = "Cliente Teste";
        $email = "cliente@teste.com";
        $telefone = "11987654321";

        $this->mockClienteModel
            ->expects($this->once())
            ->method('registerClient')
            ->with($nome, $email, $telefone, self::VALID_PASSWORD)
            ->willReturn(true);

        $result = $this->clienteController->cadastroCliente($nome, $email, $telefone, self::VALID_PASSWORD);
        $this->assertTrue($result, "Deve retornar true para um cadastro válido.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_client_with_missing_fields()
    {
        // Teste com nome faltando
        $result = $this->clienteController->cadastroCliente("", "email@teste.com", "11987654321", self::VALID_PASSWORD);
        $this->assertFalse($result, "Deve retornar false se o nome estiver vazio.");
        
        // Teste com email faltando
        $result = $this->clienteController->cadastroCliente("Nome", "", "11987654321", self::VALID_PASSWORD);
        $this->assertFalse($result, "Deve retornar false se o email estiver vazio.");
        
        // Teste com senha faltando
        $result = $this->clienteController->cadastroCliente("Nome", "email@teste.com", "11987654321", "");
        $this->assertFalse($result, "Deve retornar false se a senha estiver vazia.");
        
        // Teste com telefone faltando
        $result = $this->clienteController->cadastroCliente("Nome", "email@teste.com", "", self::VALID_PASSWORD);
        $this->assertFalse($result, "Deve retornar false se o telefone estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_register_client_when_model_returns_false()
    {
        $this->mockClienteModel
            ->expects($this->once())
            ->method('registerClient')
            ->willReturn(false); // Simula falha no banco de dados (ex: email duplicado)

        $result = $this->clienteController->cadastroCliente("Duplicado", "email@teste.com", "11987654321", self::VALID_PASSWORD);
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao registrar.");
    }

    // =========================================================================
    // Testes para checkClienteByEmail($email)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_client_by_existing_email()
    {
        $existingEmail = 'existente@email.com';
        $userData = ['id' => 1, 'email' => $existingEmail, 'nome' => 'Usuário Existente'];

        $this->mockClienteModel->expects($this->once())
            ->method('getClientByEmail')
            ->with($existingEmail)
            ->willReturn($userData);

        $result = $this->clienteController->checkClienteByEmail($existingEmail);
        $this->assertIsArray($result, "Deve retornar um array para um email existente.");
        $this->assertEquals($existingEmail, $result['email']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_client_by_non_existing_email()
    {
        $nonExistingEmail = 'naoexiste@email.com';

        $this->mockClienteModel->expects($this->once())
            ->method('getClientByEmail')
            ->with($nonExistingEmail)
            ->willReturn(null);

        $result = $this->clienteController->checkClienteByEmail($nonExistingEmail);
        $this->assertNull($result, "Deve retornar null para um email não existente.");
    }

    // =========================================================================
    // Testes para loginCliente($email, $senha)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_in_client_with_correct_credentials()
    {
        $email = 'login_ok@email.com';
        $password = self::VALID_PASSWORD;
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $cliente = [
            "id" => 10,
            "nome" => "Cliente Logado",
            "email" => $email,
            "telefone" => "11999998888",
            "senha" => $hashed
        ];

        $this->mockClienteModel->expects($this->once())
            ->method('getClientByEmail')
            ->with($email)
            ->willReturn($cliente);

        // Simular a sessão ativa, pois o Controller a inicia
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Não podemos iniciar a sessão em ambiente CLI, mas podemos simular o comportamento
            // do Controller que define as variáveis de sessão.
            // O teste deve garantir que as variáveis de sessão são definidas.
        }

        $result = $this->clienteController->loginCliente($email, $password);

        $this->assertTrue($result, "Deve retornar true para login bem-sucedido.");
        $this->assertEquals(10, $_SESSION['id'], "A SESSION['id'] deve ser definida corretamente.");
        $this->assertEquals($email, $_SESSION['email'], "A SESSION['email'] deve ser definida corretamente.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_login_with_incorrect_password()
    {
        $email = 'teste@email.com';
        $this->mockClienteModel->expects($this->once())
            ->method('getClientByEmail')
            ->with($email)
            ->willReturn([
                "id" => 1,
                "nome" => "Teste",
                "email" => $email,
                "telefone" => "11987654321",
                "senha" => self::HASHED_PASSWORD
            ]);

        $result = $this->clienteController->loginCliente($email, 'senha_errada');
        $this->assertFalse($result, "Deve retornar false para senha incorreta.");
        $this->assertArrayNotHasKey('id', $_SESSION, "A SESSION['id'] não deve ser definida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_login_with_non_existing_email()
    {
        $email = 'naoexiste@email.com';
        $this->mockClienteModel->expects($this->once())
            ->method('getClientByEmail')
            ->with($email)
            ->willReturn(null);

        $result = $this->clienteController->loginCliente($email, self::VALID_PASSWORD);
        $this->assertFalse($result, "Deve retornar false para email não existente.");
        $this->assertArrayNotHasKey('id', $_SESSION, "A SESSION['id'] não deve ser definida.");
    }

    // =========================================================================
    // Testes para isLoggedIn()
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_true_when_client_is_logged_in()
    {
        $_SESSION['id'] = 1;
        $this->assertTrue($this->clienteController->isLoggedIn(), "Deve retornar true se SESSION['id'] estiver definida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_when_client_is_not_logged_in()
    {
        $this->assertFalse($this->clienteController->isLoggedIn(), "Deve retornar false se SESSION['id'] não estiver definida.");
    }

    // =========================================================================
    // Testes para updateClient($id, $nome, $email, $telefone)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_client_info_successfully()
    {
        $id = 1;
        $nome = "Novo Nome";
        $email = "novo@email.com";
        $telefone = "11900001111";

        $this->mockClienteModel->expects($this->once())
            ->method('updateClient')
            ->with($id, $nome, $email, $telefone)
            ->willReturn(true);

        $result = $this->clienteController->updateClient($id, $nome, $email, $telefone);
        $this->assertTrue($result, "Deve retornar true para atualização bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_client_with_missing_fields()
    {
        // Teste com ID faltando
        $result = $this->clienteController->updateClient(null, "Nome", "email@teste.com", "11987654321");
        $this->assertFalse($result, "Deve retornar false se o ID estiver vazio.");
        
        // Teste com nome faltando
        $result = $this->clienteController->updateClient(1, "", "email@teste.com", "11987654321");
        $this->assertFalse($result, "Deve retornar false se o nome estiver vazio.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_update_client_when_model_returns_false()
    {
        $id = 1;
        $nome = "Nome";
        $email = "email@teste.com";
        $telefone = "11987654321";

        $this->mockClienteModel->expects($this->once())
            ->method('updateClient')
            ->willReturn(false); // Simula falha no banco de dados

        $result = $this->clienteController->updateClient($id, $nome, $email, $telefone);
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao atualizar.");
    }

    // =========================================================================
    // Testes para deleteCliente($id)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_client_successfully()
    {
        $id = 1;

        $this->mockClienteModel->expects($this->once())
            ->method('deleteCliente')
            ->with($id)
            ->willReturn(true);

        $result = $this->clienteController->deleteCliente($id);
        $this->assertTrue($result, "Deve retornar true para exclusão bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_delete_client_with_missing_id()
    {
        $result = $this->clienteController->deleteCliente(null);
        $this->assertFalse($result, "Deve retornar false se o ID estiver vazio.");
    }

    // =========================================================================
    // Testes para resetPasswordByEmail($email, $senha)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_resets_password_successfully()
    {
        $email = "reset@email.com";
        $newPassword = "nova_senha_123";

        $this->mockClienteModel->expects($this->once())
            ->method('updatePasswordByEmail')
            ->with($email, $newPassword)
            ->willReturn(true);

        $result = $this->clienteController->resetPasswordByEmail($email, $newPassword);
        $this->assertTrue($result, "Deve retornar true para redefinição de senha bem-sucedida.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_reset_password_with_invalid_email()
    {
        $result = $this->clienteController->resetPasswordByEmail("email_invalido", "nova_senha_123");
        $this->assertFalse($result, "Deve retornar false para email inválido.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_reset_password_with_short_password()
    {
        $result = $this->clienteController->resetPasswordByEmail("email@valido.com", "curta");
        $this->assertFalse($result, "Deve retornar false para senha com menos de 6 caracteres.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_reset_password_when_model_returns_false()
    {
        $email = "reset@email.com";
        $newPassword = "nova_senha_123";

        $this->mockClienteModel->expects($this->once())
            ->method('updatePasswordByEmail')
            ->willReturn(false);

        $result = $this->clienteController->resetPasswordByEmail($email, $newPassword);
        $this->assertFalse($result, "Deve retornar false se o Model falhar ao atualizar a senha.");
    }
    
    // =========================================================================
    // Testes para getClienteName e getClienteInfo (Controller não tem lógica)
    // =========================================================================
    
    // As funções getClienteName e getClienteInfo no Controller apenas repassam a chamada para o Model.
    // O teste unitário do Controller deve garantir que a chamada ao Model é feita corretamente.
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getClienteName_on_model()
    {
        $id = 1;
        $nome = "Nome";
        $expectedResult = ['nome' => $nome];

        $this->mockClienteModel->expects($this->once())
            ->method('getClienteName')
            ->with($id, $nome)
            ->willReturn($expectedResult);

        $result = $this->clienteController->getClienteName($id, $nome);
        $this->assertEquals($expectedResult, $result, "Deve retornar o resultado do Model.");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calls_getClienteInfo_on_model()
    {
        $id = 1;
        $nome = "Nome";
        $email = "email@teste.com";
        $telefone = "11987654321";
        $senha = "hash_senha";
        $expectedResult = ['nome' => $nome, 'email' => $email];

        $this->mockClienteModel->expects($this->once())
            ->method('getClienteInfo')
            ->with($id, $nome, $email, $telefone, $senha)
            ->willReturn($expectedResult);

        $result = $this->clienteController->getClienteInfo($id, $nome, $email, $telefone, $senha);
        $this->assertEquals($expectedResult, $result, "Deve retornar o resultado do Model.");
    }
}
?>
