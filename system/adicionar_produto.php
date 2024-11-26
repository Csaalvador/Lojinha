<?php
include 'conexao.php';
include 'mensagens.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $valor = $_POST['valor'];

    // Prepare a consulta SQL com o campo ativo
    $stmt = $pdo->prepare("INSERT INTO produtos (nome, valor, ativo) VALUES (:nome, :valor, :ativo)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':valor', $valor);
    $ativo = 1; // Define o valor padrão para o campo ativo como 1
    $stmt->bindParam(':ativo', $ativo);

    // Executa a consulta e adiciona a mensagem apropriada
    if ($stmt->execute()) {
        add_message('Produto adicionado com sucesso!', 'success');
    } else {
        add_message('Erro ao adicionar produto.', 'danger');
    }

    // Redireciona de volta para a página de produtos
    header("Location: ../produto.php");
    exit();
}
