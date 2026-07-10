<?php
// db.php
$host = 'localhost';
$dbname = 'gcsp_enem';
$user = 'root'; // Usuário padrão do WampServer
$pass = '';     // Senha padrão do WampServer (vazia)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>