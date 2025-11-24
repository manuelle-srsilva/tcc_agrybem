<?php
namespace Model;

use PDO;
use PDOException;

require_once __DIR__ . '/Connection.php';

class Order {
    private $db;

    public function __construct(){
        $this->db = Connection::getInstance();
    }

    /**
     * Cria um pedido e retorna o id
     */
    public function createOrder($cliente_id, $empresa_id, $total, $pickup_date = null, $pickup_time = null){
        try{
            $sql = "INSERT INTO `orders` (cliente_id, empresa_id, total, pickup_date, pickup_time, status, created_at) VALUES (:cliente_id, :empresa_id, :total, :pickup_date, :pickup_time, 'pending', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cliente_id', $cliente_id ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
            $stmt->bindValue(':total', $total);
            $stmt->bindValue(':pickup_date', $pickup_date ?: null);
            $stmt->bindValue(':pickup_time', $pickup_time ?: null);
            if($stmt->execute()){
                return $this->db->lastInsertId();
            }
            return false;
        }catch(PDOException $e){
            return false;
        }
    }

    /**
     * Insere itens do pedido
     * $items = array of ['produto_id','quantidade','unidade','preco_unitario','total_item']
     */
    public function createOrderItems($order_id, array $items){
        try{
            $sql = "INSERT INTO order_items (order_id, produto_id, quantidade, unidade, preco_unitario, total_item) VALUES (:order_id, :produto_id, :quantidade, :unidade, :preco_unitario, :total_item)";
            $stmt = $this->db->prepare($sql);
            foreach($items as $it){
                $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
                $stmt->bindValue(':produto_id', $it['produto_id'], PDO::PARAM_INT);
                $stmt->bindValue(':quantidade', $it['quantidade']);
                $stmt->bindValue(':unidade', $it['unidade']);
                $stmt->bindValue(':preco_unitario', $it['preco_unitario']);
                $stmt->bindValue(':total_item', $it['total_item']);
                $stmt->execute();
            }
            return true;
        }catch(PDOException $e){
            return false;
        }
    }

    public function getOrderItems($order_id){
        try{
            $sql = "SELECT produto_id, quantidade, unidade, preco_unitario, total_item FROM order_items WHERE order_id = :order_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            return [];
        }
    }

    public function getOrdersByCliente($cliente_id){
        try{
            $sql = "SELECT o.*, (SELECT nome FROM empreendimento e WHERE e.id = o.empresa_id LIMIT 1) AS empresa_nome FROM orders o WHERE o.cliente_id = :cliente_id ORDER BY o.pickup_date DESC, o.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cliente_id', $cliente_id, PDO::PARAM_INT);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($orders as &$ord){
                $ord['items'] = $this->getOrderItems($ord['id']);
            }
            return $orders;
        }catch(PDOException $e){
            return [];
        }
    }

    public function getOrdersByEmpresa($empresa_id){
        try{
            $sql = "SELECT o.*, c.nome AS cliente_nome, c.telefone AS cliente_telefone FROM orders o JOIN cliente c ON c.id = o.cliente_id WHERE o.empresa_id = :empresa_id ORDER BY o.pickup_date DESC, o.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($orders as &$ord){
                $ord['items'] = $this->getOrderItems($ord['id']);
            }
            return $orders;
        }catch(PDOException $e){
            return [];
        }
    }

    public function getOrderById($order_id){
        try{
            $sql = "SELECT * FROM orders WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $order_id, PDO::PARAM_INT);
            $stmt->execute();
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if($order){
                $order['items'] = $this->getOrderItems($order['id']);
            }
            return $order ?: null;
        }catch(PDOException $e){
            return null;
        }
    }

    public function updateStatus($order_id, $status){
        try{
            $sql = "UPDATE orders SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $order_id, PDO::PARAM_INT);
            return $stmt->execute();
        }catch(PDOException $e){
            return false;
        }
    }
}

?>