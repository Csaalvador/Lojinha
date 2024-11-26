<?php
// Inclua a conexão com o banco de dados e mensagens
include 'system/conexao.php';
include 'system/mensagens.php';

// Obtenha os dados dos clientes com pendências
$pendenciasQuery = $pdo->query("
    SELECT c.id, c.nome, SUM(v.valor_total) AS total_pendente
    FROM clientes c
    JOIN vendas v ON c.id = v.cliente_id
    WHERE v.pago = 0
    GROUP BY c.id, c.nome
    HAVING SUM(v.valor_total) > 0
");
$pendencias = $pendenciasQuery->fetchAll(PDO::FETCH_ASSOC);

// Ajustando a consulta SQL para incluir o venda_id
$pendenciasEncerradasQuery = $pdo->query("
    SELECT c.nome, p.data AS data, SUM(v.valor_total) AS valor_pago, v.id AS venda_id
    FROM pendencias p
    JOIN vendas v ON p.venda_id = v.id
    JOIN clientes c ON v.cliente_id = c.id
    GROUP BY c.nome, p.data, v.id
");
$pendenciasEncerradas = $pendenciasEncerradasQuery->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="pt-br" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendências | Tata Presentes</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.4/fh-4.0.1/datatables.min.css" rel="stylesheet">
    <link rel="icon" href="includes/favicon_16.png">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.4/fh-4.0.1/datatables.min.js"></script>
</head>

<style>
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

      .bi {
        vertical-align: -.125em;
        fill: currentColor;
      }

      .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
      }

      .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
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

<body>
<main class="d-flex flex-nowrap">
    <?php include 'includes/menu.php'; ?>
    <div class="b-example-divider b-example-vr"></div>
    <div class="container-fluid">
        <hr>
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Pendências de Vendas</h1>
        </div>

        <!-- Adiciona as abas -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pendencias-abertas-tab" data-bs-toggle="tab" data-bs-target="#pendencias-abertas" type="button" role="tab" aria-controls="pendencias-abertas" aria-selected="true">Pendências Abertas</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pendencias-encerradas-tab" data-bs-toggle="tab" data-bs-target="#pendencias-encerradas" type="button" role="tab" aria-controls="pendencias-encerradas" aria-selected="false">Pendências Encerradas</button>
            </li>
        </ul>

        <div class="tab-content mt-4" id="myTabContent">
            <!-- Conteúdo da aba Pendências Abertas -->
            <div class="tab-pane fade show active" id="pendencias-abertas" role="tabpanel" aria-labelledby="pendencias-abertas-tab">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTableAbertas" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nome do Cliente</th>
                                <th>Total Pendente</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendencias as $pendencia): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pendencia['nome']); ?></td>
                                    <td><?php echo number_format($pendencia['total_pendente'], 2, ',', '.'); ?></td>
                                    <td>
                                        <button class="btn btn-primary view-details" data-client-id="<?php echo htmlspecialchars($pendencia['id']); ?>">Ver Detalhes</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Conteúdo da aba Pendências Encerradas -->
            <div class="tab-pane fade" id="pendencias-encerradas" role="tabpanel" aria-labelledby="pendencias-encerradas-tab">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTableEncerradas" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nome do Cliente</th>
                                <th>Valor Pago</th>
                                <th>Data de Encerramento</th>
                                <th>Recibo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendenciasEncerradas as $pendencia): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pendencia['nome']); ?></td>
                                    <td><?php echo number_format($pendencia['valor_pago'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pendencia['data'])); ?></td>
                                    <td>
                                        <!-- Adicionar o link para imprimir o recibo passando o ID da venda -->
                                        <a href="gera_recibo.php?id=<?php echo htmlspecialchars($pendencia['venda_id']); ?>" 
                                            target="_blank" class="btn btn-success">
                                            Imprimir Recibo
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- Modal de Detalhes da Pendência -->
<div class="modal fade" id="pendenciaModal" tabindex="-1" aria-labelledby="pendenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pendenciaModalLabel">Detalhes da Pendência</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pendenciaDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes da Venda -->
<div class="modal fade" id="verVendaModal" tabindex="-1" aria-labelledby="verVendaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verVendaModalLabel">Detalhes da Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="vendaDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Configuração de tradução do DataTable
        const dataTableOptions = {
            "language": {
                "decimal": "",
                "emptyTable": "Nenhum dado disponível na tabela",
                "info": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 até 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros no total)",
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
                }
            }
        };

        // Inicializa as tabelas com a configuração de tradução
        $('#dataTableAbertas').DataTable(dataTableOptions);
        $('#dataTableEncerradas').DataTable(dataTableOptions);

        // Exibir detalhes da pendência
        $(document).on('click', '.view-details', function() {
            var clientId = $(this).data('client-id');
            $.ajax({
                url: 'system/get_pendencias.php',
                type: 'GET',
                data: { id: clientId },
                success: function(response) {
                    $('#pendenciaDetails').html(response);
                    var pendenciaModal = new bootstrap.Modal(document.getElementById('pendenciaModal'));
                    pendenciaModal.show();
                }
            });
        });

        // Exibir detalhes da venda
        $(document).on('click', '.ver-venda', function() {
            var vendaId = $(this).data('venda-id');
            $.ajax({
                url: 'system/get_venda_detalhes.php',
                type: 'GET',
                data: { id: vendaId },
                success: function(response) {
                    $('#vendaDetails').html(response);
                    var vendaModal = new bootstrap.Modal(document.getElementById('verVendaModal'));
                    vendaModal.show();
                }
            });
        });

        // Marcar venda como quitada
        $(document).on('click', '.marcar-quitado', function() {
            var vendaId = $(this).data('venda-id');
            $.ajax({
                url: 'system/marcar_quitado.php',
                type: 'POST',
                data: { id: vendaId },
                success: function(response) {
                    if (response === 'success') {
                        $('#verVendaModal').modal('hide');
                        $('body').append(`
                            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="toast-header">
                                        <strong class="me-auto">Sucesso</strong>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                    <div class="toast-body">
                                        Venda marcada como quitada com sucesso!
                                    </div>
                                </div>
                            </div>
                        `);

                        setTimeout(function() {
                            $('.toast').toast('hide');
                            location.reload();
                        }, 3000);
                    } else {
                        $('body').append(`
                            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="toast-header">
                                        <strong class="me-auto">Erro</strong>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                    <div class="toast-body">
                                        Erro ao marcar a venda como quitada.
                                    </div>
                                </div>
                            </div>
                        `);

                        setTimeout(function() {
                            $('.toast').toast('hide');
                        }, 3000);
                    }
                },
                error: function() {
                    $('body').append(`
                        <div class="toast-container position-fixed bottom-0 end-0 p-3">
                            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="toast-header">
                                    <strong class="me-auto">Erro</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                                <div class="toast-body">
                                    Erro na requisição. Tente novamente.
                                </div>
                            </div>
                        </div>
                    `);

                    setTimeout(function() {
                        $('.toast').toast('hide');
                    }, 3000);
                }
            });
        });

        // Exibe os toasts ao carregar a página (caso existam mensagens)
        <?php if (isset($_SESSION['messages']) && !empty($_SESSION['messages'])): ?>
            <?php foreach ($_SESSION['messages'] as $message): ?>
                var toastHtml = `
                    <div class="toast-container position-fixed bottom-0 end-0 p-3">
                        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header">
                                <strong class="me-auto"><?php echo ucfirst($message['type']); ?></strong>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body">
                                <?php echo htmlspecialchars($message['message']); ?>
                            </div>
                        </div>
                    </div>`;
                $('body').append(toastHtml);

                setTimeout(function() {
                    $('.toast').toast('hide');
                }, 3000);
            <?php endforeach; ?>
            <?php unset($_SESSION['messages']); ?> // Limpa as mensagens após exibir
        <?php endif; ?>
    });
</script>


</body>
</html>
