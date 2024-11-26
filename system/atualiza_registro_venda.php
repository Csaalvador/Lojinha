<?php
include 'conexao.php';
include 'mensagens.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Inicia a transação
        $pdo->beginTransaction();

        // Coleta de dados
        $venda_id = $_POST['venda_id'];
        $cliente_id = $_POST['cliente_id'];
        $data = $_POST['data'];
        $valor_total = str_replace(',', '.', $_POST['valor_total']); // Formato correto do valor
        $status_pagamento = $_POST['pago'];
        $produtos = json_decode($_POST['produtos'], true); // Verifica se o JSON foi enviado corretamente

        // Verificação do JSON de produtos
        if (is_null($produtos) || !is_array($produtos)) {
            throw new Exception('Erro ao processar os produtos. Verifique se os produtos foram corretamente selecionados.');
        }

        // Atualiza a tabela de vendas
        $stmt = $pdo->prepare("UPDATE vendas SET cliente_id = :cliente_id, data = :data, valor_total = :valor_total, pago = :pago WHERE id = :venda_id");
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':valor_total', $valor_total); // Certifica-se de que o valor está no formato correto
        $stmt->bindParam(':pago', $status_pagamento);
        $stmt->bindParam(':venda_id', $venda_id);

        // Executa a atualização da venda
        if ($stmt->execute()) {
            // Deleta os produtos antigos da venda
            $stmt_delete = $pdo->prepare("DELETE FROM venda_produto WHERE venda_id = :venda_id");
            $stmt_delete->bindParam(':venda_id', $venda_id);
            $stmt_delete->execute();

            // Prepara a inserção de novos produtos
            $stmt_produto = $pdo->prepare("INSERT INTO venda_produto (venda_id, produto_id, valor) VALUES (:venda_id, :produto_id, :valor)");

            foreach ($produtos as $produto) {
                // Verifica se o produto tem ID e valor válidos
                if (isset($produto['id']) && isset($produto['valor'])) {
                    $produto_valor = str_replace(',', '.', $produto['valor']); // Formata corretamente o valor do produto
                    $stmt_produto->bindParam(':venda_id', $venda_id);
                    $stmt_produto->bindParam(':produto_id', $produto['id']);
                    $stmt_produto->bindParam(':valor', $produto_valor); // Grava o valor formatado
                    $stmt_produto->execute();
                }
            }

            // Confirma a transação
            $pdo->commit();
            add_message('Venda atualizada com sucesso!', 'success');
        } else {
            throw new Exception('Erro ao atualizar a venda.');
        }
    } catch (Exception $e) {
        // Reverte a transação em caso de erro
        $pdo->rollBack();
        add_message($e->getMessage(), 'error');
    }

    // Redireciona para a página de vendas
    header("Location: ../vendas.php");
    exit();
}
?>
