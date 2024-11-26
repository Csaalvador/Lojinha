<?php
// Inclua a conexão com o banco de dados
include 'conexao.php';

if (isset($_GET['venda_id'])) {
    $venda_id = $_GET['venda_id'];

    // Consulta para obter os dados da venda
    $query = $pdo->prepare("
        SELECT v.*, c.nome AS cliente_nome, c.id AS cliente_id
        FROM vendas v
        JOIN clientes c ON v.cliente_id = c.id
        WHERE v.id = :venda_id
    ");
    $query->bindParam(':venda_id', $venda_id, PDO::PARAM_INT);
    $query->execute();
    $venda = $query->fetch(PDO::FETCH_ASSOC);

    // Consulta para obter os produtos da venda
    $produtosQuery = $pdo->prepare("
        SELECT p.nome, vp.produto_id, vp.valor
        FROM venda_produto vp
        JOIN produtos p ON vp.produto_id = p.id
        WHERE vp.venda_id = :venda_id
    ");
    $produtosQuery->bindParam(':venda_id', $venda_id, PDO::PARAM_INT);
    $produtosQuery->execute();
    $produtos = $produtosQuery->fetchAll(PDO::FETCH_ASSOC);

    // Inclua os produtos na resposta JSON
    $venda['produtos'] = $produtos;

    // Retorne os dados da venda em formato JSON
    echo json_encode($venda);
}
?>
