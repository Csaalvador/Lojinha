<?php
include 'conexao.php';

if (isset($_GET['id'])) {
    $vendaId = $_GET['id'];

    // Obtenha os detalhes da venda
    $vendaQuery = $pdo->prepare("
        SELECT v.id, v.data, v.valor_total, c.nome AS cliente_nome, GROUP_CONCAT(p.nome SEPARATOR ', ') AS produtos
        FROM vendas v
        JOIN clientes c ON v.cliente_id = c.id
        JOIN venda_produto vp ON v.id = vp.venda_id
        JOIN produtos p ON vp.produto_id = p.id
        WHERE v.id = :vendaId
        GROUP BY v.id, v.data, v.valor_total, c.nome
    ");
    $vendaQuery->bindParam(':vendaId', $vendaId, PDO::PARAM_INT);
    $vendaQuery->execute();
    $venda = $vendaQuery->fetch(PDO::FETCH_ASSOC);

    if ($venda) {
        echo '<div class="row">';
        echo '<div class="col-md-6"><strong>ID da Venda:</strong> ' . htmlspecialchars($venda['id']) . '</div>';
        echo '<div class="col-md-6"><strong>Data:</strong> ' . htmlspecialchars(date('d/m/Y', strtotime($venda['data']))) . '</div>';
        echo '</div>';
        echo '<div class="row">';
        echo '<div class="col-md-6"><strong>Cliente:</strong> ' . htmlspecialchars($venda['cliente_nome']) . '</div>';
        echo '<div class="col-md-6"><strong>Valor Total:</strong> R$' . number_format($venda['valor_total'], 2, ',', '.') . '</div>';
        echo '</div>';
        echo '<div class="row">';
        echo '<div class="col-md-12"><strong>Produtos:</strong> ' . htmlspecialchars($venda['produtos']) . '</div>';
        echo '</div>';
    } else {
        echo '<p>Detalhes da venda não encontrados.</p>';
    }
} else {
    echo '<p>ID da venda não fornecido.</p>';
}
?>
