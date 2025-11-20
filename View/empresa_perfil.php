<?php
// Inicia a sessão
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 1. CARREGAMENTO DO AMBIENTE E VERIFICAÇÃO DE LOGIN
// Inclui o autoloader do Composer, que cuidará de carregar todas as classes.
require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['id_empreendedor'])) {
    header('Location: login_empresa.php');
    exit();
}

// 2. USO DOS CONTROLLERS (sem require_once manuais)
use Controller\EmpreendedorController;
use Controller\EmpreendimentoController;
use Controller\EnderecoController;

// Instancia os controllers
$empreendedorController = new EmpreendedorController();
$empreendimentoController = new EmpreendimentoController();
$enderecoController = new EnderecoController();

// 3. PROCESSAMENTO DAS ATUALIZAÇÕES (LÓGICA OTIMIZADA)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // AÇÃO PARA ATUALIZAR A FOTO DE PERFIL
    if (isset($_FILES['nova_foto']) && $_FILES['nova_foto']['error'] === UPLOAD_ERR_OK) {
        $fotoBin = file_get_contents($_FILES['nova_foto']['tmp_name']);
        $empreendimentoController->updateEmpreendimentoFoto(
            $_SESSION['id_empreendimento'],
            $fotoBin
        );
        // Redireciona para limpar o POST e mostrar a nova foto
        header('Location: empresa_perfil.php');
        exit();
    }

    $formType = $_POST['form_type'] ?? '';

    // Ação para o card "Informações da Empresa"
    if ($formType === 'empresa') {
        $empreendedorController->updateEmpreendedor(
            $_SESSION['id_empreendedor'],
            $_POST['nome_empreendedor'],
            $_POST['email'],
            $_POST['cnpj_cpf']
        );
        // A função de update do empreendimento precisa de todos os campos.
        // O campo 'descricao' não está neste formulário, então o passamos de um campo oculto.
        $empreendimentoController->updateEmpreendimento(
            $_SESSION['id_empreendimento'],
            $_POST['nome_empreendimento'],
            $_POST['telefone'],
            $_POST['link_whatsapp'],
            $_POST['descricao_hidden'], // <-- Usando o campo oculto
            $_POST['hr_funcionamento']
        );
    }

    // Ação para o card "Descrição" (AGORA OTIMIZADO)
    if ($formType === 'description') {
        // A lógica agora é idêntica à do outro card, usando campos ocultos.
        // É mais simples, mais rápido e mais consistente.
        $empreendimentoController->updateEmpreendimento(
            $_SESSION['id_empreendimento'],
            $_POST['nome_empreendimento_hidden'],
            $_POST['telefone_hidden'],
            $_POST['link_whatsapp_hidden'],
            $_POST['descricao'], // O único campo visível e editado
            $_POST['hr_funcionamento_hidden']
        );
    }

    // Ação para o card "Endereço"
    if ($formType === 'address') {
        $enderecoController->updateEndereco(
            $_SESSION['id_endereco'],
            $_POST['cep'], $_POST['rua'], $_POST['numero'], $_POST['bairro'],
            $_POST['cidade'], $_POST['estado'], $_POST['complemento']
        );
    }

    // Redireciona para a mesma página para mostrar os dados atualizados
    header('Location: empresa_perfil.php');
    exit();
}

// 4. BUSCA DOS DADOS PARA EXIBIÇÃO
$empreendedorInfo = $empreendedorController->getEmpreendedorInfo($_SESSION['id_empreendedor']);
$empreendimentoInfo = $empreendimentoController->getempreendimentoInfo($_SESSION['id_empreendimento']);
$empreendimentoFoto = $empreendimentoController->getEmpreendimentoFoto($_SESSION['id_empreendimento']);
$enderecoInfo = $enderecoController->getEnderecoInfo($_SESSION['id_endereco']);

$foto = ($empreendimentoFoto && !empty($empreendimentoFoto['foto'])) 
    ? 'data:image/jpeg;base64,' . base64_encode($empreendimentoFoto['foto']) 
    : '../templates/assets/img/cesta rural.png';

// 5. PREPARAÇÃO DA URL DO MAPA
$mapQuery = 'Brasil'; // Valor padrão caso não haja endereço
if ($enderecoInfo) {
    $mapParts = [
        $enderecoInfo['rua'] ?? '',
        $enderecoInfo['numero'] ?? '',
        $enderecoInfo['bairro'] ?? '',
        $enderecoInfo['cidade'] ?? '',
        $enderecoInfo['estado'] ?? '',
        $enderecoInfo['cep'] ?? ''
    ];
    // Remove partes vazias e junta com vírgula
    $addressString = implode(',', array_filter($mapParts));
    // Codifica a string para ser usada em uma URL (troca espaços por '+', etc.)
    $mapQuery = urlencode($addressString);
}
$googleMapsApiKey = 'AIzaSyC0tzkSQhRrMOcoMZ1XU0Ty4RwCER5gxLo'; // IMPORTANTE: Substitua pela sua chave de API do Google Maps
$mapUrl = "https://www.google.com/maps/embed/v1/place?key={$googleMapsApiKey}&q={$mapQuery}";

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresa Perfil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/empresa_perfil.css">
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
                <a href="painel_profissional_empresa.php" class="nav-button">Voltar</a>
            </nav>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- HEADER SECTION -->
        <section class="header-section">
            <div class="container">
                <div class="header-top">
                    <form id="form-foto" action="empresa_perfil.php" method="POST" enctype="multipart/form-data">
                        <div class="logo-institution">
                            <button type="button" class="photo-edit-button" title="Trocar Foto">✎</button>
                            <img src="<?= $foto ?>" alt="Logo da Empresa" class="logo-img">
                            <input type="file" name="nova_foto" id="input-nova-foto" style="display: none;" accept="image/*">
                        </div>
                    </form>
                    <div class="header-info">
                        <h1 class="institution-title"><?= htmlspecialchars($empreendimentoInfo['nome'] ?? 'Nome da Empresa') ?></h1>
                    </div>
                </div>
            </div>
        </section>

        <!-- DESCRIPTION CARD -->
        <section class="card-section">
            <div class="container">
                <form class="profile-form" action="empresa_perfil.php" method="POST">
                    <input type="hidden" name="form_type" value="description">
                    <!-- Campos ocultos para reenviar dados que não estão neste formulário -->
                    <input type="hidden" name="nome_empreendimento_hidden" value="<?= htmlspecialchars($empreendimentoInfo['nome'] ?? '') ?>">
                    <input type="hidden" name="telefone_hidden" value="<?= htmlspecialchars($empreendimentoInfo['telefone'] ?? '') ?>">
                    <input type="hidden" name="link_whatsapp_hidden" value="<?= htmlspecialchars($empreendimentoInfo['link_whatsapp'] ?? '') ?>">
                    <input type="hidden" name="hr_funcionamento_hidden" value="<?= htmlspecialchars($empreendimentoInfo['hr_funcionamento'] ?? '') ?>">

                    <div class="card" data-card="description">
                        <div class="card-header">
                            <h2 class="card-title">Descrição</h2>
                            <button type="button" class="card-edit-button" title="Editar">✎</button>
                        </div>
                        <div class="card-actions">
                            <button type="submit" class="card-save-button" title="Salvar">✓</button>
                            <button type="button" class="card-cancel-button" title="Cancelar">✕</button>
                        </div>
                        <div class="form-field">
                            <label for="company-description">Descrição da Empresa</label>
                            <textarea id="company-description" name="descricao" disabled><?= htmlspecialchars($empreendimentoInfo['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- INSTITUTION INFO CARD -->
        <section class="card-section">
            <div class="container">
                <form class="profile-form" action="empresa_perfil.php" method="POST">
                    <input type="hidden" name="form_type" value="empresa">
                    <input type="hidden" name="descricao_hidden" value="<?= htmlspecialchars($empreendimentoInfo['descricao'] ?? '') ?>">
                    <div class="card" data-card="empresa">
                        <div class="card-header">
                            <h2 class="card-title">Informações da Empresa</h2>
                            <button type="button" class="card-edit-button" title="Editar">✎</button>
                        </div>
                        <div class="card-actions">
                            <button type="submit" class="card-save-button" title="Salvar">✓</button>
                            <button type="button" class="card-cancel-button" title="Cancelar">✕</button>
                        </div>
                        <div class="institution-form">
                            <div class="form-field"><label for="responsible">Nome do Responsável</label><input type="text" id="responsible" name="nome_empreendedor" value="<?= htmlspecialchars($empreendedorInfo['nome'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="company-name">Nome da Empresa</label><input type="text" id="company-name" name="nome_empreendimento" value="<?= htmlspecialchars($empreendimentoInfo['nome'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="email">Email Comercial</label><input type="email" id="email" name="email" value="<?= htmlspecialchars($empreendedorInfo['email'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="cpf">CPF ou CNPJ</label><input type="text" id="cpf" name="cnpj_cpf" value="<?= htmlspecialchars($empreendedorInfo['cnpj_cpf'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="phone">Telefone</label><input type="tel" id="phone" name="telefone" value="<?= htmlspecialchars($empreendimentoInfo['telefone'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="hours">Horário de Funcionamento</label><input type="text" id="hours" name="hr_funcionamento" value="<?= htmlspecialchars($empreendimentoInfo['hr_funcionamento'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="whatsapp">Link para WhatsApp</label><input type="text" id="whatsapp" name="link_whatsapp" value="<?= htmlspecialchars($empreendimentoInfo['link_whatsapp'] ?? '') ?>" disabled></div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- ADDRESS CARD -->
        <section class="card-section">
            <div class="container">
                <form class="profile-form" action="empresa_perfil.php" method="POST">
                    <input type="hidden" name="form_type" value="address">
                    <div class="card" data-card="address">
                        <div class="card-header">
                            <h2 class="card-title">Endereço</h2>
                            <button type="button" class="card-edit-button" title="Editar">✎</button>
                        </div>
                        <div class="card-actions">
                            <button type="submit" class="card-save-button" title="Salvar">✓</button>
                            <button type="button" class="card-cancel-button" title="Cancelar">✕</button>
                        </div>
                        <div class="address-form">
                            <div class="form-field"><label for="cep">CEP</label><input type="text" id="cep" name="cep" value="<?= htmlspecialchars($enderecoInfo['cep'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="street">Rua</label><input type="text" id="street" name="rua" value="<?= htmlspecialchars($enderecoInfo['rua'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="number">Número</label><input type="text" id="number" name="numero" value="<?= htmlspecialchars($enderecoInfo['numero'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="neighborhood">Bairro</label><input type="text" id="neighborhood" name="bairro" value="<?= htmlspecialchars($enderecoInfo['bairro'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="city">Cidade</label><input type="text" id="city" name="cidade" value="<?= htmlspecialchars($enderecoInfo['cidade'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="state">Estado</label><input type="text" id="state" name="estado" value="<?= htmlspecialchars($enderecoInfo['estado'] ?? '') ?>" disabled></div>
                            <div class="form-field"><label for="reference">Ponto de Referência</label><input type="text" id="reference" name="complemento" value="<?= htmlspecialchars($enderecoInfo['complemento'] ?? '') ?>" disabled></div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
        
        <!-- MAP SECTION -->
        <section class="map-section">
            <div class="container">
                <div class="map-placeholder">
                    <iframe
                        id="google-map-iframe"
                        width="600"
                        height="450"
                        style="border:0"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        src="<?= htmlspecialchars($mapUrl) ?>">
                    </iframe>
                </div>
            </div>
        </section>
    </main>
    
    <!-- SCRIPT AJUSTADO PARA FUNCIONAR COM MÚLTIPLOS FORMULÁRIOS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Itera sobre cada formulário de perfil na página
        document.querySelectorAll('.profile-form').forEach(form => {
            const card = form.querySelector('.card');
            if (!card) return;

            const editButton = card.querySelector('.card-edit-button');
            const saveButton = card.querySelector('.card-save-button');
            const cancelButton = card.querySelector('.card-cancel-button');
            const inputs = card.querySelectorAll('input, textarea');

            // Armazena os valores originais para a função de cancelar
            const originalValues = {};
            inputs.forEach(input => {
                originalValues[input.id] = input.value;
            });

            if (!editButton) return;

            // Ação para o botão EDITAR (✎)
            editButton.addEventListener('click', function() {
                card.classList.add('editing');
                inputs.forEach(input => input.disabled = false);
                if (inputs.length > 0) inputs[0].focus();
            });

            // Ação para o botão SALVAR (✓)
            saveButton.addEventListener('click', function() {
                // A mágica acontece aqui: o JavaScript não impede mais o envio.
                // Ele apenas submete o formulário específico daquele card.
                form.submit();
            });

            // Ação para o botão CANCELAR (✕)
            cancelButton.addEventListener('click', function() {
                // Restaura os valores originais dos campos
                inputs.forEach(input => {
                    input.value = originalValues[input.id];
                });
                // Sai do modo de edição visualmente
                card.classList.remove('editing');
                inputs.forEach(input => input.disabled = true);
            });
        });


        // --- NOVA LÓGICA PARA ATUALIZAÇÃO DA FOTO ---
        const photoEditButton = document.querySelector('.photo-edit-button');
        const inputNovaFoto = document.getElementById('input-nova-foto');
        const formFoto = document.getElementById('form-foto');

        // 1. Quando o botão de editar foto (✎) é clicado, ele clica no input de arquivo oculto.
        if (photoEditButton && inputNovaFoto) {
            photoEditButton.addEventListener('click', function() {
                inputNovaFoto.click(); // Abre a janela de seleção de arquivo
            });
        }

        // 2. Quando um novo arquivo é selecionado no input, o formulário da foto é enviado automaticamente.
        if (inputNovaFoto && formFoto) {
            inputNovaFoto.addEventListener('change', function() {
                // Verifica se um arquivo foi realmente selecionado
                if (this.files && this.files.length > 0) {
                    formFoto.submit(); // Envia o formulário para o PHP processar a imagem
                }
            });
        }
    });
    </script>
</body>
</html>
