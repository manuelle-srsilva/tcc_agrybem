<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
use Controller\InstituicaoController;
use Model\Endereco;

$instController = new InstituicaoController();
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$instInfo = null;
$instFoto = null;
$endereco = null;
if($id){
    $instInfo = $instController->getInstituicaoInfo($id);
    $instFoto = $instController->getInstituicaoFoto($id);
    if(!empty($instInfo['id_endereco'])){
        $endModel = new Endereco();
        $endereco = $endModel->getEnderecoInfo($instInfo['id_endereco']);
    }
}

// PREPARAÇÃO DA URL DO MAPA
$mapQuery = 'Brasil'; // Valor padrão caso não haja endereço
if ($endereco) {
    $mapParts = [
        $endereco['rua'] ?? '',
        $endereco['numero'] ?? '',
        $endereco['bairro'] ?? '',
        $endereco['cidade'] ?? '',
        $endereco['estado'] ?? '',
        $endereco['cep'] ?? ''
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
    <title><?php echo htmlspecialchars($instInfo['nome'] ?? 'Instituição'); ?> - Agrybem</title>
    <link rel="stylesheet" href="../templates/assets/css/doacao_instituicao.css">
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
        <!-- HEADER SECTION WITH LOGO AND TITLE -->
        <section class="header-section">
            <div class="container">
                <div class="header-top" style="justify-content: flex-start">
                            <div class="logo-institution">
                                <?php
                                if(!empty($instFoto['foto'])){
                                    $base = base64_encode($instFoto['foto']);
                                    echo '<img src="data:image/jpeg;base64,' . $base . '" alt="' . htmlspecialchars($instInfo['nome'] ?? '') . '" class="logo-img">';
                                } else {
                                    echo '<img src="../templates/assets/img/instituição 1.png" alt="Logo" class="logo-img">';
                                }
                                ?>
                            </div>

                            <div class="header-info">
                                <h1 class="institution-title"><?php echo htmlspecialchars($instInfo['nome'] ?? 'Instituição'); ?></h1>
                            </div>
                        </div>
            </div>
        </section>

        <!-- DESCRIPTION CARD -->
        <section class="card-section">
            <div class="container">
                    <div class="card" data-card="description">
                    <div class="card-actions">
                        <button class="card-save-button" title="Salvar">✓</button>
                        <button class="card-cancel-button" title="Cancelar">✕</button>
                    </div>
                    <div class="form-field">
                        <textarea id="company-description" disabled><?php echo htmlspecialchars($instInfo['descricao'] ?? 'Descrição não disponível.'); ?></textarea>
                    </div>
                </div>
            </div>
        </section>

        <!-- INSTITUTION INFO CARD -->
        <section class="card-section">
            <div class="container">
                        <div class="card" data-card="institution">
                    <div class="card-header">
                        <h2 class="card-title">Informações da Instituição</h2>
                    </div>
                    <!-- REMOVIDO: style="display: none;" -->
                    <div class="card-actions">
                        <button class="card-save-button" title="Salvar">✓</button>
                        <button class="card-cancel-button" title="Cancelar">✕</button>
                    </div>
                    <form class="institution-form" id="institutionForm">
                        

                        <div class="form-field">
                            <label for="company-name">Nome da Instituição</label>
                            <input type="text" id="company-name" value="<?php echo htmlspecialchars($instInfo['nome'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="email">Email Institucional</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($instInfo['email'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="cpf">CNPJ</label>
                            <input type="text" id="cpf" value="<?php echo htmlspecialchars($instInfo['cnpj'] ?? ''); ?>" disabled>
                        </div>
                        
                        <div class="form-field">
                            <label for="whatsapp">Link para WhatsApp</label>
                            <input type="text" id="whatsapp" value="<?php echo htmlspecialchars($instInfo['link_whatsapp'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="hours">Horário de Funcionamento</label>
                            <input type="text" id="hours" value="Seg - Sex: 07:00 - 17:00 • Sáb: 07:00 - 12:00 • Dom: Fechado" disabled>
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
                            <input type="text" id="cep" value="<?php echo htmlspecialchars($endereco['cep'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="street">Rua</label>
                            <input type="text" id="street" value="<?php echo htmlspecialchars($endereco['rua'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="number">Número</label>
                            <input type="text" id="number" value="<?php echo htmlspecialchars($endereco['numero'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="neighborhood">Bairro</label>
                            <input type="text" id="neighborhood" value="<?php echo htmlspecialchars($endereco['bairro'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="city">Cidade</label>
                            <input type="text" id="city" value="<?php echo htmlspecialchars($endereco['cidade'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="state">Estado</label>
                            <input type="text" id="state" value="<?php echo htmlspecialchars($endereco['estado'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="reference">Ponto de Referência</label>
                            <input type="text" id="reference" value="<?php echo htmlspecialchars($endereco['complemento'] ?? ''); ?>" disabled>
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
    </main>

  
    
    <script src="js/empresa_perfil.js"></script>

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