<?php
include 'system/conexao.php';

// Definir o idioma para Português do Brasil
setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

// Obtém o total de vendas mensais em R$ (últimos 12 meses)
$vendasMensaisQuery = $pdo->prepare("
    SELECT DATE_FORMAT(data, '%Y-%m') AS mes_formatado, SUM(valor_total) AS total_vendas
    FROM vendas
    WHERE data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY mes_formatado
    ORDER BY mes_formatado
");
$vendasMensaisQuery->execute();
$vendasMensais = $vendasMensaisQuery->fetchAll(PDO::FETCH_ASSOC);

// Obtém os 5 produtos mais vendidos no mês atual
$produtosVendidosQuery = $pdo->prepare("
    SELECT p.nome, COUNT(vp.produto_id) AS total
    FROM venda_produto vp
    JOIN produtos p ON vp.produto_id = p.id
    JOIN vendas v ON vp.venda_id = v.id
    WHERE MONTH(v.data) = MONTH(CURDATE()) AND YEAR(v.data) = YEAR(CURDATE())
    GROUP BY p.nome
    ORDER BY total DESC
    LIMIT 5
");
$produtosVendidosQuery->execute();
$produtosMaisVendidos = $produtosVendidosQuery->fetchAll(PDO::FETCH_ASSOC);

// Busca o total de clientes
$totalClientesQuery = $pdo->query("SELECT COUNT(*) AS total FROM clientes");
$totalClientes = $totalClientesQuery->fetch(PDO::FETCH_ASSOC)['total'];

// Busca o total de vendas no mês atual
$totalVendasMesQuery = $pdo->query("
    SELECT COUNT(*) AS total 
    FROM vendas 
    WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())
");
$totalVendasMes = $totalVendasMesQuery->fetch(PDO::FETCH_ASSOC)['total'];

// Busca o total de pendências (vendas não pagas)
$totalPendenciasQuery = $pdo->query("SELECT COUNT(*) AS total FROM vendas WHERE pago = 0");
$totalPendencias = $totalPendenciasQuery->fetch(PDO::FETCH_ASSOC)['total'];

// Busca o total de produtos
$totalProdutosQuery = $pdo->query("SELECT COUNT(*) AS total FROM produtos");
$totalProdutos = $totalProdutosQuery->fetch(PDO::FETCH_ASSOC)['total'];

// Pendências Abertas nos últimos 12 meses
$pendenciasAbertasQuery = $pdo->prepare("
    SELECT DATE_FORMAT(data, '%Y-%m') AS mes_formatado, COUNT(*) AS total_pendencias
    FROM vendas
    WHERE pago = 0 AND data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY mes_formatado
    ORDER BY mes_formatado
");
$pendenciasAbertasQuery->execute();
$pendenciasAbertasMensais = $pendenciasAbertasQuery->fetchAll(PDO::FETCH_ASSOC);

// Pendências Encerradas nos últimos 12 meses, com base na data de encerramento
$pendenciasEncerradasQuery = $pdo->prepare("
    SELECT DATE_FORMAT(p.data, '%Y-%m') AS mes_formatado, COUNT(*) AS total_pendencias_encerradas
    FROM pendencias p
    JOIN vendas v ON p.venda_id = v.id
    WHERE p.data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY mes_formatado
    ORDER BY mes_formatado
");
$pendenciasEncerradasQuery->execute();
$pendenciasEncerradasMensais = $pendenciasEncerradasQuery->fetchAll(PDO::FETCH_ASSOC);

// Produtos mais vendidos nos últimos 12 meses (top 10) com base nos meses e produtos
$produtosVendidosMesesQuery = $pdo->prepare("
    SELECT p.nome, DATE_FORMAT(v.data, '%Y-%m') AS mes_formatado, COUNT(vp.produto_id) AS total_vendido
    FROM venda_produto vp
    JOIN produtos p ON vp.produto_id = p.id
    JOIN vendas v ON vp.venda_id = v.id
    WHERE v.data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY p.nome, mes_formatado
    ORDER BY total_vendido DESC
    LIMIT 10
");
$produtosVendidosMesesQuery->execute();
$produtosMaisVendidosMeses = $produtosVendidosMesesQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-br" data-bs-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="generator" content="Lojinha 1.0">
    <title>Página Inicial | Tata Presentes</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" href="includes/favicon_16.png">
    <meta name="theme-color" content="#712cf9">

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
        background-color: rgba(0, 0, 0, .1);
        border: solid rgba(0, 0, 0, .15);
        border-width: 1px 0;
        box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
      }

      .b-example-vr {
          top: 0; /* Alinha no topo */
          bottom: 0; /* Extende até o final */
          width: 1.5rem; /* Largura definida */
          background-color: rgba(0, 0, 0, .1); /* Cor do divider */
          border-right: solid rgba(0, 0, 0, .15); /* Borda para separação visual */
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

      .card-statistics {
          max-width: 18rem;
          height: 150px; /* Definir uma altura fixa */
      }

      .card-statistics .card-body img {
          width: 48px;
          height: 48px;
          position: absolute;
          bottom: 10px;
          right: 10px;
      }


      #vendasMensaisChart, #produtosMaisVendidosChart {
            height: 300px;
            width: 100%;
        }

        /* Ajuste para o gráfico de pizza */
        .pie-chart {
            max-width: 100%;
            max-height: 300px;
            margin: 0 auto;
        }

        /* Ajuste para o card conter o conteúdo do gráfico corretamente */
        .card-grafico {
            min-height: 400px; /* Garante que o card não fique muito pequeno */
        }
    </style>

  </head>
  <body>
    <main class="d-flex flex-nowrap">
      <?php include 'includes/menu.php'; ?>
  
      <div class="b-example-divider b-example-vr"></div>
    
      <div class="container-fluid">

      <div class="container-fluid mt-4"> <!-- Adicionado o espaçamento superior -->
        <div class="row">
            <div class="col-sm-3 mb-3 mb-sm-0">
                <div class="card text-bg-primary mb-3 card-statistics" style="max-width: 18rem; position: relative;">
                    <div class="card-header">
                        <!-- Exibe o total de clientes dinâmico -->
                        <span style="font-size: 1.5rem;"><?php echo $totalClientes; ?></span>
                    </div>
                    <div class="card-body">
                        <!-- Imagem posicionada no canto direito e ampliada -->
                        <img src="includes/cliente_dashboard.png" class="rounded float-end" alt="..." style="width: 64px; height: 64px;">
                        <p class="card-text">Total Clientes</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card text-bg-secondary mb-3 card-statistics" style="max-width: 18rem; position: relative;">
                    <div class="card-header">
                        <!-- Exibe o total de vendas no mês dinâmico -->
                        <span style="font-size: 1.5rem;"><?php echo $totalVendasMes; ?></span>
                    </div>
                    <div class="card-body">
                        <img src="includes/venda_dashboard.png" class="rounded float-end" alt="..." style="width: 64px; height: 64px;">
                        <p class="card-text">Vendas no Mês</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card text-bg-danger mb-3 card-statistics" style="max-width: 18rem; position: relative;">
                    <div class="card-header">
                        <!-- Exibe o total de pendências dinâmico -->
                        <span style="font-size: 1.5rem;"><?php echo $totalPendencias; ?></span>
                    </div>
                    <div class="card-body">
                        <img src="includes/pendencia_dashboard.png" class="rounded float-end" alt="..." style="width: 64px; height: 64px;">
                        <p class="card-text">Total de Pendências</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card text-bg-success mb-3 card-statistics" style="max-width: 18rem; position: relative;">
                    <div class="card-header">
                        <!-- Exibe o total de produtos dinâmico -->
                        <span style="font-size: 1.5rem;"><?php echo $totalProdutos; ?></span>
                    </div>
                    <div class="card-body">
                        <img src="includes/produto_dashboard.png" class="rounded float-end" alt="..." style="width: 64px; height: 64px;">
                        <p class="card-text">Total de Produtos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Gráficos -->
        <div class="row">
          <div class="col-sm-6 mb-3">
            <div class="card mt-4 card-grafico">
              <div class="card-header">Vendas Mensais nos últimos 12 Meses</div>
              <div class="card-body">
                <p class="card-text"><canvas id="vendasMensaisChart"></canvas></p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="card mt-4 card-grafico">
              <div class="card-header">Produtos mais Vendidos no Mês</div>
              <div class="card-body pie-chart">
                <p class="card-text"><canvas id="produtosMaisVendidosChart"></canvas></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Pendências e Produtos Mais Vendidos nos últimos 12 meses -->
        <div class="row">
          <div class="col-sm-6 mb-3">
            <div class="card mt-4 card-grafico">
              <div class="card-header">Pendências nos últimos 12 Meses</div>
              <div class="card-body">
                <p class="card-text"><canvas id="PendenciasChart"></canvas></p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="card mt-4 card-grafico">
              <div class="card-header">Produtos mais Vendidos nos últimos 12 Meses</div>
              <div class="card-body">
                <p class="card-text"><canvas id="produtosMaisVendidosMesesChart"></canvas></p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    // Função para traduzir os meses numéricos para português
    function traduzirMes(mes) {
        const meses = {
            "01": "Janeiro",
            "02": "Fevereiro",
            "03": "Março",
            "04": "Abril",
            "05": "Maio",
            "06": "Junho",
            "07": "Julho",
            "08": "Agosto",
            "09": "Setembro",
            "10": "Outubro",
            "11": "Novembro",
            "12": "Dezembro"
        };
        const [ano, numeroMes] = mes.split("-");
        return meses[numeroMes];
    }

    // Dados injetados via PHP para Vendas Mensais
    const vendasMensaisLabels = <?php echo json_encode(array_column($vendasMensais, 'mes_formatado')); ?>;
    const vendasMensaisData = <?php echo json_encode(array_column($vendasMensais, 'total_vendas')); ?>;
    const vendasMensaisLabelsTraduzidos = vendasMensaisLabels.map(mes => traduzirMes(mes));

    // Gráfico de Vendas Mensais
    const configVendasMensais = {
        type: 'line',
        data: {
            labels: vendasMensaisLabelsTraduzidos,
            datasets: [{
                label: 'Total em R$',
                data: vendasMensaisData,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Total de Vendas Mensais' }
            },
            scales: {
                y: { ticks: { callback: value => 'R$ ' + value.toFixed(2).replace('.', ',') } }
            }
        }
    };

    // Gráfico de Produtos mais Vendidos no Mês (Pie Chart)
    const produtosMaisVendidosLabels = <?php echo json_encode(array_column($produtosMaisVendidos, 'nome')); ?>;
    const produtosMaisVendidosData = <?php echo json_encode(array_column($produtosMaisVendidos, 'total')); ?>;

    const configProdutosMaisVendidos = {
        type: 'pie',
        data: {
            labels: produtosMaisVendidosLabels,
            datasets: [{
                label: 'Quantidade',
                data: produtosMaisVendidosData,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Permite redimensionamento
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Produtos Mais Vendidos' }
            }
        }
    };

          // Dados injetados via PHP para Pendências Abertas e Encerradas
          const pendenciasAbertasMensais = <?php echo json_encode(array_column($pendenciasAbertasMensais, 'total_pendencias', 'mes_formatado')); ?>;
      const pendenciasEncerradasMensais = <?php echo json_encode(array_column($pendenciasEncerradasMensais, 'total_pendencias_encerradas', 'mes_formatado')); ?>;

      // Meses de referência
      const todosMeses = [...new Set([...Object.keys(pendenciasAbertasMensais), ...Object.keys(pendenciasEncerradasMensais)])].sort();

      // Traduzindo os meses
      const mesesPendencias = todosMeses.map(traduzirMes);

      // Alinhar os dados de pendências abertas e encerradas aos meses
      const dadosPendenciasAbertas = todosMeses.map(mes => pendenciasAbertasMensais[mes] || 0);
      const dadosPendenciasEncerradas = todosMeses.map(mes => pendenciasEncerradasMensais[mes] || 0);

      // Gráfico de Pendências Abertas e Encerradas nos últimos 12 meses
      const configPendencias = {
          type: 'line',
          data: {
              labels: mesesPendencias,
              datasets: [
                  {
                      label: 'Pendências Abertas',
                      data: dadosPendenciasAbertas,
                      borderColor: 'rgba(255, 99, 132, 1)',
                      backgroundColor: 'rgba(255, 99, 132, 0.2)',
                  },
                  {
                      label: 'Pendências Encerradas',
                      data: dadosPendenciasEncerradas,
                      borderColor: 'rgba(54, 162, 235, 1)',
                      backgroundColor: 'rgba(54, 162, 235, 0.2)',
                  }
              ]
          },
          options: {
              responsive: true,
              plugins: {
                  legend: { position: 'top' },
                  title: { display: true, text: 'Pendências nos Últimos 12 Meses' }
              },
              scales: {
                  y: { beginAtZero: true, ticks: { precision: 0 } }
              }
          }
      };

    // Produtos mais vendidos nos últimos 12 meses (Comparação por produto e mês)
    const produtosMaisVendidosMeses = <?php echo json_encode($produtosMaisVendidosMeses); ?>;
    const produtosPorMes = {};
    produtosMaisVendidosMeses.forEach(produto => {
        const { mes_formatado, nome, total_vendido } = produto;
        if (!produtosPorMes[mes_formatado]) produtosPorMes[mes_formatado] = {};
        produtosPorMes[mes_formatado][nome] = total_vendido;
    });

    const mesesProdutos = Object.keys(produtosPorMes).map(traduzirMes);
    const produtosDatasets = Object.keys(produtosPorMes[Object.keys(produtosPorMes)[0]]).map(produto => ({
        label: produto,
        data: Object.values(produtosPorMes).map(mesData => mesData[produto] || 0),
        borderColor: `#${Math.floor(Math.random()*16777215).toString(16)}`,
        backgroundColor: `#${Math.floor(Math.random()*16777215).toString(16)}`
    }));

    const configProdutosMaisVendidosMeses = {
        type: 'line',
        data: {
            labels: mesesProdutos,
            datasets: produtosDatasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Produtos Mais Vendidos nos Últimos 12 Meses' }
            }
        }
    };

    window.onload = function() {
        const ctxVendasMensais = document.getElementById('vendasMensaisChart').getContext('2d');
        new Chart(ctxVendasMensais, configVendasMensais);

        const ctxProdutosMaisVendidos = document.getElementById('produtosMaisVendidosChart').getContext('2d');
        new Chart(ctxProdutosMaisVendidos, configProdutosMaisVendidos);

        const ctxPendencias = document.getElementById('PendenciasChart').getContext('2d');
        new Chart(ctxPendencias, configPendencias);

        const ctxProdutosMaisVendidosMeses = document.getElementById('produtosMaisVendidosMesesChart').getContext('2d');
        new Chart(ctxProdutosMaisVendidosMeses, configProdutosMaisVendidosMeses);
    };
  </script>
    

  </body>
</html>
