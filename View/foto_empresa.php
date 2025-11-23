<?php
session_start();
require_once '../vendor/autoload.php';

use Controller\EmpreendedorController;

$registerMessage = '';

// Ação 1: Lida APENAS com o upload da foto.
// Isso acontece quando o formulário é auto-enviado pelo 'onchange' do input.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    
    // Verifica se o upload não foi acionado pelo botão de finalizar ao mesmo tempo
    if (!isset($_POST['action']) || $_POST['action'] !== 'finalize') {
        $empreendimentoController = new \Controller\EmpreendimentoController();
        $tmpName = $_FILES['foto']['tmp_name'];
        $fotoBin = file_get_contents($tmpName);

        // Salva a foto na sessão. O script para aqui e a página recarrega mostrando o preview.
        $empreendimentoController->salvarEmpreendimentoFoto($fotoBin);
        
        // Redireciona para a mesma página para limpar o POST e evitar reenvio
        header('Location: foto_empresa.php');
        exit();
    }
}

// Ação 2: Lida APENAS com a finalização do cadastro.
// Isso acontece quando o botão de avançar (>) é clicado.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalize') {
    
    // Se o usuário clicou em finalizar mas ainda não enviou uma foto, salva a foto primeiro.
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $empreendimentoController = new \Controller\EmpreendimentoController();
        $tmpName = $_FILES['foto']['tmp_name'];
        $fotoBin = file_get_contents($tmpName);
        $empreendimentoController->salvarEmpreendimentoFoto($fotoBin);
    }

    $empreendedorController = new EmpreendedorController();

    // Chama o método centralizador que salva tudo no banco
    if ($empreendedorController->finalizarCadastroCompleto()) {
        // Sucesso! Redireciona para a página de login com uma mensagem de sucesso.
        header('Location: login_empresa.php?status=success');
        exit();
    } else {
        // Falha! Exibe uma mensagem de erro.
        $registerMessage = 'Ocorreu um erro ao finalizar seu cadastro. Por favor, tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foto de Perfil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/formulario_foto.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- HEADER -->
        <header class="header">
            <div class="container">
                <div class="logo">
                    <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
                </div>
            </div>
        </header>

        <!-- BARRA DE PROGRESSO -->
        <div class="progress-bar-container">
            <div class="progress-step active"><div class="progress-fill"></div></div>
            <div class="progress-step active"><div class="progress-fill"></div></div>
            <div class="progress-step active"><div class="progress-fill"></div></div>
            <div class="progress-step active"><div class="progress-fill"></div></div>
            <div class="progress-step active"><div class="progress-fill"></div></div>
        </div>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="main-content">
            <!-- Formulário -->
            <div class="form-container">
                <h1 class="form-title">Foto de perfil!</h1>
                
                <form class="form" method="POST" enctype="multipart/form-data">
                    <div class="upload-box">
                        <input type="file" id="foto" name="foto" accept="image/*" class="upload-input">
                        <label for="foto" class="upload-label">
                            <!-- Conteúdo original para o estado vazio -->
                            <div id="upload-content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#1a1a1a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                <p>Envie sua foto</p>
                            </div>
                            <!-- A imagem de preview agora está dentro do label -->
                            <img id="foto-preview" alt="Pré-visualização da foto" />
                        </label>
                    </div>
                    
                    <!-- Mensagem de erro para validação do arquivo -->
                    <p id="foto-error" style="color:#c00;display:none;margin-top:8px;font-size:0.9rem;text-align:center;"></p>

                    <input type="hidden" name="action" value="finalize">
                    
                    <?php if ($registerMessage ): ?>
                        <p class="error" style="text-align:center; color: #c00;"><?php echo htmlspecialchars($registerMessage); ?></p>
                    <?php endif; ?>

                    <!-- BOTÃO OK -->
                    <div class="form-group form-button-ok">
                        <button type="submit" class="ok-button">OK</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
    // Preview da imagem selecionada dentro da caixa de upload
    ;(function(){
        const input = document.getElementById('foto');
        const label = document.querySelector('.upload-label'); // Seleciona o label
        const preview = document.getElementById('foto-preview');
        const err = document.getElementById('foto-error');
        const MAX_BYTES = 5 * 1024 * 1024; // 5MB

        if (!input) return;

        input.addEventListener('change', function(e){
            err.style.display = 'none';
            label.classList.remove('has-image'); // Remove a classe ao trocar de imagem
            preview.src = '';

            const file = this.files && this.files[0];
            if (!file) return;

            // Validações
            if (!file.type || !file.type.startsWith('image/')) {
                err.textContent = 'Por favor selecione um arquivo de imagem (jpg, png, etc).';
                err.style.display = 'block';
                this.value = '';
                return;
            }

            if (file.size > MAX_BYTES) {
                err.textContent = 'Arquivo muito grande. Tamanho máximo: 5MB.';
                err.style.display = 'block';
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(ev){
                preview.src = ev.target.result;
                label.classList.add('has-image'); // Adiciona classe para mostrar a imagem
            };
            reader.readAsDataURL(file);
        });
    })();
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
        new window.VLibras.Widget('https://vlibras.gov.br/app' );
    </script>
</body>
</html>
