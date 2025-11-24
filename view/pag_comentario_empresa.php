<?php
// ... (toda a lógica PHP do início do arquivo permanece a mesma)
session_start();
require_once '../vendor/autoload.php';

use Controller\ComentarioController;

$comentarioController = new ComentarioController();
$id_usuario_logado = $_SESSION['id'] ?? null;
$registerMessage = '';

// ID do empreendimento (prioriza GET, senão usa sessão da empresa logada)
$empreendimento_id = $_GET['id'] ?? $_SESSION['id_empreendimento'] ?? null;

// --- LÓGICA PARA DELETAR, ATUALIZAR E CRIAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$id_usuario_logado) {
        $registerMessage = "Você precisa estar logado para realizar esta ação.";
        goto skip_post;
    }

    $action = $_POST['action'] ?? null;
    $comentario_id = $_POST['comentario_id'] ?? null;
    $post_empreendimento = $_POST['id_empreendimento'] ?? $_GET['id'] ?? $_SESSION['id_empreendimento'] ?? null;

    if ($action === 'delete' && $comentario_id) {
        if ($comentarioController->deleteComentario($comentario_id)) {
            header('Location: pag_comentario_empresa.php' . ($post_empreendimento ? '?id=' . urlencode($post_empreendimento) : ''));
            exit();
        } else {
            $registerMessage = 'Erro ao excluir o comentário.';
        }
    } elseif ($action === 'update' && $comentario_id) {
        $novo_comentario = $_POST['comentario'] ?? '';
        if (!empty($novo_comentario)) {
            if ($comentarioController->updateComentario($comentario_id, $novo_comentario)) {
                header('Location: pag_comentario_empresa.php' . ($post_empreendimento ? '?id=' . urlencode($post_empreendimento) : ''));
                exit();
            } else {
                $registerMessage = 'Erro ao atualizar o comentário.';
            }
        } else {
            $registerMessage = 'O comentário não pode ficar vazio.';
        }
    } elseif ($action === 'create') {
        $comentario = $_POST['comentario'] ?? null;
        $id_empreendimento_post = $_POST['id_empreendimento'] ?? null;
        if (!$comentario) {
            $registerMessage = "O comentário não pode estar vazio.";
        } else {
            if ($comentarioController->cadastroComentario($comentario, $id_usuario_logado, $id_empreendimento_post)) {
                header('Location: pag_comentario_empresa.php' . ($id_empreendimento_post ? '?id=' . urlencode($id_empreendimento_post) : ''));
                exit();
            } else {
                $registerMessage = 'Erro ao postar comentário.';
            }
        }
    }
}

skip_post:

$comentarios = $comentarioController->listarComentarios($empreendimento_id);
$countComentarios = $comentarioController->countComentarios($empreendimento_id);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- ... (seu <head> permanece o mesmo, incluindo o <style>) ... -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentários - Feedback dos Clientes</title>
    <link rel="stylesheet" href="../templates/assets/css/pag_comentario1.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .edit-form { display: none; flex-direction: column; margin-top: 10px; }
        .edit-form textarea { width: 100%; min-height: 80px; margin-bottom: 10px; }
        .edit-form .edit-actions { display: flex; justify-content: flex-end; gap: 10px; }
    </style>
</head>
<body>

    <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
               
                <?php if (!empty($empreendimento_id)): ?>
                    <a href="empresa_loja.php?id=<?= urlencode($empreendimento_id) ?>" class="nav-link nav-button">Voltar</a>
                <?php else: ?>
                    <a href="painel_profissional_empresa.php" class="nav-link nav-button">Voltar</a>
                <?php endif; ?>
            </nav>
            <div class="menu-container">
                <button class="menu-toggle" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="dropdown-menu">
            
                    <a href="#" class="menu-item"><i class="fas fa-envelope menu-item-icon"></i> voltar</a>
                </div>
            </div>
        </div>
    </header>

    <main id="main-content">
        <div class="container">
            <section class="comment-section">
                <!-- ... (cabeçalho da seção e mensagens de alerta ) ... -->

                <div class="comments-list-container">
                    <div class="comments-header">
                        <h2>Comentários</h2>
                        <span class="comment-count-badge"><?= $countComentarios ?></span>
                    </div>

                    <div id="comments-list">
                        <?php if ($comentarios): ?>
                            <?php foreach ($comentarios as $comentario): ?>
                                <!-- ESTRUTURA DO ITEM DE COMENTÁRIO (permanece a mesma) -->
                                <div class="comment-item" id="comment-<?= $comentario['id'] ?>">
                                    <div class="comment-header">
                                        <div class="comment-author-info">
                                            <span class="comment-author"><?= htmlspecialchars($comentario['nome_cliente'] ?? 'Anônimo') ?></span>
                                            <span class="comment-date"><?= htmlspecialchars($comentario['data_post']) ?></span>
                                        </div>
                                        
                                        <?php if ($id_usuario_logado && $id_usuario_logado == $comentario["id_cliente"]): ?>
                                            <div class="comment-actions">
                                                
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="comment-body" id="comment-body-<?= $comentario['id'] ?>">
                                        <?= nl2br(htmlspecialchars($comentario['comentario'])) ?>
                                    </div>

                                        <?php if ($id_usuario_logado && $id_usuario_logado == $comentario["id_cliente"]): ?>
                                        <form method="post" action="pag_comentario_empresa.php" class="edit-form" id="edit-form-<?= $comentario['id'] ?>">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="comentario_id" value="<?= $comentario['id'] ?>">
                                            <input type="hidden" name="id_empreendimento" value="<?= htmlspecialchars($empreendimento_id) ?>">
                                            <textarea name="comentario" required><?= htmlspecialchars($comentario['comentario']) ?></textarea>
                                            <div class="edit-actions">
                                                <button type="button" class="cancel-btn" onclick="toggleEdit(<?= $comentario['id'] ?>)">Cancelar</button>
                                                <button type="submit" class="save-btn">Salvar</button>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>

</body>
</html>