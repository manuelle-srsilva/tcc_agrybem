<?php
// CRIAR CLIENTE
// LOGIN
// OBTER NOME DO CLIENTE
// OBTER TODAS AS INFORMAÇÕES DO CLIENTE

namespace Model;

use Model\Connection;

use PDO;
use PDOException;

class Cliente {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function registerClient ($nome, $email, $telefone, $senha) {
        try {
            $sql = 'INSERT INTO cliente (nome, email, telefone, senha) VALUES (:nome, :email, :telefone, :senha)';

            $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":telefone", $telefone, PDO::PARAM_STR);
            $stmt->bindParam(":senha", $hashedPassword, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function getClientByEmail($email) {
        try {
            $sql = "SELECT * FROM cliente WHERE email = :email LIMIT 1";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":email", $email, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $error) {
            echo "Erro ao buscar informações do usuário: " . $error->getMessage();
        }
    }

    public function getClienteName ($id, $nome) {
        try {
            $sql = "SELECT nome FROM cliente WHERE id = :id AND nome = :nome";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            echo "Erro ao buscar informações: " . $error->getMessage();
            return false;
        }
    }

    public function getClienteInfo ($id, $nome, $email, $telefone, $senha) {
        try {
            $sql = "SELECT nome, email, telefone, senha FROM cliente WHERE id = :id AND nome = :nome AND email = :email AND telefone = :telefone AND senha = :senha";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":telefone", $telefone, PDO::PARAM_STR);
            $stmt->bindParam(":senha", $senha, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            echo "Erro ao buscar informações: " . $error->getMessage();
            return false;
        }
    }

    public function updateClient ($id, $nome, $email, $telefone) {
        try {
            $sql = 'UPDATE cliente SET nome = :nome, email = :email, telefone = :telefone WHERE id = :id';

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":telefone", $telefone, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function deleteCliente ($id) {
        try {
            $sql = "DELETE FROM cliente WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function updatePasswordByEmail($email, $senha){
        try {
            $sql = 'UPDATE cliente SET senha = :senha WHERE email = :email';
            $hashed = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':senha', $hashed, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            return $stmt->execute();
        } catch(PDOException $e){
            echo "Erro ao atualizar senha: " . $e->getMessage();
            return false;
        }
    }
}

?>