<?php
include 'conexao.php';

if (isset($_POST['nome'])) {
    $nome = '%' . $_POST['nome'] . '%';

    $stmt = $pdo->prepare("SELECT id, nome FROM clientes WHERE nome LIKE ? LIMIT 5");
    $stmt->execute([$nome]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($clientes) {
        foreach ($clientes as $cliente) {
            echo '<a href="#" class="list-group-item list-group-item-action" data-id="' . $cliente['id'] . '" data-nome="' . htmlspecialchars($cliente['nome']) . '">';
            echo htmlspecialchars($cliente['nome']);
            echo '</a>';
        }
    } else {
        echo '<a href="#" class="list-group-item list-group-item-action disabled">Nenhum cliente encontrado</a>';
    }
}
?>
