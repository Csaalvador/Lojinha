<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $venda_id = $_POST['id'];

    try {
        // Inicia uma transação
        $pdo->beginTransaction();

        // Marcar venda como paga
        $stmt = $pdo->prepare("UPDATE vendas SET pago = 1 WHERE id = :id");
        $stmt->execute(['id' => $venda_id]);

        // Verificar se a venda foi atualizada com sucesso
        if ($stmt->rowCount() == 0) {
            throw new Exception("Erro ao marcar a venda como quitada: Venda não encontrada ou já quitada.");
        }

        // Inserir na tabela pendencias como encerrada
        $stmt_pendencia = $pdo->prepare("
            INSERT INTO pendencias (venda_id, data) 
            VALUES (:venda_id, NOW())
        ");
        $stmt_pendencia->execute(['venda_id' => $venda_id]);

        // Confirma a transação
        $pdo->commit();

        echo 'success';
    } catch (Exception $e) {
        // Se houver um erro, cancela a transação
        $pdo->rollBack();

        // Envia o erro para o AJAX
        echo 'error: ' . $e->getMessage();
    }
}
