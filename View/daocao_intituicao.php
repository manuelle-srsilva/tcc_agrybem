<?php
session_start();
require_once '../vendor/autoload.php';

use Controller\InstituicaoController;
use Controller\EnderecoController;

$instituicao_id = $_SESSION['id_instituicao'] ?? null;

if (!$instituicao_id) {
    // Redireciona ou mostra uma mensagem de erro se o ID não for fornecido
    header('Location: instituicao_pag_principal.php');
    exit();
}

$instituicaoController = new InstituicaoController();
$enderecoController = new EnderecoController();

// Busca as informações do instituicao
$instituicaoInfo = $instituicaoController->getInstituicaoInfo($instituicao_id);
$instituicaoFoto = $instituicaoController->getInstituicaoFoto($instituicao_id);

// Busca as informações do endereço do instituicao
$enderecoInfo = null;
if ($instituicaoInfo && isset($instituicaoInfo['id_endereco'])) {
    $enderecoInfo = $enderecoController->getEnderecoInfo($instituicaoInfo['id_endereco']);
}

// Converte a foto para base64
$foto_base64 = '';
if ($instituicaoFoto && isset($instituicaoFoto['foto'])) {
    $foto_base64 = base64_encode($instituicaoFoto['foto']);
}
$foto_src = 'data:image/jpeg;base64,' . $foto_base64;

// PREPARAÇÃO DA URL DO MAPA
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
    <title>Agrybem - Página Principal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/loja.css">
    <script src="js/carrinho.js" defer></script>
</head>
<body>
    <!-- Header -->
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
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
       
        </div>
    </section>
    <!-- CONTEÚDO DO PERFIL INTEGRADO (Sem tag <main> para evitar conflitos) -->
        <!-- HEADER SECTION WITH LOGO AND TITLE -->
        <section class="header-section">
            <div class="container">
                <div class="header-top" style="justify-content: flex-start;">
                    <div class="logo-institution">
                       
                        <img src="<?php echo $foto_src; ?>" alt="<?php echo htmlspecialchars($instituicaoInfo['nome']); ?>" class="logo-img">
                    </div>

                    <div class="header-info">
                        <h1 class="institution-title"><?php echo htmlspecialchars($instituicaoInfo['nome']); ?></h1>
        
                    </div>
                    <!-- Ícone do Carrinho Movido para Aqui -->

                </div>
                
            </div>
        </section>

        <div class="container">
            <div class="comment-feed">
                <a href="../View/pag_comentario.php">Comentários</a>
            </div>
        </div>

        <!-- DESCRIPTION CARD -->
        <section class="card-section">
            <div class="container">
                <div class="card" data-card="description">
                  
                    <!-- REMOVIDO: style="display: none;" -->
                    <div class="card-actions">
                        <button class="card-save-button" title="Salvar">✓</button>
                        <button class="card-cancel-button" title="Cancelar">✕</button>
                    </div>
                    <div class="form-field">
                       
                        <textarea id="company-description" disabled><?php echo htmlspecialchars($instituicaoInfo['descricao']); ?></textarea>
                    </div>
                </div>
            </div>
        </section>

        <!-- INSTITUTION INFO CARD -->
        <section class="card-section">
            <div class="container">
                <div class="card" data-card="institution">
                   
                    <!-- REMOVIDO: style="display: none;" -->
                    <div class="card-actions">
                        <button class="card-save-button" title="Salvar">✓</button>
                        <button class="card-cancel-button" title="Cancelar">✕</button>
                    </div>
                    <form class="institution-form" id="institutionForm">
                        

                        <div class="form-field">
                            <label for="company-name">Nome </label>
                            <input type="text" id="company-name" value="<?php echo htmlspecialchars($instituicaoInfo['nome']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="whatsapp">Link para WhatsApp</label>
                            <input type="text" id="whatsapp" value="<?php echo htmlspecialchars($instituicaoInfo['link_whatsapp']); ?>" disabled>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- ADDRESS CARD -->
        <section class="card-section">
            <div class="container">
                <div class="card" data-card="address">
                    <div class="card-header">
                        <h2 class="card-title">Endereço</h2>
                      
                    </div>
                    <!-- REMOVIDO: style="display: none;" -->
                    <div class="card-actions">
                        <button class="card-save-button" title="Salvar">✓</button>
                        <button class="card-cancel-button" title="Cancelar">✕</button>
                    </div>
                    <form class="address-form" id="addressForm">
                        <div class="form-field">
                            <label for="cep">CEP</label>
                            <input type="text" id="cep" value="<?php echo htmlspecialchars($enderecoInfo['cep']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="street">Rua</label>
                            <input type="text" id="street" value="<?php echo htmlspecialchars($enderecoInfo['rua']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="number">Número</label>
                            <input type="text" id="number" value="<?php echo htmlspecialchars($enderecoInfo['numero']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="neighborhood">Bairro</label>
                            <input type="text" id="neighborhood" value="<?php echo htmlspecialchars($enderecoInfo['bairro']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="city">Cidade</label>
                            <input type="text" id="city" value="<?php echo htmlspecialchars($enderecoInfo['cidade']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="state">Estado</label>
                            <input type="text" id="state" value="<?php echo htmlspecialchars($enderecoInfo['estado']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="reference">Ponto de Referência</label>
                            <input type="text" id="reference" value="<?php echo htmlspecialchars($enderecoInfo['complemento']); ?>" disabled>
                        </div>
                    </form>
                </div>
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