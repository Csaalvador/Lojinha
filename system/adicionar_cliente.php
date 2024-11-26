<?php
include 'conexao.php';
include 'mensagens.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $situacao_conta = $_POST['situacao_conta'];

    // Inclua o campo 'ativo' com valor padrão de 1
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, telefone, endereco, situacao_conta, ativo) VALUES (:nome, :telefone, :endereco, :situacao_conta, 1)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':situacao_conta', $situacao_conta);

    if ($stmt->execute()) {
        add_message('Cliente adicionado com sucesso!', 'success');
    } else {
        add_message('Erro ao adicionar cliente.', 'error');
    }

    header("Location: ../clientes.php");
    exit;
}
?>
