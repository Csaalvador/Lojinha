<?php
include 'conexao.php';

$mes = $_POST['mes'];
$ano = $_POST['ano'];

// Obter as vendas com base no mês, ano e campo ativo = 1
$salesQuery = $pdo->prepare("
    SELECT v.*, c.nome AS cliente_nome
    FROM vendas v
    JOIN clientes c ON v.cliente_id = c.id
    WHERE MONTH(v.data) = :mes AND YEAR(v.data) = :ano AND v.ativo = 1
");
$salesQuery->bindParam(':mes', $mes, PDO::PARAM_INT);
$salesQuery->bindParam(':ano', $ano, PDO::PARAM_INT);
$salesQuery->execute();
$sales = $salesQuery->fetchAll(PDO::FETCH_ASSOC);

foreach ($sales as $sale) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($sale['cliente_nome']) . '</td>';
    echo '<td>' . htmlspecialchars(date('d/m/Y', strtotime($sale['data']))) . '</td>';
    echo '<td>';
    
    // Listar produtos da venda
    $productsQuery = $pdo->prepare("
        SELECT p.nome
        FROM venda_produto vp
        JOIN produtos p ON vp.produto_id = p.id
        WHERE vp.venda_id = :venda_id
    ");
    $productsQuery->bindParam(':venda_id', $sale['id'], PDO::PARAM_INT);
    $productsQuery->execute();
    $products = $productsQuery->fetchAll(PDO::FETCH_COLUMN);
    
    echo htmlspecialchars(implode(", ", $products));
    echo '</td>';
    echo '<td>' . number_format($sale['valor_total'], 2, ',', '.') . '</td>';
    echo '<td>' . ($sale['pago'] ? 'Sim' : 'Não') . '</td>';
    
    // Botões Editar e Excluir com btn-group para alinhamento correto
    echo '<td>

            <div class="btn-group" role="group" aria-label="Ações">
                <button class="btn btn-primary btn-sm btn-editar col-sm-6" data-id="' . $sale['id'] . '">Editar</button>
                <button class="btn btn-danger btn-sm btn-excluir col-sm-6" data-id="' . $sale['id'] . '">Excluir</button>
            </div>
    </td>';
    echo '</tr>';
}
?>
