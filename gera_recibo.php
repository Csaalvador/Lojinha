<?php
require 'dompdf/vendor/autoload.php';
use Dompdf\Dompdf;

// Define a localidade para português do Brasil
setlocale(LC_TIME, 'pt_BR.utf-8', 'portuguese');

// Gera a data formatada corretamente em português
$dataFormatada = strftime('%d de %B de %Y');

include 'system/conexao.php';

function valorPorExtenso($valor = 0) {
    $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
    $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");

    $c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
    $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
    $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove");
    $u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");

    $z = 0;
    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);
    for ($i = 0; $i < count($inteiro); $i++) {
        for ($ii = mb_strlen($inteiro[$i]); $ii < 3; $ii++) {
            $inteiro[$i] = "0" . $inteiro[$i];
        }
    }

    $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
    for ($i = 0; $i < count($inteiro); $i++) {
        $valor = $inteiro[$i];
        $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
        $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
        $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

        $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
        $t = count($inteiro) - 1 - $i;
        $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valor == "000") $z++;
        elseif ($z > 0) $z--;

        if (($t == 1) && ($z > 0) && ($inteiro[0] > 0)) $r .= (($z > 1) ? " de " : "") . $plural[$t];
        if ($r) $string[] = $r;
    }

    return implode(", ", $string);
}

if (isset($_GET['id'])) {
    $venda_id = $_GET['id'];

    // Obter os dados da venda e do cliente
    $stmt = $pdo->prepare("
        SELECT v.data, v.valor_total, c.nome, c.endereco 
        FROM vendas v 
        JOIN clientes c ON v.cliente_id = c.id 
        WHERE v.id = :venda_id
    ");
    $stmt->bindParam(':venda_id', $venda_id, PDO::PARAM_INT);
    $stmt->execute();
    $venda = $stmt->fetch(PDO::FETCH_ASSOC);

    if (isset($_GET['id'])) {
        $venda_id = $_GET['id'];
    
        // Obter os dados da venda e do cliente
        $stmt = $pdo->prepare("
            SELECT v.data, v.valor_total, c.nome, c.endereco 
            FROM vendas v 
            JOIN clientes c ON v.cliente_id = c.id 
            WHERE v.id = :venda_id
        ");
        $stmt->bindParam(':venda_id', $venda_id, PDO::PARAM_INT);
        $stmt->execute();
        $venda = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($venda) {
            // Obter os produtos da venda
            $stmt_produtos = $pdo->prepare("
                SELECT p.nome 
                FROM venda_produto vp 
                JOIN produtos p ON vp.produto_id = p.id 
                WHERE vp.venda_id = :venda_id
            ");
            $stmt_produtos->bindParam(':venda_id', $venda_id, PDO::PARAM_INT);
            $stmt_produtos->execute();
            $produtos = $stmt_produtos->fetchAll(PDO::FETCH_COLUMN);
    
            $produtos_str = implode(", ", $produtos);
    
            // Criar o conteúdo do recibo com estilo ajustado
            $conteudo = '
            <html>
            <head>
                <title>Recibo - Nº ' . $venda_id . ' -  ' . $venda['nome'] . '</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 14px;
                        color: #000;
                    }
                    .recibo-container {
                        border: 2px solid #000;
                        padding: 10px;
                        width: 700px;
                        margin: 0 auto;
                        position: relative;
                        background-color: #e0f0ff;
                        border-radius: 15px;
                    }
                    .recibo-header {
                        text-align: center;
                        font-size: 24px;
                        font-weight: bold;
                        color: #000;
                        margin-bottom: 20px;
                    }
                    .recibo-header .numero,
                    .recibo-header .valor {
                        display: inline-block;
                        width: 180px;
                        font-size: 16px;
                        padding: 10px;
                        border: 1px solid #000;
                        background-color: #fff;
                        border-radius: 10px;
                    }
                    .recibo-header .numero {
                        float: left;
                    }
                    .recibo-header .valor {
                        float: right;
                        text-align: right;
                    }
                    .recibo-content {
                        margin-top: 10px;
                        padding: 10px;
                        border: 1px solid #000;
                        background-color: #fff;
                        border-radius: 10px;
                        margin-bottom: 20px;
                    }
                    .recibo-content p {
                        margin: 5px 0;
                    }
                    .recibo-content .field {
                        margin-bottom: 8px;
                    }
                    .recibo-footer {
                        padding: 10px;
                        text-align: left;
                        margin-top: 20px;
                    }
                    .recibo-footer p {
                        margin: 5px 0;
                    }
                    .assinatura-container {
                        margin-top: 10px;
                    }
                    .assinatura {
                        width: 100%;
                        border-bottom: 1px solid #000;
                        text-align: left;
                        height: 20px;
                        margin-top: -15px;
                    }
                    .data-local {
                        text-align: right;
                        margin-top: 5px;
                    }
                </style>
            </head>
            <body>
                <div class="recibo-container">
                    <div class="recibo-header">
                        <span class="numero">Nº ' . $venda_id . '</span>
                        <span>RECIBO</span>
                        <span class="valor">Valor: R$ ' . number_format($venda['valor_total'], 2, ',', '.') . '</span>
                    </div>
                    <div class="recibo-content">
                        <div class="field">
                            <strong>Recebi(emos) de:</strong> ' . htmlspecialchars($venda['nome']) . '
                        </div>
                        <div class="field">
                            <strong>Endereço:</strong> ' . htmlspecialchars($venda['endereco']) . '
                        </div>
                        <div class="field">
                            <strong>A importância de:</strong> ' . valorPorExtenso($venda['valor_total']) . ' (' . number_format($venda['valor_total'], 2, ',', '.') . ')
                        </div>
                        <div class="field">
                            <strong>Referente:</strong> ' . htmlspecialchars($produtos_str) . '
                        </div>
                    </div>
                    <div class="recibo-footer">
                        <p>Para maior clareza firmo(amos) o presente.</p>
                    </div>
                    <div class="recibo-content">
                        <div class="data-local">
                            <p>Cuiabá, ' . $dataFormatada . '.</p>
                        </div>
                    </div>
                    <div class="recibo-content">
                        <div class="assinatura-container">
                            <p>Emitente: ' . htmlspecialchars($venda['nome']) . '</p>
                            <p>Endereço: ' . htmlspecialchars($venda['endereco']) . '</p>
                            <div><p>Assinatura</p><div class="assinatura"></div></div>
                        </div>
                    </div>
                </div>
            </body>
            </html>';
    
            // Gerar o PDF com Dompdf
            $dompdf = new Dompdf();
            $dompdf->loadHtml($conteudo);
            $dompdf->setPaper('A4', 'portrait');
    
            // Renderizar o PDF
            $dompdf->render();
    
            // Enviar o PDF para o navegador
            $dompdf->stream("recibo_venda_$venda_id.pdf", array("Attachment" => 0));
        } else {
            echo "Venda não encontrada.";
        }
    } else {
        echo "ID da venda não fornecido.";
    }
}
?>
