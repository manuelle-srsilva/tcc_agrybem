<?php
// CRIAR EMPREENDIMENTO
// OBTER NOME DO EMPREENDIMENTO
// OBTER TODAS AS INFORMAÇÕES DO EMPREENDIMENTO

namespace Model;

use Model\Connection;

use PDO;
use PDOException;

class Empreendimento {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function registerEmpreendimento ($nome, $telefone, $link_whatsapp, $descricao, $hr_funcionamento, $foto, $id_endereco) {
        try {
            $sql = 'INSERT INTO empreendimento (nome, telefone, link_whatsapp, descricao, hr_funcionamento, foto, id_endereco) VALUES (:nome, :telefone, :link_whatsapp, :descricao, :hr_funcionamento, :foto, :id_endereco)';

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":telefone", $telefone, PDO::PARAM_STR);
            $stmt->bindParam(":link_whatsapp", $link_whatsapp, PDO::PARAM_STR);
            $stmt->bindParam(":descricao", $descricao, PDO::PARAM_STR);
            $stmt->bindParam(":hr_funcionamento", $hr_funcionamento, PDO::PARAM_STR);
            $stmt->bindParam(":foto", $foto, PDO::PARAM_LOB);
            $stmt->bindParam(":id_endereco", $id_endereco, PDO::PARAM_INT);

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

    public function getEmpreendimentoName ($id) {
        try {
            $sql = "SELECT nome FROM empreendimento WHERE id = :id";

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
    public function getEmpreendimentoDescricao ($id) {
        try {
            $sql = "SELECT descricao FROM empreendimento WHERE id = :id";

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

    public function getEmpreendimentoTelefone ($id) {
        try {
            $sql = "SELECT telefone FROM empreendimento WHERE id = :id";

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

    public function getEmpreendimentoFuncionamento ($id) {
        try {
            $sql = "SELECT hr_funcionamento FROM empreendimento WHERE id = :id";

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

    public function getEmpreendimentoWhatsapp ($id) {
        try {
            $sql = "SELECT Alink_whatsapp FROM empreendimento WHERE id = :id";

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

    public function getEmpreendimentoFoto ($id) {
        try {
            $sql = "SELECT foto FROM empreendimento WHERE id = :id";

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

    public function getAllEmpreendimentos() {
        try {
            // Seleciona as informações necessárias para o cartão:
            // id (para o link), nome, foto, cidade e bairro (do endereço)
            $sql = "SELECT e.id, e.nome, e.foto, a.cidade, a.bairro 
                    FROM empreendimento e
                    JOIN endereco a ON e.id_endereco = a.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            error_log("Erro ao buscar todos os empreendimentos: " . $error->getMessage());
            return [];
        }
    }

    public function getEmpreendimentoInfo ($id) {
        try {
            $sql = "SELECT nome, telefone, link_whatsapp, descricao, hr_funcionamento, id_endereco FROM empreendimento WHERE id = :id";

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

    public function updateEmpreendimento ($id, $nome, $telefone, $link_whatsapp, $descricao, $hr_funcionamento) {
        try {
            $sql = 'UPDATE empreendimento SET nome = :nome, telefone = :telefone, link_whatsapp = :link_whatsapp, descricao = :descricao, hr_funcionamento = :hr_funcionamento WHERE id = :id';

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":telefone", $telefone, PDO::PARAM_STR);
            $stmt->bindParam(":link_whatsapp", $link_whatsapp, PDO::PARAM_STR);
            $stmt->bindParam(":descricao", $descricao, PDO::PARAM_STR);
            $stmt->bindParam(":hr_funcionamento", $hr_funcionamento, PDO::PARAM_STR);

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

    public function updateEmpreendimentoFoto ($id, $foto) {
        try {
            $sql = 'UPDATE empreendimento SET foto = :foto WHERE id = :id';

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":foto", $foto, PDO::PARAM_LOB);
            
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

    public function deleteEmpreendimento ($id) {
        try {
            $sql = "DELETE FROM empreendimento WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

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
}

?>