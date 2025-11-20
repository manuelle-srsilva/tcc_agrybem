<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

// 1. VERIFICAÇÃO DE LOGIN
if (!isset($_SESSION['id_instituicao'])) {
    header('Location: instituicao_login.php');
    exit();
}

use Controller\InstituicaoController;
use Controller\EnderecoController;

$instituicaoController = new InstituicaoController();
$enderecoController = new EnderecoController();

// 2. PROCESSAMENTO DE ATUALIZAÇÕES
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['nova_foto']) && $_FILES['nova_foto']['error'] === UPLOAD_ERR_OK) {
        $fotoBin = file_get_contents($_FILES['nova_foto']['tmp_name']);
        $instituicaoController->updateInstituicaoFoto(
            $_SESSION['id_instituicao'],
            $fotoBin
        );
        // Redireciona para limpar o POST e mostrar a nova foto
        header('Location: instituicao_perfil.php');
        exit();
    }

    $formType = $_POST['form_type'] ?? '';

    // Ação para o card "Descrição"
    if ($formType === 'description') {
        // Assumindo que você criou um método 'updateDescription' no controller.
        $instituicaoController->updateInstituicaoDescricao(
            $_SESSION['id_instituicao'],
            $_POST['descricao']
        );
    }

    // Ação para o card "Informações da Instituição"
    if ($formType === 'institution') {
        // Assumindo que você criou um método 'updateProfile' que não atualiza a senha.
        $instituicaoController->updateInstituicao(
            $_SESSION['id_instituicao'],
            $_POST['nome'],
            $_POST['email'],
            $_POST['cnpj'],
            $_POST['link_whatsapp']
        );
    }

    // Ação para o card "Endereço"
    if ($formType === 'address') {
        $enderecoController->updateEndereco(
            $_SESSION['id_endereco'],
            $_POST['cep'],
            $_POST['rua'],
            $_POST['numero'],
            $_POST['bairro'],
            $_POST['cidade'],
            $_POST['estado'],
            $_POST['complemento']
        );
    }

    header('Location: instituicao_perfil.php');
    exit();
}

// 3. BUSCA DE DADOS PARA EXIBIÇÃO
// Assumindo que getInstituicaoInfo e getEnderecoInfo foram ajustados para buscar apenas com o ID.
$instituicaoInfo = $instituicaoController->getInstituicaoInfo($_SESSION['id_instituicao']);
$instituicaoFoto = $instituicaoController->getInstituicaoFoto($_SESSION['id_instituicao']);
$enderecoInfo = $enderecoController->getEnderecoInfo($_SESSION['id_endereco']);

$foto = ($instituicaoFoto && !empty($instituicaoFoto['foto'])) 
    ? 'data:image/jpeg;base64,' . base64_encode($instituicaoFoto['foto']) 
    : 'img/instituicao_padrao.png';

// 5. PREPARAÇÃO DA URL DO MAPA (NOVA LÓGICA)
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
    <title>Perfil da Instituição - Agrybem</title>
    <link rel="stylesheet" href="../templates/assets/css/doacao_perfil.css">
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
                <a href="../View/instituicao_pag_principal.php" class="nav-button">Voltar</a>
            </nav>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- HEADER SECTION -->
        <section class="header-section">
            <div class="container">
                <div class="header-top">
                    <form id="form-foto" action="instituicao_perfil.php" method="POST" enctype="multipart/form-data">
                        <div class="logo-institution">
                            <button type="button" class="photo-edit-button" title="Trocar Foto">✎</button>
                            <img src="<?= $foto ?>" alt="Logo da Instituição" class="logo-img">
                            <input type="file" name="nova_foto" id="input-nova-foto" style="display: none;" accept="image/*">
                        </div>
                    </form>
                    <div class="header-info">
                        <h1 class="institution-title"><?= htmlspecialchars($instituicaoInfo['nome'] ?? 'Nome da Instituição') ?></h1>
                    </div>
                </div>
            </div>
        </section>

        <!-- DESCRIPTION CARD -->
        <section class="card-section">
            <div class="container">
                <form class="profile-form" action="instituicao_perfil.php" method="POST">
                    <input type="hidden" name="form_type" value="description">
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
                            <label for="company-description">Descrição da Instituição</label>
                            <textarea id="company-description" name="descricao" disabled><?= htmlspecialchars($instituicaoInfo['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- INSTITUTION INFO CARD -->
        <section class="card-section">
            <div class="container">
                <form class="profile-form" action="instituicao_perfil.php" method="POST">
                    <input type="hidden" name="form_type" value="institution">
                    <div class="card" data-card="institution">
                        <div class="card-header">
                            <h2 class="card-title">Informações da instituição</h2>
                            <button type="button" class="card-edit-button" title="Editar">✎</button>
                        </div>
                        <div class="card-actions">
                            <button type="submit" class="card-save-button" title="Salvar">✓</button>
                            <button type="button" class="card-cancel-button" title="Cancelar">✕</button>
                        </div>
                        <div class="institution-form">
                            <div class="form-field">
                                <label for="company-name">Nome da Instituição</label>
                                <input type="text" id="company-name" name="nome" value="<?= htmlspecialchars($instituicaoInfo['nome'] ?? '') ?>" disabled>
                            </div>
                            <div class="form-field">
                                <label for="email">Email Institucional</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($instituicaoInfo['email'] ?? '') ?>" disabled>
                            </div>
                            <div class="form-field">
                                <label for="cpf">CNPJ</label>
                                <input type="text" id="cpf" name="cnpj" value="<?= htmlspecialchars($instituicaoInfo['cnpj'] ?? '') ?>" disabled>
                            </div>
                            <div class="form-field">
                                <label for="whatsapp">Link para WhatsApp</label>
                                <input type="text" id="whatsapp" name="link_whatsapp" value="<?= htmlspecialchars($instituicaoInfo['link_whatsapp'] ?? '') ?>" disabled>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- ADDRESS CARD -->
        <section class="card-section">
            <div class="container">
                <form class="profile-form" action="instituicao_perfil.php" method="POST">
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

    
    
    <!-- SCRIPT DE INTERAÇÃO -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.profile-form').forEach(form => {
            const card = form.querySelector('.card');
            if (!card) return;

            const editButton = card.querySelector('.card-edit-button');
            const saveButton = card.querySelector('.card-save-button');
            const cancelButton = card.querySelector('.card-cancel-button');
            const inputs = card.querySelectorAll('input, textarea');

            const originalValues = {};
            inputs.forEach(input => {
                originalValues[input.id] = input.value;
            });

            if (!editButton) return;

            editButton.addEventListener('click', function() {
                card.classList.add('editing');
                inputs.forEach(input => input.disabled = false);
                if (inputs.length > 0) inputs[0].focus();
            });

            saveButton.addEventListener('click', function() {
                form.submit();
            });

            cancelButton.addEventListener('click', function() {
                inputs.forEach(input => {
                    input.value = originalValues[input.id];
                });
                card.classList.remove('editing');
                inputs.forEach(input => input.disabled = true);
            });
        });


        // Lógica para troca de foto
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