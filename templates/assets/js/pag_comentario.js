document.addEventListener('DOMContentLoaded', () => {
    const commentsList = document.getElementById('comments-list');

    // Função para lidar com a exclusão de um comentário
    const handleDelete = (event) => {
        const deleteButton = event.currentTarget;
        const commentItem = deleteButton.closest('.comment-item');
        
        if (confirm('Tem certeza de que deseja excluir este comentário?')) {
            // Em um projeto real, você faria uma chamada AJAX para o servidor aqui
            console.log('Comentário excluído:', commentItem.querySelector('.comment-body').textContent);
            commentItem.remove();
            updateCommentCount();
        }
    };

    // Função para entrar no modo de edição
    const handleEdit = (event) => {
        const editButton = event.currentTarget;
        const commentItem = editButton.closest('.comment-item');
        const commentBody = commentItem.querySelector('.comment-body');
        const commentActions = commentItem.querySelector('.comment-actions');

        // 1. Esconder as ações originais (Editar/Excluir)
        commentActions.style.display = 'none';

        // 2. Criar o campo de edição (textarea)
        const originalText = commentBody.textContent.trim();
        const editArea = document.createElement('textarea');
        editArea.className = 'edit-textarea';
        editArea.value = originalText;
        
        // 3. Criar os botões de ação (Salvar/Cancelar)
        const editActionsDiv = document.createElement('div');
        editActionsDiv.className = 'edit-actions';
        
        const saveButton = document.createElement('button');
        saveButton.className = 'save-btn';
        saveButton.innerHTML = '<i class="fas fa-save"></i> Salvar';
        
        const cancelButton = document.createElement('button');
        cancelButton.className = 'cancel-btn';
        cancelButton.innerHTML = '<i class="fas fa-times"></i> Cancelar';

        editActionsDiv.appendChild(saveButton);
        editActionsDiv.appendChild(cancelButton);

        // 4. Inserir os novos elementos no DOM
        commentBody.style.display = 'none'; // Esconde o corpo do comentário
        commentItem.appendChild(editArea);
        commentItem.appendChild(editActionsDiv);

        editArea.focus();

        // 5. Adicionar ouvintes de evento para Salvar e Cancelar
        saveButton.addEventListener('click', (e) => {
            e.preventDefault();
            const newText = editArea.value.trim();
            if (newText) {
                // Em um projeto real, você faria uma chamada AJAX para o servidor aqui
                commentBody.textContent = newText;
                exitEditMode();
            } else {
                alert('O comentário não pode estar vazio.');
            }
        });

        cancelButton.addEventListener('click', (e) => {
            e.preventDefault();
            exitEditMode();
        });

        // Função para sair do modo de edição
        function exitEditMode() {
            commentBody.style.display = 'block';
            commentActions.style.display = 'flex';
            editArea.remove();
            editActionsDiv.remove();
        }
    };

    // Função para atualizar a contagem de comentários
    const updateCommentCount = () => {
        const count = commentsList.querySelectorAll('.comment-item').length;
        document.getElementById('comment-count').textContent = count;
    };

    // Função para anexar ouvintes de evento a um novo comentário
    const attachListeners = (commentItem) => {
        const editButton = commentItem.querySelector('.edit-btn');
        const deleteButton = commentItem.querySelector('.delete-btn');

        if (editButton) {
            editButton.addEventListener('click', handleEdit);
        }
        if (deleteButton) {
            deleteButton.addEventListener('click', handleDelete);
        }
    };

    // Anexar ouvintes de evento aos comentários existentes (o de exemplo)
    commentsList.querySelectorAll('.comment-item').forEach(attachListeners);
    updateCommentCount(); // Inicializa a contagem

    // Opcional: Lidar com o envio do formulário de novo comentário (apenas para demonstração)
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const commentInput = document.getElementById('comment-text');
            const newCommentText = commentInput.value.trim();

            if (newCommentText) {
                // Em um projeto real, você criaria o novo elemento de comentário
                // com os dados retornados do servidor (ID, data, etc.)
                console.log('Novo comentário enviado:', newCommentText);
                alert('Comentário enviado! (Funcionalidade de adição de novo comentário simulada)');
                commentInput.value = ''; // Limpa o campo
                
                // Simulação de adição de novo comentário (opcional, mas útil para teste)
                /*
                const newCommentItem = document.createElement('li');
                newCommentItem.className = 'comment-item';
                newCommentItem.innerHTML = `
                    <div class="comment-header">
                        <div class="comment-author-info">
                            <div class="comment-avatar">VC</div>
                            <div class="comment-author-details">
                                <span class="comment-author">Visitante</span>
                                <span class="comment-date">Agora</span>
                            </div>
                        </div>
                        <div class="comment-actions">
                            <button class="edit-btn" title="Editar Comentário">
                                <i class="fas fa-pencil-alt"></i> Editar
                            </button>
                            <button class="delete-btn" title="Excluir Comentário">
                                <i class="fas fa-trash-alt"></i> Excluir
                            </button>
                        </div>
                    </div>
                    <p class="comment-body">${newCommentText}</p>
                `;
                commentsList.prepend(newCommentItem);
                attachListeners(newCommentItem);
                updateCommentCount();
                */
            }
        });
    }
});