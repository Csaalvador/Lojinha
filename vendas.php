<?php
// Inclua a conexão com o banco de dados
include 'system/conexao.php';
include 'system/mensagens.php';
session_start();

// Obtenha o ano e o mês atuais
$currentYear = date('Y');
$currentMonth = date('m');

// Obtenha os dados para o filtro do ano
$yearsQuery = $pdo->query("SELECT DISTINCT YEAR(data) AS year FROM vendas ORDER BY year DESC");
$years = $yearsQuery->fetchAll(PDO::FETCH_COLUMN, 0);

// Defina o ano mínimo do sistema
$startYear = 2024;

// Se a lista de anos estiver vazia, use apenas o ano de início
if (empty($years)) {
    $years = [$startYear];
} else {
    // Adicione o ano atual e os anos existentes
    $years = array_merge([$startYear], $years);
    // Remova duplicatas e ordene os anos
    $years = array_unique($years);
    sort($years);
}

// Mapeamento dos meses em português
$months = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

// Obter a lista de produtos ativos para os modais de registro e edição de vendas
$productsQuery = $pdo->query("SELECT id, nome, valor FROM produtos WHERE ativo = 1");
$products = $productsQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-br" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="generator" content="Lojinha 1.0">
    <title>Vendas | Tata Presentes</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.4/datatables.min.css" rel="stylesheet">
    <link rel="icon" href="includes/favicon_16.png">
    <meta name="theme-color" content="#712cf9">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.4/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        .bd-placeholder-img { font-size: 1.125rem; text-anchor: middle; user-select: none; }
        @media (min-width: 768px) { .bd-placeholder-img-lg { font-size: 3.5rem; } }
        .b-example-divider { width: 100%; height: 3rem; background-color: rgba(0, 0, 0, .1); }
        .b-example-vr { flex-shrink: 0; width: 1.5rem; height: 100vh; }
    </style>
</head>
<body>

<!-- Exibe as mensagens -->
<?php display_messages(); ?>

<main class="d-flex flex-nowrap">
    <?php include 'includes/menu.php'; ?>
    <div class="b-example-divider b-example-vr"></div>
    <div class="container-fluid">
        <hr>
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Vendas da Lojinha</h1>
            <div class="d-flex justify-content-center align-items-center">
                <div class="me-3">
                    <p class="mb-0">Selecione o Mês:</p>
                    <select class="form-select" id="mes">
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>" <?php echo $num == $currentMonth ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <p class="mb-0">Selecione o Ano:</p>
                    <select class="form-select" id="ano">
                        <?php foreach ($years as $yearOption): ?>
                            <option value="<?php echo $yearOption; ?>" <?php echo $yearOption == $currentYear ? 'selected' : ''; ?>>
                                <?php echo $yearOption; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" id="aplicarFiltros" class="btn btn-info ms-4 mt-3">Aplicar</button>
            </div>
            <button class="d-none d-sm-inline-block btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#registrarVendaModal">
                <i class="fas fa-download fa-sm text-white-50"></i> Registrar Venda
            </button>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Produtos</th>
                            <th>Total</th>
                            <th>Pago</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dados das vendas serão carregados aqui via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal para Registrar Venda -->
<div class="modal fade" id="registrarVendaModal" tabindex="-1" aria-labelledby="registrarVendaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="registrarVendaForm" action="system/processa_registro_venda.php" method="post">
                    <div class="mb-3">
                        <label for="cliente" class="form-label">Nome do Cliente</label>
                        <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Digite o nome do cliente" autocomplete="off">
                        <input type="hidden" id="cliente_id" name="cliente_id">
                        <div id="clienteSugestoes" class="list-group mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="data" class="form-label">Data</label>
                        <input type="date" class="form-control" id="data" name="data" required>
                    </div>
                    <div class="mb-3">
                        <label for="produtos" class="form-label">Produtos</label>
                        <div id="produtosSelecionados" class="mb-2"></div>
                        <select class="form-select" id="produtos">
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>" data-nome="<?php echo htmlspecialchars($product['nome']); ?>" data-valor="<?php echo $product['valor']; ?>">
                                    <?php echo htmlspecialchars($product['nome']); ?> - R$<?php echo number_format($product['valor'], 2, ',', '.'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="adicionarProdutoBtn" class="btn btn-sm btn-primary mt-2">Adicionar Produto</button>
                    </div>
                    <div class="mb-3">
                        <label for="pago" class="form-label">Status de Pagamento</label>
                        <select class="form-select" id="pago" name="pago" required>
                            <option value="1">Pago</option>
                            <option value="0">Pendente</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="valor_total" class="form-label">Valor Total</label>
                        <input type="text" class="form-control" id="valor_total" name="valor_total" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrar Venda</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Venda -->
<div class="modal fade" id="editarVendaModal" tabindex="-1" aria-labelledby="editarVendaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editarVendaForm" action="system/atualiza_registro_venda.php" method="post">
                    <input type="hidden" id="edit_venda_id" name="venda_id">
                    <div class="mb-3">
                        <label for="edit_cliente" class="form-label">Nome do Cliente</label>
                        <input type="text" class="form-control" id="edit_cliente" name="cliente" autocomplete="off">
                        <input type="hidden" id="edit_cliente_id" name="cliente_id">
                    </div>
                    <div class="mb-3">
                        <label for="edit_data" class="form-label">Data</label>
                        <input type="date" class="form-control" id="edit_data" name="data" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_produtos" class="form-label">Produtos</label>
                        <div id="edit_produtosSelecionados" class="mb-2"></div>
                        <select class="form-select" id="edit_produtos">
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>" data-nome="<?php echo htmlspecialchars($product['nome']); ?>" data-valor="<?php echo $product['valor']; ?>">
                                    <?php echo htmlspecialchars($product['nome']); ?> - R$<?php echo number_format($product['valor'], 2, ',', '.'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="edit_adicionarProdutoBtn" class="btn btn-sm btn-primary mt-2">Adicionar Produto</button>
                    </div>
                    <div class="mb-3">
                        <label for="edit_valor_total" class="form-label">Valor Total</label>
                        <input type="text" class="form-control" id="edit_valor_total" name="valor_total" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Atualizar Venda</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let table;

// Função para atualizar filtros de mês e ano
function updateFilters(month, year) {
    console.log("Aplicando filtros para mês:", month, " e ano:", year);
    
    // Destrói a instância atual da tabela se já existir
    if (table) {
        console.log('Destruindo DataTable anterior...');
        table.clear().destroy(); // Limpa e destrói a instância da tabela
    }

    // Chama o AJAX para obter os dados filtrados
    $.ajax({
        url: 'system/obter_vendas.php',
        method: 'POST',
        data: { mes: month, ano: year },
        success: function(response) {
            console.log('Resposta recebida com sucesso:', response);
            
            // Insere os novos dados recebidos no corpo da tabela
            $('#dataTable tbody').html(response);

            // Recria a DataTable com os novos dados
            table = $('#dataTable').DataTable({
                "language": {
                    "decimal": "",
                    "emptyTable": "Nenhum dado disponível na tabela",
                    "info": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros no total)",
                    "thousands": ".",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "loadingRecords": "Carregando...",
                    "search": "Buscar:",
                    "zeroRecords": "Nenhum registro correspondente encontrado",
                    "paginate": { "first": "Primeiro", "last": "Último", "next": "Próximo", "previous": "Anterior" }
                }
            });

            // Certifique-se de que os botões de editar/excluir estão sendo ativados novamente após a atualização
            activateActionButtons();
        },
        error: function() {
            toastr.error('Erro ao buscar vendas.');
        }
    });
}

// Função para ativar os botões de ação (Editar/Excluir) após a atualização da tabela
function activateActionButtons() {
    $('.btn-editar').on('click', function() {
        let vendaId = $(this).data('id');
        carregarDadosVenda(vendaId);
    });

    $('.btn-excluir').on('click', function() {
        let vendaId = $(this).data('id');
        excluirVenda(vendaId, $('#mes').val(), $('#ano').val());
    });
}

$('#cliente').on('input', function() {
    var nomeCliente = $(this).val();

    if (nomeCliente.length >= 2) {
        $.ajax({
            url: 'system/buscar_cliente.php', // Rota onde o cliente será buscado
            method: 'POST',
            data: { nome: nomeCliente },
            success: function(response) {
                $('#clienteSugestoes').html(response);
            },
            error: function() {
                toastr.error('Erro ao buscar cliente.');
            }
        });
    }
});

$(document).on('click', '.list-group-item', function() {
    var clienteNome = $(this).data('nome');
    var clienteId = $(this).data('id');
    $('#cliente').val(clienteNome);
    $('#cliente_id').val(clienteId);
    $('#clienteSugestoes').html(''); // Limpa a lista de sugestões
});

// Função para carregar dados da venda no modal de edição
function carregarDadosVenda(vendaId) {
    console.log("Carregando dados para venda ID:", vendaId);
    $.ajax({
        url: 'system/obter_dados_venda.php',
        method: 'GET',
        data: { venda_id: vendaId },
        beforeSend: function() {
            console.log("Enviando requisição AJAX para obter dados da venda...");
        },
        success: function(response) {
            console.log("Dados da venda recebidos:", response);
            let venda = JSON.parse(response);
            $('#edit_venda_id').val(venda.id);
            $('#edit_cliente').val(venda.cliente_nome);
            $('#edit_cliente_id').val(venda.cliente_id);
            $('#edit_data').val(venda.data);
            $('#edit_valor_total').val(venda.valor_total);
            $('#edit_produtosSelecionados').html('');

            venda.produtos.forEach(function(produto) {
                console.log("Produto adicionado ao modal de edição:", produto);
                $('#edit_produtosSelecionados').append(`
                    <div class="card mt-2" style="width: 18rem;">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${produto.nome} - R$${parseFloat(produto.valor).toFixed(2)}</span>
                                <a href="#" class="remover-produto btn btn-danger" data-produto-id="${produto.produto_id}" data-produto-valor="${produto.valor}">x</a>
                            </li>
                        </ul>
                    </div>
                `);
            });

            // Calcula o valor total dos produtos já carregados
            calcularValorTotalEdicao();
            $('#editarVendaModal').modal('show');
        },
        error: function(error) {
            console.log("Erro ao carregar dados da venda:", error);
            toastr.error('Erro ao carregar dados da venda.');
        }
    });
}

// Função para excluir venda
function excluirVenda(vendaId, month, year) {
    if (confirm('Tem certeza de que deseja excluir esta venda?')) {
        console.log("Excluindo venda ID:", vendaId);
        $.ajax({
            url: 'system/excluir_venda.php',
            method: 'POST',
            data: { venda_id: vendaId },
            beforeSend: function() {
                console.log("Enviando requisição AJAX para excluir venda...");
            },
            success: function(response) {
                const res = JSON.parse(response);
                console.log("Resposta da exclusão da venda:", res);
                if (res.status === 'success') {
                    toastr.success(res.message);
                    updateFilters(month, year);  // Atualiza a tabela após exclusão
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(error) {
                console.log("Erro ao tentar excluir a venda:", error);
                toastr.error('Erro ao tentar excluir a venda.');
            }
        });
    }
}

// Função para calcular o valor total no modal de edição
function calcularValorTotalEdicao() {
    let valorTotal = 0;
    $('#edit_produtosSelecionados .remover-produto').each(function() {
        let valorProduto = parseFloat($(this).data('produto-valor'));
        valorTotal += valorProduto;
    });
    console.log("Valor total calculado no modal de edição:", valorTotal);
    $('#edit_valor_total').val(valorTotal.toFixed(2)); // Atualiza o campo de valor total
}

// Adicionar e remover produtos no modal de edição
$('#edit_adicionarProdutoBtn').on('click', function() {
    let produtoSelect = $('#edit_produtos');
    let produtoId = produtoSelect.val();
    let produtoNome = produtoSelect.find(':selected').data('nome');
    let produtoValor = parseFloat(produtoSelect.find(':selected').data('valor'));

    if (produtoId) {
        console.log("Adicionando produto ao modal de edição:", {produtoId, produtoNome, produtoValor});
        $('#edit_produtosSelecionados').append(`
            <div class="card mt-2" style="width: 18rem;">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${produtoNome} - R$${produtoValor.toFixed(2)}</span>
                        <a href="#" class="remover-produto btn btn-danger" data-produto-id="${produtoId}" data-produto-valor="${produtoValor}">x</a>
                    </li>
                </ul>
            </div>
        `);
        produtoSelect.val('');
        calcularValorTotalEdicao();  // Atualiza o valor total
    }
});

// Remover produto no modal de edição
$('#edit_produtosSelecionados').on('click', '.remover-produto', function(e) {
    e.preventDefault();
    console.log("Removendo produto do modal de edição:", $(this).data());
    $(this).closest('.card').remove();
    calcularValorTotalEdicao();  // Atualiza o valor total
});

// Antes de enviar o formulário de edição
$('#editarVendaForm').on('submit', function(e) {
    e.preventDefault();

    // Monta um array de produtos
    let produtos = [];
    $('#edit_produtosSelecionados .remover-produto').each(function() {
        let produtoId = $(this).data('produto-id');
        let produtoValor = $(this).data('produto-valor');
        produtos.push({ id: produtoId, valor: produtoValor });
    });

    // Converte o array de produtos para JSON e adiciona ao campo hidden
    let produtosJson = JSON.stringify(produtos);
    $('<input>').attr({
        type: 'hidden',
        name: 'produtos',
        value: produtosJson
    }).appendTo('#editarVendaForm');

    // Envia o formulário
    this.submit();
});

// Função para adicionar produto no modal de registro
$('#adicionarProdutoBtn').on('click', function() {
    let produtoSelect = $('#produtos');
    let produtoId = produtoSelect.val();
    let produtoNome = produtoSelect.find(':selected').data('nome');
    let produtoValor = parseFloat(produtoSelect.find(':selected').data('valor'));

    if (produtoId) {
        console.log("Adicionando produto no registro de venda:", {produtoId, produtoNome, produtoValor});
        $('#produtosSelecionados').append(`
            <div class="card mt-2" style="width: 18rem;">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${produtoNome} - R$${produtoValor.toFixed(2)}</span>
                        <a href="#" class="remover-produto btn btn-danger" data-produto-id="${produtoId}" data-produto-valor="${produtoValor}">x</a>
                    </li>
                </ul>
            </div>
        `);
        produtoSelect.val('');
        calcularValorTotalRegistro();  // Atualiza o valor total do registro
    }
});

// Remover produto no modal de registro
$('#produtosSelecionados').on('click', '.remover-produto', function(e) {
    e.preventDefault();
    console.log("Removendo produto do registro de venda:", $(this).data());
    $(this).closest('.card').remove();
    calcularValorTotalRegistro();  // Atualiza o valor total do registro
});

// Função para calcular o valor total no modal de registro
function calcularValorTotalRegistro() {
    let valorTotal = 0;
    $('#produtosSelecionados .remover-produto').each(function() {
        let valorProduto = parseFloat($(this).data('produto-valor'));
        valorTotal += valorProduto;
    });
    console.log("Valor total calculado no registro de venda:", valorTotal);
    $('#valor_total').val(valorTotal.toFixed(2)); // Atualiza o campo de valor total
}

// Função para enviar os produtos no formulário de registro
$('#registrarVendaForm').on('submit', function(e) {
    e.preventDefault();

    let valorTotal = $('#valor_total').val();
    console.log("Enviando valor total:", valorTotal);

    // Monta um array de produtos
    let produtos = [];
    $('#produtosSelecionados .remover-produto').each(function() {
        let produtoId = $(this).data('produto-id');
        let produtoValor = $(this).data('produto-valor');
        produtos.push({ id: produtoId, valor: produtoValor });
    });

    // Converte o array de produtos para JSON e adiciona ao campo hidden
    let produtosJson = JSON.stringify(produtos);
    $('<input>').attr({
        type: 'hidden',
        name: 'produtos',
        value: produtosJson
    }).appendTo('#registrarVendaForm');

    // Envia o formulário
    this.submit();
});


// Ao carregar a página
$(document).ready(function() {
    const currentMonth = new Date().getMonth() + 1;
    const currentYear = new Date().getFullYear();
    console.log("Iniciando com o mês:", currentMonth, " e ano:", currentYear);
    
    updateFilters(currentMonth, currentYear); // Inicializa com o mês e ano atuais

    // Ação do botão "Aplicar"
    $('#aplicarFiltros').on('click', function() {
        const month = $('#mes').val();
        const year = $('#ano').val();
        console.log(`Botão aplicar filtros clicado. Mês selecionado: ${month}  Ano selecionado: ${year}`);
        updateFilters(month, year);  // Aplica os filtros com os valores selecionados
    });
});
</script>


</body>
</html>
