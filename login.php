<?php
// login.php
session_start();
require 'db.php';

$erro = '';
$sucesso = '';

// Processa o formulário de Cadastro ou Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['cadastrar'])) {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografia segura

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $email, $senha]);
            $sucesso = "Cadastro realizado! Faça login para continuar.";
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar. O email já pode estar em uso.";
        }
    } 
    elseif (isset($_POST['login'])) {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $stmt = $pdo->prepare("SELECT id, nome, senha_hash FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            header("Location: index.php"); // Redireciona para o simulador
            exit;
        } else {
            $erro = "Email ou senha incorretos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Simulador ENEM</title>
    <style>
        body { font-family: Arial; background: #f4f4f9; display: flex; justify-content: center; padding-top: 50px; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); width: 300px; margin: 10px; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { background: #003366; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Entrar</h2>
        <p style="color:red;"><?= $erro ?></p>
        <p style="color:green;"><?= $sucesso ?></p>
        <form method="POST">
            <input type="email" name="email" placeholder="Seu Email" required>
            <input type="password" name="senha" placeholder="Sua Senha" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>

    <div class="box">
        <h2>Criar Conta</h2>
        <form method="POST">
            <input type="text" name="nome" placeholder="Nome Completo" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Crie uma Senha" required>
            <button type="submit" name="cadastrar">Cadastrar</button>
        </form>
    </div>
</body>
</html>