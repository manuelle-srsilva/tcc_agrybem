<?php
session_start();
require_once '../vendor/autoload.php';

use Controller\InstituicaoController;

$registerMessage = '';

// Ação 1: Lida com o upload da foto e salva na sessão
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    if (!isset($_POST['action']) || $_POST['action'] !== 'finalize') {
        $instituicaoController = new InstituicaoController();
        $fotoBin = file_get_contents($_FILES['foto']['tmp_name']);
        $instituicaoController->salvarInstituicaoFoto($fotoBin);
        header('Location: instituicao_foto.php');
        exit();
    }
}

// Ação 2: Lida com a finalização do cadastro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalize') {
    $instituicaoController = new InstituicaoController();

    // Salva a foto se ela foi enviada junto com o clique final
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fotoBin = file_get_contents($_FILES['foto']['tmp_name']);
        $instituicaoController->salvarInstituicaoFoto($fotoBin);
    }

    if ($instituicaoController->finalizarCadastroCompleto()) {
        header('Location: instituicao_login.php?status=success');
        exit();
    } else {
        $registerMessage = 'Ocorreu um erro ao finalizar seu cadastro. Tente novamente.';
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
          <!-- Header Fixo -->
        <header class="header">
            <div class="container">
                <div class="logo">
                    <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
                </div>
                 <nav class="nav">  
                <a href="doacao_descricao.html" class="nav-button">Voltar</a>
            </nav>

        </header>

        <!-- BARRA DE PROGRESSO -->
        <div class="progress-bar-container">
            <div class="progress-step active"><div class="progress-fill"></div></div>
            <div class="progress-step active"><div class="progress-fill"></div></div>
            <div class="progress-step active"><div class="progress-fill"></div></div>
            <div class="progress-step active"><div class="progress-fill"></div></div>
          
        </div>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="main-content">
            <!-- Botões Voltar e Avançar removidos. -->

            <!-- Formulário -->
            <div class="form-container">
                <h1 class="form-title">Foto de perfil!</h1>
                
                <form method="POST" class="form" enctype="multipart/form-data">
                    <div class="upload-box">
                        <input type="file" id="foto" name="foto" accept="image/*" class="upload-input" onchange="this.form.submit()">
                        <label for="foto" class="upload-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#1a1a1a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <p>Envie sua foto</p>
                        </label>
                    </div>

                    <div class="preview-container">
                        <img id="foto-preview" alt="Pré-visualização da foto" style="max-width:240px;max-height:240px;display:none;border-radius:8px;margin-top:12px;" />
                        <p id="foto-error" style="color:#c00;display:none;margin-top:8px;font-size:0.9rem"></p>
                    </div>

                    <input type="hidden" name="action" value="finalize">

                    <?php if ($registerMessage): ?>
                        <p class="error"><?php echo htmlspecialchars($registerMessage); ?></p>
                    <?php endif; ?>

                    <!-- BOTÃO OK INSERIDO AQUI -->
                    <div class="form-group form-button-ok">
                        <button type="submit" class="ok-button">OK</button>
                    </div>
                </form>
            </div>

            <!-- Botão Avançar removido. -->
        </main>

       
    </div>


    <script>
        // Preview selected image before upload (minimal, client-side)
        ;(function(){
            const input = document.getElementById('foto');
            const preview = document.getElementById('foto-preview');
            const err = document.getElementById('foto-error');
            const MAX_BYTES = 5 * 1024 * 1024; // 5MB

            if (!input) return;

            input.addEventListener('change', function(e){
                err.style.display = 'none';
                preview.style.display = 'none';
                preview.src = '';

                const file = this.files && this.files[0];
                if (!file) return;

                // basic validations
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
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>
</body>
</html>