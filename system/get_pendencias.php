<?php
include 'conexao.php';

if (isset($_GET['id'])) {
    $clienteId = $_GET['id'];

    // Obtenha as vendas pendentes do cliente
    $vendasQuery = $pdo->prepare("
        SELECT v.id, v.data, v.valor_total, GROUP_CONCAT(p.nome SEPARATOR ', ') AS produtos
        FROM vendas v
        JOIN venda_produto vp ON v.id = vp.venda_id
        JOIN produtos p ON vp.produto_id = p.id
        WHERE v.cliente_id = :clienteId AND v.pago = 0
        GROUP BY v.id, v.data, v.valor_total
    ");
    $vendasQuery->bindParam(':clienteId', $clienteId, PDO::PARAM_INT);
    $vendasQuery->execute();
    $vendas = $vendasQuery->fetchAll(PDO::FETCH_ASSOC);

    if ($vendas) {
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>ID</th><th>Data</th><th>Valor Total</th><th>Ações</th></tr></thead><tbody>';

        foreach ($vendas as $venda) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($venda['id']) . '</td>';
            echo '<td>' . htmlspecialchars(date('d/m/Y', strtotime($venda['data']))) . '</td>';
            echo '<td>R$' . number_format($venda['valor_total'], 2, ',', '.') . '</td>';
            echo '<td>
                    <div class="btn-group" role="group" aria-label="Ação">
                        <button class="btn btn-info ver-venda" data-venda-id="' . htmlspecialchars($venda['id']) . '">Ver Venda</button>
                        <button class="btn btn-primary" onclick="window.open(\'gera_recibo.php?id=' . $venda['id'] . '\', \'_blank\')">Imprimir Recibo</button>
                        <button class="btn btn-success marcar-quitado" data-venda-id="' . htmlspecialchars($venda['id']) . '">Marcar como Quitado</button>
                    </div>
                  </td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>Nenhuma venda pendente encontrada para este cliente.</p>';
    }
}
?>
