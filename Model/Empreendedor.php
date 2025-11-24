<?php
// CRIAR EMPREENDEDOR
// LOGIN
// OBTER NOME DO EMPREENDEDOR
// OBTER TODAS AS INFORMAÇÕES DO EMPREENDEDOR

namespace Model;

use Model\Connection;

use PDO;
use PDOException;

class Empreendedor {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function registerEmpreendedor ($nome, $email, $senha, $cnpj_cpf, $id_empreendimento) {
        try {
            $sql = 'INSERT INTO empreendedor (nome, email, senha, cnpj_cpf, id_empreendimento) VALUES (:nome, :email, :senha, :cnpj_cpf, :id_empreendimento)';

            $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":senha", $hashedPassword, PDO::PARAM_STR);
            // store CNPJ/CPF as string to preserve leading zeros
            $stmt->bindParam(":cnpj_cpf", $cnpj_cpf, PDO::PARAM_STR);
            $stmt->bindParam(":id_empreendimento", $id_empreendimento, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // CORREÇÃO: Retorna o ID do empreendedor recém-criado.
                return $this->db->lastInsertId();
            }
            return false;
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function getEmpreendedorByCNPJ_CPF($cnpj_cpf) {
        try {
            $sql = "SELECT e.id, e.email, e.senha, e.id_empreendimento, em.id_endereco FROM empreendedor e LEFT JOIN empreendimento em ON e.id_empreendimento = em.id WHERE e.cnpj_cpf = :cnpj_cpf LIMIT 1";

            $stmt = $this->db->prepare($sql);

            // treat CNPJ/CPF as string when searching
            $stmt->bindParam(":cnpj_cpf", $cnpj_cpf, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $error) {
            echo "Erro ao buscar informações do usuário: " . $error->getMessage();
        }
    }

    public function getEmpreendedorName ($id, $nome) {
        try {
            $sql = "SELECT nome FROM empreendedor WHERE id = :id AND nome = :nome";

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

    public function getEmpreendedorInfo ($id) {
        try {
            $sql = "SELECT nome, email, senha, cnpj_cpf FROM empreendedor WHERE id = :id";

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

    public function updateEmpreendedor ($id, $nome, $email, $cnpj_cpf) {
        try {
            $sql = 'UPDATE empreendedor SET nome = :nome, email = :email, cnpj_cpf = :cnpj_cpf WHERE id = :id';

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            // store CNPJ/CPF as string to preserve leading zeros
            $stmt->bindParam(":cnpj_cpf", $cnpj_cpf, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function deleteEmpreendedor ($id) {
        try {
            $sql = "DELETE FROM empreendedor WHERE id = :id";

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
        try{
            $sql = "UPDATE empreendedor SET senha = :senha WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $hashed = password_hash($senha, PASSWORD_DEFAULT);
            $stmt->bindParam(":senha", $hashed, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            return $stmt->execute();
        } catch(PDOException $error){
            echo "Erro ao atualizar senha: " . $error->getMessage();
            return false;
        }
    }
}

?>