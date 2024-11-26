<?php
include 'conexao.php';

if (isset($_POST['venda_id'])) {
    $venda_id = $_POST['venda_id'];

    // Atualizar o status para ativo = 0 (excluído)
    $stmt = $pdo->prepare("UPDATE vendas SET ativo = 0 WHERE id = :venda_id");
    $stmt->bindParam(':venda_id', $venda_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Venda excluída com sucesso.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir a venda.']);
    }
}
?>
