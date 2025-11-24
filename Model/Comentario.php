<?php

namespace Model;

use Exception;
use Model\Connection;

use PDO;
use PDOException;

class Comentario {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function registerComentario ($comentario, $id_cliente, $id_empreendimento = null) {
        try {
            // Tenta inserir com id_empreendimento quando fornecido. Se a coluna não existir,
            // captura a exceção e tenta o insert simples para manter compatibilidade.
            if (!empty($id_empreendimento)) {
                $sql = 'INSERT INTO comentario (comentario, id_cliente, id_empreendimento) VALUES (:comentario, :id_cliente, :id_empreendimento)';
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(":comentario", $comentario, PDO::PARAM_STR);
                $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
                $stmt->bindParam(":id_empreendimento", $id_empreendimento, PDO::PARAM_INT);
                return $stmt->execute();
            }

            // Fallback quando não foi passado id_empreendimento
            $sql = 'INSERT INTO comentario (comentario, id_cliente) VALUES (:comentario, :id_cliente)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":comentario", $comentario, PDO::PARAM_STR);
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            return $stmt->execute();
        }
        catch (PDOException $error) {
            // Caso tenha tentado inserir com id_empreendimento e a coluna não exista,
            // tenta o insert simples como fallback.
            try {
                $sql = 'INSERT INTO comentario (comentario, id_cliente) VALUES (:comentario, :id_cliente)';
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(":comentario", $comentario, PDO::PARAM_STR);
                $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                // Se falhar novamente, log e retorna false
                error_log("Erro ao executar o comando registerComentario: " . $e->getMessage());
                return false;
            }
        }
    }

    public function getComentarioInfo ($id, $comentario, $id_cliente, $data_post) {
        try {
            $sql = "SELECT comentario FROM comentario WHERE id = :id AND comentario = :comentario AND data_post = :data_post";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":comentario", $comentario, PDO::PARAM_STR);
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->bindParam(":data_post", $data_post, PDO::PARAM_STR);
            
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            echo "Erro ao buscar informações: " . $error->getMessage();
            return false;
        }
    }

    public function listComentarios($id_empreendimento = null) {
        try{
            if (!empty($id_empreendimento)) {
                // Tenta filtrar por empreendimento
                $sql = "SELECT c.*, cli.nome AS nome_cliente FROM comentario c JOIN cliente cli ON c.id_cliente = cli.id WHERE c.id_empreendimento = :id ORDER BY c.data_post DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id_empreendimento, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $sql = "SELECT c.*, cli.nome AS nome_cliente FROM comentario c JOIN cliente cli ON c.id_cliente = cli.id ORDER BY c.data_post DESC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            // Se falhar ao tentar filtrar (p.ex. coluna inexistente), tenta a query sem filtro
            try {
                $sql = "SELECT c.*, cli.nome AS nome_cliente FROM comentario c JOIN cliente cli ON c.id_cliente = cli.id ORDER BY c.data_post DESC";
                return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Erro ao carregar comentários: " . $e->getMessage());
                return false;
            }
        }
    }

    public function updateComentario ($id, $comentario) {
        try {
            $sql = "UPDATE comentario SET comentario = :comentario WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":comentario", $comentario, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function deleteComentario ($id) {
        try {
            $sql = "DELETE FROM comentario WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function countComentarios($id_empreendimento = null){
        try{
            if (!empty($id_empreendimento)) {
                $sql = "SELECT COUNT(*) AS total FROM comentario WHERE id_empreendimento = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id_empreendimento, PDO::PARAM_INT);
                $stmt->execute();
                return (int) $stmt->fetchColumn();
            }

            $sql = "SELECT COUNT(*) AS total FROM comentario";
            return (int) $this->db->query($sql)->fetchColumn();
        }catch (PDOException $error){
            // Fallback sem filtro
            try {
                $sql = "SELECT COUNT(*) AS total FROM comentario";
                return (int) $this->db->query($sql)->fetchColumn();
            } catch (PDOException $e) {
                error_log("Erro ao contar comentários: " . $e->getMessage());
                return 0;
            }
        }
    }
}

?>