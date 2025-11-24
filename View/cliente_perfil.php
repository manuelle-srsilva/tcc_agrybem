<?php
// Inicia a sessão para garantir que temos acesso às variáveis $_SESSION
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 1. VERIFICAÇÃO DE LOGIN
if (!isset($_SESSION['id'])) {
    header('Location: /cliente_login.php'); 
    exit();
}

// 2. INCLUSÃO DE DEPENDÊNCIAS E PROCESSAMENTO DO FORMULÁRIO
require_once __DIR__ . '/../vendor/autoload.php'; 
use Controller\ClienteController;

$clienteController = new ClienteController();

// Verifica se a requisição é do tipo POST para processar a atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);

    if ($id == $_SESSION['id']) {
        if ($clienteController->updateClient($id, $nome, $email, $telefone)) {
            $_SESSION['nome'] = $nome;
            $_SESSION['email'] = $email;
            $_SESSION['telefone'] = $telefone;
            $_SESSION['mensagem_perfil'] = "Perfil atualizado com sucesso!";
        } else {
            $_SESSION['mensagem_perfil'] = "Erro ao atualizar o perfil.";
        }
    } else {
        $_SESSION['mensagem_perfil'] = "Erro de permissão.";
    }

    header('Location: cliente_perfil.php');
    exit();
}

// 3. BUSCA DOS DADOS PARA EXIBIÇÃO
$cliente_id = $_SESSION['id'];
$cliente_nome = $_SESSION['nome'];
$cliente_email = $_SESSION['email'];
$cliente_telefone = $_SESSION['telefone'];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Agrybem</title>
    <link rel="stylesheet" href="../templates/assets/css/cliente_perfil1.css">
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
                <a href="cliente_pag_principal.php" class="nav-button">Voltar</a>
            </nav>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <section class="header-section">
            <div class="container">
                <div class="header-top">
                    <div class="header-info">
                        <h1 class="institution-title"><?= htmlspecialchars($cliente_nome) ?></h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="card-section">
            <div class="container">
                <!-- O formulário engloba o card e tem um ID para ser facilmente selecionado pelo JS -->
                <form id="perfil-form" action="cliente_perfil.php" method="POST">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($cliente_id) ?>">

                    <div class="card" data-card="institution">
                        <div class="card-header">
                            <h2 class="card-title">Informações Pessoais</h2>
                            <button type="button" class="card-edit-button" title="Editar">✎</button>
                        </div>
                        
                        <div class="card-actions">
                            <!-- Botão de salvar continua sendo 'button' para o JS controlar, mas ele vai submeter o form -->
                            <button type="button" class="card-save-button" title="Salvar">✓</button>
                            <button type="button" class="card-cancel-button" title="Cancelar">✕</button>
                        </div>
                        
                        <div class="form-field">
                            <label for="nome">Nome</label>
                            <!-- O 'id' foi alterado para 'nome' para corresponder ao 'for' da label -->
                            <input type="text" id="nome" value="<?= htmlspecialchars($cliente_nome) ?>" name="nome" disabled required>
                        </div>

                        <div class="form-field">
                            <label for="email">E-mail</label>
                            <!-- O 'id' foi alterado para 'email' para corresponder ao 'for' da label -->
                            <input type="email" id="email" value="<?= htmlspecialchars($cliente_email) ?>" name="email" disabled required>
                        </div>

                        <div class="form-field">
                            <label for="telefone">Telefone</label>
                            <!-- O 'id' foi alterado para 'telefone' para corresponder ao 'for' da label -->
                            <input type="text" id="telefone" value="<?= htmlspecialchars($cliente_telefone) ?>" name="telefone" disabled required>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>

    
    <!-- SCRIPT CORRIGIDO E INTEGRADO -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const card = document.querySelector('.card[data-card="institution"]');
        if (!card) return; // Se não encontrar o card, para a execução

        const editButton = card.querySelector('.card-edit-button');
        const saveButton = card.querySelector('.card-save-button');
        const cancelButton = card.querySelector('.card-cancel-button');
        const inputs = card.querySelectorAll('input[name="nome"], input[name="email"], input[name="telefone"]');
        const form = document.getElementById('perfil-form');

        // Armazenar valores originais para a função de cancelar
        const originalValues = {};
        inputs.forEach(input => {
            originalValues[input.id] = input.value;
        });

        // 1. CLIQUE NO BOTÃO EDITAR (✎)
        editButton.addEventListener('click', function() {
            card.classList.add('editing');
            inputs.forEach(input => {
                input.disabled = false;
            });
            inputs[0].focus();
        });

        // 2. CLIQUE NO BOTÃO SALVAR (✓) - A MUDANÇA CRÍTICA ESTÁ AQUI
        saveButton.addEventListener('click', function() {
            // Em vez de 'preventDefault', nós explicitamente enviamos o formulário!
            form.submit();
        });

        // 3. CLIQUE NO BOTÃO CANCELAR (✕)
        cancelButton.addEventListener('click', function() {
            // Restaura os valores originais dos campos
            inputs.forEach(input => {
                input.value = originalValues[input.id];
            });
            
            // Sai do modo de edição
            card.classList.remove('editing');
            inputs.forEach(input => {
                input.disabled = true;
            });
        });
    });
    </script>

     <!-- Api Vlibras -->
    <div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>
</body>
</html>