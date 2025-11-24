<?php
// CRIAR ENDEREÇO
// OBTER CIDADE E BAIRRO DO ENDEREÇO
// OBTER TODAS AS INFORMAÇÕES DO ENDEREÇO

namespace Model;

use Model\Connection;

use PDO;
use PDOException;

class Endereco {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function registerEndereco ($cep, $rua, $numero, $bairro, $cidade, $estado, $complemento) {
        try {
            $sql = 'INSERT INTO endereco (cep, rua, numero, bairro, cidade, estado, complemento) VALUES (:cep, :rua, :numero, :bairro, :cidade, :estado, :complemento)';

            $stmt = $this->db->prepare($sql);

            // CEP can have leading zeros; treat as string
            $stmt->bindParam(":cep", $cep, PDO::PARAM_STR);
            $stmt->bindParam(":rua", $rua, PDO::PARAM_STR);
            $stmt->bindParam(":numero", $numero, PDO::PARAM_INT);
            $stmt->bindParam(":bairro", $bairro, PDO::PARAM_STR);
            $stmt->bindParam(":cidade", $cidade, PDO::PARAM_STR);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":complemento", $complemento, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // CORREÇÃO: Retorna o ID do endereço que acabou de ser inserido.
                return $this->db->lastInsertId();
            }
            return false;
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function getEnderecoResumido ($id, $cidade, $bairro) {
        try {
            $sql = "SELECT cidade, bairro FROM endereco WHERE id = :id AND cidade = :cidade AND bairro = :bairro";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":cidade", $cidade, PDO::PARAM_STR);
            $stmt->bindParam(":bairro", $bairro, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            echo "Erro ao buscar informações: " . $error->getMessage();
            return false;
        }
    }

    public function getEnderecoInfo ($id) {
        try {
            $sql = "SELECT cep, rua, numero, bairro, cidade, estado, complemento FROM endereco WHERE id = :id";

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

    public function updateEndereco ($id, $cep, $rua, $numero, $bairro, $cidade, $estado, $complemento) {
        try {
            $sql = 'UPDATE endereco SET cep = :cep, rua = :rua, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, complemento = :complemento WHERE id = :id';

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            // CEP can have leading zeros; treat as string
            $stmt->bindParam(":cep", $cep, PDO::PARAM_STR);
            $stmt->bindParam(":rua", $rua, PDO::PARAM_STR);
            $stmt->bindParam(":numero", $numero, PDO::PARAM_INT);
            $stmt->bindParam(":bairro", $bairro, PDO::PARAM_STR);
            $stmt->bindParam(":cidade", $cidade, PDO::PARAM_STR);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":complemento", $complemento, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function deleteEndereco ($id) {
        try {
            $sql = "DELETE FROM endereco WHERE id = :id";

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