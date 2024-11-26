<?php
include 'conexao.php';

if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Prepara a query para buscar clientes, case-insensitive
    $stmt = $pdo->prepare("SELECT id, nome FROM clientes WHERE nome LIKE :nome LIMIT 10");
    $stmt->bindValue(':nome', "%$query%");
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($clientes) {
        foreach ($clientes as $cliente) {
            // Retorna os clientes como itens de lista
            echo '<li class="list-group-item list-group-item-action" data-id="' . $cliente['id'] . '">' . htmlspecialchars($cliente['nome']) . '</li>';
        }
    } else {
        echo '<li class="list-group-item">Nenhum cliente encontrado</li>';
    }
}
?>
