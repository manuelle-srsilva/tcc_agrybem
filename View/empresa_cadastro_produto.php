<?php
session_start();
require_once '../vendor/autoload.php';

use Controller\ProdutoController;

$registerMessage = '';

// Verifica se o empreendedor está logado e pega o id_empreendimento
if (!isset($_SESSION['id_empreendimento'])) {
    // Redireciona para o login se não estiver logado
    header('Location: login_empresa.php');
    exit();
}

$id_empreendimento = $_SESSION['id_empreendimento'];
$produtoController = new ProdutoController();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_raw = $_POST['nome'] ?? '';
    $preco_raw = $_POST['preco'] ?? '';
    $medida_raw = $_POST['unidadeMedida'] ?? '';
    $categoria_raw = $_POST['categoria'] ?? '';

    $nome = htmlspecialchars(trim($nome_raw), ENT_QUOTES, 'UTF-8');
    $preco = filter_var(str_replace(',', '.', trim($preco_raw)), FILTER_VALIDATE_FLOAT);
    $medida = htmlspecialchars(trim($medida_raw), ENT_QUOTES, 'UTF-8');
    $categoria = htmlspecialchars(trim($categoria_raw), ENT_QUOTES, 'UTF-8');

    // validações básicas
    if (!$nome || $preco === false || !$medida || !$categoria) {
        $registerMessage = 'Por favor, preencha todos os campos corretamente.';
    } elseif (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $registerMessage = 'Por favor, selecione uma foto válida.';
    } else {
        $tmpName = $_FILES['foto']['tmp_name'];
        $fotoBin = file_get_contents($tmpName);

        // unidade de medida aceita
        $validMedidas = ['kg','un'];
        if (!in_array($medida, $validMedidas)) {
            $registerMessage = 'Unidade de medida inválida.';
        } else {
            if($produtoController->cadastroProduto($nome, $preco, $categoria, $medida, $fotoBin, $id_empreendimento)) {
                header('Location: empresa_produto.php?status=success');
                exit();
            } else {
                $registerMessage = 'Ocorreu um erro ao cadastrar o produto. Por favor, tente novamente.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Empresa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="../templates/assets/css/empresa_cadastro_produto.css">
	    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <a href="../View/empresa_produto.php" class="nav-button">Voltar</a>
            </nav>

        </header>

        

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Botão Anterior (Esquerda) -->
       
            

            <!-- Formulário -->
            <div class="form-container">
                <h1 class="form-title">Informações do seu produto!</h1>
                
                <form class="form" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome  </label>
                        <input type="text" id="nome" name="nome" class="form-input" placeholder="Ex: Tomate Cereja">
                    </div>

                    <!-- NOVO CAMPO: Unidade de Medida (Acordeão - CSS PURE) -->
                    <div class="accordion-group">
                        <input type="checkbox" id="acc-unidade" class="accordion-toggle" hidden>
                        <label for="acc-unidade" class="accordion-header">
                          
                            <span class="form-label">Unidade de Medida</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </label>
                        <div class="accordion-content">
                            <div class="form-group">
                                <select id="unidadeMedida" name="unidadeMedida" class="form-input">
                                    
                                    <option value="kg">kg </option>

                                    <option value="un">un</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- CAMPO EXISTENTE: Preço (Ajustado para number com prefixo) -->
                    <div class="form-group">
                        <label for="preco" class="form-label">Preço</label>
                        <div class="input-with-prefix">
                            <span class="prefix">R$</span>
                            <input type="number" id="preco" name="preco" class="form-input" placeholder="0.00" step="0.01">
                        </div>
                    </div>

                    <!-- CAMPO EXISTENTE: Categoria (Acordeão - CSS PURE) -->
                    <div class="accordion-group">
                        <input type="checkbox" id="acc-categoria" class="accordion-toggle" hidden>
                        <label for="acc-categoria" class="accordion-header">
                            <span class="form-label">Categoria</span>
                            <i class="fas fa-chevron-down accordion-icon"></i>
                        </label>
                        <div class="accordion-content">
                            <div class="categoria-opcoes">
                                <label class="categoria-item">
                                    <input type="radio" name="categoria" value="Fruta">
                                    <span>Fruta</span>
                                </label>

                                <label class="categoria-item">
                                    <input type="radio" name="categoria" value="Legume">
                                    <span>Legume</span>
                                </label>

                                <label class="categoria-item">
                                    <input type="radio" name="categoria" value="Verdura">
                                    <span>Verdura</span>
                                </label>

                                <label class="categoria-item">
                                    <input type="radio" name="categoria" value="Raiz">
                                    <span>Raiz</span>
                                </label>

                                <label class="categoria-item">
                                    <input type="radio" name="categoria" value="Grãos e Cereais">
                                    <span>Grãos e Cereais</span>
                                </label>

                                <label class="categoria-item">
                                    <input type="radio" name="categoria" value="Ervas e Temperos">
                                    <span>Ervas e Temperos</span>
                                </label>

                                <label class="categoria-item">
                                    <input type="radio" name="categoria" value="Oleaginosas">
                                    <span>Oleaginosas</span>
                                </label>
                            </div>
                        </div>
                    </div>

<!-- UPLOAD DE FOTO -->
<div class="form-group upload-group">
	  <label class="form-label">Foto</label>
	  <div class="upload-box" onclick="document.getElementById('uploadFoto').click()">
	    <img id="previewFoto" src="../templates/assets/img/upload-de-arquivo 2.png" alt="Upload" class="upload-icon">
	    <input type="file" id="uploadFoto" name="foto" accept="image/*" style="display: none;">
	  </div>
	</div>

<!-- BOTÃO OK -->
	<div class="form-group form-button-ok">
  <button type="submit" class="ok-button">OK</button>
</div>


		       
	    </div>
    <script>
    // Script para pré-visualização da imagem
    document.getElementById('uploadFoto').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewFoto').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
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