<?php
include 'conexao.php';
include 'mensagens.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'];
    $id = $_POST['id'];

    if ($acao === 'excluir') {
        $stmt = $pdo->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?");
        if ($stmt->execute([$id])) {
            add_message('Produto excluído com sucesso!', 'success');
            echo 'success';
        } else {
            add_message('Erro ao excluir produto.', 'danger');
            echo 'error';
        }
    } elseif ($acao === 'atualizar') {
        $nome = $_POST['nome'];
        $valor = $_POST['valor'];
        
        $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, valor = ? WHERE id = ?");
        if ($stmt->execute([$nome, $valor, $id])) {
            add_message('Produto atualizado com sucesso!', 'success');
            echo 'success';
        } else {
            add_message('Erro ao atualizar produto.', 'danger');
            echo 'error';
        }
    }
}
