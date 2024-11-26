<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $valor = $_POST['valor'];

    $stmt = $pdo->prepare("INSERT INTO produtos (nome, valor, ativo) VALUES (?, ?, 1)");
    if ($stmt->execute([$nome, $valor])) {
        $_SESSION['produto_adicionado'] = true;
        header('Location: ../produtos.php'); // Redireciona para a página de produtos
    } else {
        echo 'error';
    }
}
