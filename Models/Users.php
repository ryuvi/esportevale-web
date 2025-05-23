<?php
// Models/Users.php

class Users {
    private $db; 

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function checkAuth($user, $password) {
        // Prepara a query
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $this->db->prepare("SELECT id, username, email FROM users WHERE email = :email AND password = :password");

        // Executa a consulta com os parâmetros
        $stmt->execute(array('email' => $user, 'password' => $password));

        // Verifica se algum usuário foi retornado
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user : null;
    }

    public function createUser($name, $username, $email, $password) {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = :username OR email = :email');
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return false;
        }

        $stmt = $this->db->prepare('INSERT INTO users (name, username, email, password) VALUES (:name, :username, :email, :password)');
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();

        return $this->db->lastInsertId();
    }

    public function storeToken($userId, $token)
    {

        $sql = "INSERT OR REPLACE INTO user_tokens (user_id, token, created_at, expires_at) VALUES (:user_id, :token, datetime('now'), NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Validação do token
    public function validateToken($token)
    {

        $sql = "SELECT user_id FROM user_tokens WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        return $stmt->fetchColumn(); // retorna user_id ou false
    }
}