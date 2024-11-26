<?php
// Inclua a conexão com o banco de dados
include 'conexao.php';

// Obtenha o ID da venda
$saleId = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Atualizar a venda para paga
$pdo->beginTransaction();
try {
    $updateSale = $pdo->prepare("UPDATE vendas SET pago = 1 WHERE id = :saleId");
    $updateSale->execute(['saleId' => $saleId]);

    // Obter o valor da venda
    $valorQuery = $pdo->prepare("SELECT valor_total FROM vendas WHERE id = :saleId");
    $valorQuery->execute(['saleId' => $saleId]);
    $valor = $valorQuery->fetchColumn();

    // Atualizar o total pendente do cliente
    $clienteQuery = $pdo->prepare("
        UPDATE clientes
        SET situacao_conta = (
            SELECT
                CASE
                    WHEN SUM(v.valor_total) > 0 THEN 'aberta'
                    ELSE 'fechada'
                END
            FROM vendas v
            WHERE v.id_cliente = (
                SELECT id_cliente FROM vendas WHERE id = :saleId
            )
            AND v.pago = 0
        )
        WHERE id = (
            SELECT id_cliente FROM vendas WHERE id = :saleId
        )
    ");
    $clienteQuery->execute(['saleId' => $saleId]);

    $pdo->commit();
    echo 'Venda paga com sucesso!';
} catch (Exception $e) {
    $pdo->rollBack();
    echo 'Erro ao pagar a venda: ' . $e->getMessage();
}
?>
