<?php
// CRIAR PRODUTO
// OBTER TODAS AS INFORMAÇÕES DO PRODUTO

namespace Model;

use Exception;
use Model\Connection;

use PDO;
use PDOException;

class Produto {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function registerProduto ($nome, $preco, $medida, $categoria, $foto, $id_empreendimento) {
        try {
            $sql = 'INSERT INTO produto (nome, preco, categoria, medida, foto, id_empreendimento) VALUES (:nome, :preco, :categoria, :medida, :foto, :id_empreendimento)';

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":preco", $preco, PDO::PARAM_STR);
            $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
            $stmt->bindParam(":medida", $medida, PDO::PARAM_STR);
            $stmt->bindParam(":foto", $foto, PDO::PARAM_LOB);
            $stmt->bindParam(":id_empreendimento", $id_empreendimento, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function getProdutoInfo ($id) {
        try {
            $sql = "SELECT nome, preco, categoria, medida, foto, id_empreendimento FROM produto WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            echo "Erro ao buscar informações: " . $error->getMessage();
            return false;
        }
    }

    public function getProdutosByEmpreendimento($id_empreendimento) {
        try {
            $sql = "SELECT id, nome, preco, categoria, medida, foto FROM produto WHERE id_empreendimento = :id_empreendimento";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id_empreendimento", $id_empreendimento, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            echo "Erro ao buscar produtos: " . $error->getMessage();
            return false;
        }
    }

    public function updateProduto ($id, $nome, $preco) {
        try {
            $sql = "UPDATE produto SET nome = :nome, preco = :preco WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":preco", $preco, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function deleteProduto ($id) {
        try {
            $sql = "DELETE FROM produto WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }
}

?>