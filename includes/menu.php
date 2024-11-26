<?php 
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark" style="width: 280px;">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <img src="includes\favicon_32.png" class="card-img-top bi pe-none me-2" alt="..." width="32" height="32">
        <span class="fs-4"><h4 style="font-size: 20px;">Tata Presentes</h4></span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link text-white <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <img src="includes\dashboard.png" class="rounded float-start me-2" alt="..." width="24" height="24"> Painel
            </a>
        </li>
        <li>
            <a href="vendas.php" class="nav-link text-white <?php echo ($current_page == 'vendas.php') ? 'active' : ''; ?>">
                <img src="includes\venda.png" class="rounded float-start me-2" alt="..." width="24" height="24"> Vendas
            </a>
        </li>
        <li>
            <a href="clientes.php" class="nav-link text-white <?php echo ($current_page == 'clientes.php') ? 'active' : ''; ?>">
                <img src="includes\clientes.png" class="rounded float-start me-2" alt="..." width="24" height="24"> Clientes
            </a>
        </li>
        <li>
            <a href="pendencia.php" class="nav-link text-white <?php echo ($current_page == 'pendencia.php') ? 'active' : ''; ?>">
                <img src="includes\pendencia.png" class="rounded float-start me-2" alt="..." width="24" height="24"> Pendência
            </a>
        </li>
        <li>
            <a href="produto.php" class="nav-link text-white <?php echo ($current_page == 'produto.php') ? 'active' : ''; ?>">
                <img src="includes\produto.png" class="rounded float-start me-2" alt="..." width="24" height="24"> Produto
            </a>
        </li>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="includes\perfil.png" alt="" width="32" height="32" class="rounded-circle me-2">
            <strong>Usuário</strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
            <li><a class="dropdown-item" href="#">Configuração</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Sair</a></li>
        </ul>
    </div>
</div>
