<?php
// Inclua a conexão com o banco de dados
include 'system/conexao.php';
include 'system/mensagens.php';
session_start();

// Obtenha os dados dos produtos (somente ativos)
$productsQuery = $pdo->query("SELECT * FROM produtos WHERE ativo = 1");
$products = $productsQuery->fetchAll(PDO::FETCH_ASSOC);

// Verifica se houve algum sucesso na adição de um produto
$produto_adicionado = isset($_SESSION['produto_adicionado']) ? $_SESSION['produto_adicionado'] : false;
unset($_SESSION['produto_adicionado']); // Limpa a variável após a verificação

if ($produto_adicionado) {
    add_message('Produto adicionado com sucesso!', 'success');
}
?>
<!doctype html>
<html lang="pt-br" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="generator" content="Lojinha 1.0">
    <title>Produtos | Tata Presentes</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.4/fh-4.0.1/datatables.min.css" rel="stylesheet">

    <!-- Favicons -->
    <link rel="icon" href="includes/favicon_16.png">
    <meta name="theme-color" content="#712cf9">

    <style>
        /* Custom styles for this template */
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .b-example-divider {
            width: 100%;
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .hide-column {
            display: none;
        }

        .btn-bd-primary {
            --bd-violet-bg: #712cf9;
            --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

            --bs-btn-font-weight: 600;
            --bs-btn-color: var(--bs-white);
            --bs-btn-bg: var(--bd-violet-bg);
            --bs-btn-border-color: var(--bd-violet-bg);
            --bs-btn-hover-color: var(--bs-white);
            --bs-btn-hover-bg: #6528e0;
            --bs-btn-hover-border-color: #6528e0;
            --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
            --bs-btn-active-color: var(--bs-btn-hover-color);
            --bs-btn-active-bg: #5a23c8;
            --bs-btn-active-border-color: #5a23c8;
        }

        .bd-mode-toggle {
            z-index: 1500;
        }

        .bd-mode-toggle .dropdown-menu .active .bi {
            display: block !important;
        }
    </style>
</head>
<body>
<main class="d-flex flex-nowrap">
    <?php include 'includes/menu.php'; ?>
    <div class="b-example-divider b-example-vr"></div>
    <div class="container-fluid">
        <hr>
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Produtos da Lojinha</h1>
            <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#adicionarProdutoModal">
                <i class="fas fa-plus fa-sm text-white-50"></i> Adicionar Produto
            </button>
        </div>

        <?php display_messages(); ?>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="hide-column">ID</th>
                            <th class="w-50">Nome</th>
                            <th class="w-50">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr class="clickable-row" data-id="<?php echo $product['id']; ?>" data-nome="<?php echo htmlspecialchars($product['nome']); ?>" data-valor="<?php echo $product['valor']; ?>">
                                <td class="hide-column"><?php echo htmlspecialchars($product['id']); ?></td>
                                <td class="w-50"><?php echo htmlspecialchars($product['nome']); ?></td>
                                <td class="w-50">R$ <?php echo number_format($product['valor'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal para Adicionar Produto -->
<div class="modal fade" id="adicionarProdutoModal" tabindex="-1" aria-labelledby="adicionarProdutoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adicionarProdutoModalLabel">Adicionar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="adicionarProdutoForm" action="system/adicionar_produto.php" method="post">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor</label>
                        <input type="number" step="0.01" class="form-control" id="valor" name="valor" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Adicionar Produto</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Produto -->
<div class="modal fade" id="editarProdutoModal" tabindex="-1" aria-labelledby="editarProdutoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarProdutoModalLabel">Editar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editarProdutoForm">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="mb-3">
                        <label for="edit-nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="edit-nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-valor" class="form-label">Valor</label>
                        <input type="number" step="0.01" class="form-control" id="edit-valor" name="valor" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="excluirProdutoBtn">Excluir</button>
                        <button type="submit" class="btn btn-primary" id="atualizarProdutoBtn">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.4/fh-4.0.1/datatables.min.js"></script>

<script>
$(document).ready(function() {
    // Configuração do DataTable
    $('#dataTable').DataTable({
        "language": {
            "decimal": "",
            "emptyTable": "Nenhum dado disponível na tabela",
            "info": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 até 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros no total)",
            "infoPostFix": "",
            "thousands": ".",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Carregando...",
            "processing": "Processando...",
            "search": "Buscar:",
            "zeroRecords": "Nenhum registro correspondente encontrado",
            "paginate": {
                "first": "Primeiro",
                "last": "Último",
                "next": "Próximo",
                "previous": "Anterior"
            },
            "aria": {
                "orderable": "Ordenar por esta coluna",
                "orderableReverse": "Ordem reversa nesta coluna"
            }
        }
    });

    // Evento para abrir o modal de edição ao clicar na linha da tabela
    $('#dataTable').on('click', '.clickable-row', function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        var valor = $(this).data('valor');

        $('#edit-id').val(id);
        $('#edit-nome').val(nome);
        $('#edit-valor').val(valor);

        var editarModal = new bootstrap.Modal(document.getElementById('editarProdutoModal'));
        editarModal.show();
    });

    // Evento para excluir o produto
    $('#excluirProdutoBtn').click(function() {
        var id = $('#edit-id').val();

        // Exibe o modal de confirmação
        var confirmar = confirm("Você realmente deseja excluir este produto?");
        if (confirmar) {
            $.ajax({
                url: 'system/processa_edicao_produto.php',
                type: 'POST',
                data: {
                    id: id,
                    acao: 'excluir'
                },
                success: function(response) {
                    if (response === 'success') {
                        window.location.reload();
                    } else {
                        alert('Erro ao excluir o produto.');
                    }
                }
            });
        }
    });

    // Evento para atualizar o produto
    $('#editarProdutoForm').submit(function(e) {
        e.preventDefault();
        var id = $('#edit-id').val();
        var nome = $('#edit-nome').val();
        var valor = $('#edit-valor').val();

        $.ajax({
            url: 'system/processa_edicao_produto.php',
            type: 'POST',
            data: {
                id: id,
                nome: nome,
                valor: valor,
                acao: 'atualizar'
            },
            success: function(response) {
                if (response === 'success') {
                    // Redireciona ou recarrega a página para que a mensagem seja exibida pelo PHP
                    window.location.reload();
                } else {
                    alert('Erro ao atualizar o produto.');
                }
            }
        });
    });
});
</script>

</body>
</html>
