<?php
include 'conexao.php';
include 'mensagens.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Iniciar a transação
        $pdo->beginTransaction();

        // Coleta de dados do formulário
        $cliente_id = $_POST['cliente_id'];
        $data = $_POST['data'];
        $valor_total = str_replace(',', '.', $_POST['valor_total']); // Garantir que o valor está no formato correto
        $status_pagamento = $_POST['pago'];
        $produtos = json_decode($_POST['produtos'], true); // Decodifica os produtos enviados via JSON

        // Verificar se o valor total é válido
        if (empty($valor_total) || $valor_total <= 0) {
            throw new Exception('O valor total da venda não foi calculado corretamente.');
        }

        // Inserir a venda na tabela 'vendas' e definir 'ativo' como 1
        $stmt = $pdo->prepare("
            INSERT INTO vendas (cliente_id, data, valor_total, pago, ativo) 
            VALUES (:cliente_id, :data, :valor_total, :pago, 1)
        ");
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->bindParam(':pago', $status_pagamento);

        if ($stmt->execute()) {
            // Pegar o ID da venda inserida
            $venda_id = $pdo->lastInsertId();

            // Inserir os produtos vinculados à venda
            $stmt_produto = $pdo->prepare("
                INSERT INTO venda_produto (venda_id, produto_id, valor) 
                VALUES (:venda_id, :produto_id, :valor)
            ");

            foreach ($produtos as $produto) {
                $produto_valor = str_replace(',', '.', $produto['valor']); // Formatar corretamente o valor do produto
                $stmt_produto->bindParam(':venda_id', $venda_id);
                $stmt_produto->bindParam(':produto_id', $produto['id']);
                $stmt_produto->bindParam(':valor', $produto_valor);
                $stmt_produto->execute();
            }

            // Confirmar a transação
            $pdo->commit();
            add_message('Venda registrada com sucesso!', 'success');
        } else {
            throw new Exception('Erro ao registrar a venda.');
        }
    } catch (Exception $e) {
        // Reverter a transação em caso de erro
        $pdo->rollBack();
        add_message($e->getMessage(), 'danger');
    }

    // Redirecionar para a página de vendas
    header("Location: ../vendas.php");
    exit();
}
