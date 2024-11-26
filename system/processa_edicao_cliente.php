<?php
include 'conexao.php';
include 'mensagens.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'];
    $id = $_POST['id'];

    if ($acao === 'excluir') {
        $stmt = $pdo->prepare("UPDATE clientes SET ativo = 0 WHERE id = ?");
        if ($stmt->execute([$id])) {
            add_message('Cliente excluído com sucesso!', 'success');
            echo 'success';
        } else {
            add_message('Erro ao excluir cliente.', 'error');
            echo 'error';
        }
    } elseif ($acao === 'atualizar') {
        $nome = $_POST['nome'];
        $telefone = $_POST['telefone'];
        $endereco = $_POST['endereco'];
        $situacao_conta = $_POST['situacao_conta'];

        $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, telefone = ?, endereco = ?, situacao_conta = ? WHERE id = ?");
        if ($stmt->execute([$nome, $telefone, $endereco, $situacao_conta, $id])) {
            add_message('Cliente atualizado com sucesso!', 'success');
            echo 'success';
        } else {
            add_message('Erro ao atualizar cliente.', 'error');
            echo 'error';
        }
    }
}
?>
