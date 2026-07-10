<?php
// index.php
session_start();
require 'db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$provasDisponiveis = [
    "Azul (Caderno 7)" => ["codigo" => 1407, "pdf" => "ENEM_2024_P1_CAD_07_DIA_2_AZUL.pdf"],
    "Amarelo (Caderno 5)" => ["codigo" => 1408, "pdf" => "ENEM_2024_P1_CAD_05_DIA_2_AMARELO.pdf"],
    "Cinza (Caderno 6)" => ["codigo" => 1410, "pdf" => "ENEM_2024_P1_CAD_06_DIA_2_CINZA.pdf"],
    "Verde (Caderno 8)" => ["codigo" => 1409, "pdf" => "ENEM_2024_P1_CAD_08_DIA_2_VERDE.pdf"]
];

$descricoesHabilidades = [
    "H_1" => "H1 - Reconhecer, no contexto social, diferentes significados...",
];

$questoes = range(136, 180);
$provaSelecionada = $_POST['prova'] ?? "Azul (Caderno 7)";
$codigoProvaAtual = $provasDisponiveis[$provaSelecionada]["codigo"];
$pdfAtual = "Dados/Provas/" . $provasDisponiveis[$provaSelecionada]["pdf"]; 

// Lógica para Extrair Gabarito Oficial do Banco de Dados
// Busca a string de 45 caracteres correspondente ao gabarito da prova selecionada
$stmt = $pdo->prepare("SELECT tx_gabarito_mt FROM resultados_provas WHERE co_prova_mt = :codigo LIMIT 1");
$stmt->execute(['codigo' => $codigoProvaAtual]);
$resultadoDB = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o banco ainda não tiver dados do INEP importados, previne falhas
$gabaritoOficial = $resultadoDB ? $resultadoDB['tx_gabarito_mt'] : str_repeat("X", 45);

$resultadosCalculados = false;
$acertos = 0;
$totalRespondidas = 0;
$diagnosticoIA = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['avaliar'])) {
    $evidenciasRede = [];

    foreach ($questoes as $index => $q) {
        if (isset($_POST["q_$q"]) && $_POST["q_$q"] !== "NR") {
            $totalRespondidas++;
            $respAluno = $_POST["q_$q"];
            $respCorreta = $gabaritoOficial[$index]; 
            
            $acertou = ($respAluno === $respCorreta) ? 1 : 0;
            if ($acertou === 1) $acertos++;
            
            $evidenciasRede["Item_$q"] = $acertou;
        }
    }

    if ($totalRespondidas > 0) {
        $jsonEvidencias = json_encode($evidenciasRede);
        $comando = escapeshellcmd("python inferencia.py") . " " . escapeshellarg($jsonEvidencias);
        $saidaPython = shell_exec($comando);
        
        $diagnosticoIA = json_decode($saidaPython, true);
        $resultadosCalculados = true;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Simulador ENEM - IA</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f9; }
        .header { background: #003366; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;}
        .container { display: flex; gap: 20px; }
        .coluna { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .opcao-radio { margin-right: 15px; }
        .barra-progresso { background: #e0e0e0; border-radius: 4px; overflow: hidden; height: 20px; margin-top: 5px;}
        .barra-preenchida { background: #4CAF50; height: 100%; }
        .questao-box { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
        select, button { padding: 10px; font-size: 16px; margin-bottom: 20px;}
        button { background: #003366; color: white; border: none; border-radius: 5px; cursor: pointer;}
        button:hover { background: #002244; }
        .btn-perfil { background: #ffcc00; color: #003366; text-decoration: none; padding: 10px 15px; border-radius: 5px; font-weight: bold;}
    </style>
</head>
<body>

<div class="header">
    <h1>🧠 Portal do Aluno: Simulador</h1>
    <div>
        <span style="margin-right: 15px;">Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</span>
        <a href="perfil.php" class="btn-perfil">Meu Perfil Socioeconômico</a>
    </div>
</div>

<!-- Seletor de Prova -->
<form method="POST" action="">
    <label><strong>Selecione o caderno que você realizou:</strong></label><br>
    <select name="prova" onchange="this.form.submit()">
        <?php foreach ($provasDisponiveis as $nome => $dados): ?>
            <option value="<?= $nome ?>" <?= $nome == $provaSelecionada ? 'selected' : '' ?>>
                <?= $nome ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<div class="container">
    <div class="coluna">
        <h2>📄 Caderno de Questões (<?= $provaSelecionada ?>)</h2>
        <embed src="<?= $pdfAtual ?>" width="100%" height="800px" type="application/pdf">
    </div>

    <div class="coluna">
        <h2>🖋️ Cartão de Respostas</h2>
        
        <?php if ($resultadosCalculados): ?>
            <div style="background: #e6f7ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <h3>📊 Resultado Geral</h3>
                <p><strong>Total de Acertos:</strong> <?= $acertos ?> / <?= $totalRespondidas ?></p>
                <p><strong>Aproveitamento:</strong> <?= number_format(($acertos/$totalRespondidas)*100, 1) ?>%</p>
            </div>

            <h3>🔮 Diagnóstico de Habilidades</h3>
            <?php if (isset($diagnosticoIA['erro'])): ?>
                <p style="color: red;">Erro na IA: <?= $diagnosticoIA['erro'] ?></p>
            <?php else: ?>
                <?php 
                uksort($diagnosticoIA, function($a, $b) {
                    return (int)str_replace('H_', '', $a) - (int)str_replace('H_', '', $b);
                });
                
                foreach ($diagnosticoIA as $hab => $prob): 
                    $porcentagem = $prob * 100;
                    $textoHab = $descricoesHabilidades[$hab] ?? $hab;
                ?>
                    <div style="margin-bottom: 15px;">
                        <strong><?= $textoHab ?></strong>
                        <div class="barra-progresso">
                            <div class="barra-preenchida" style="width: <?= $porcentagem ?>%;"></div>
                        </div>
                        <small>Probabilidade de domínio real: <?= number_format($porcentagem, 2) ?>%</small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <hr>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="prova" value="<?= $provaSelecionada ?>">
            <?php foreach ($questoes as $q): ?>
                <div class="questao-box">
                    <strong>Questão <?= $q ?>:</strong>
                    <label class="opcao-radio"><input type="radio" name="q_<?= $q ?>" value="NR" checked> Não Respondida</label>
                    <label class="opcao-radio"><input type="radio" name="q_<?= $q ?>" value="A"> A</label>
                    <label class="opcao-radio"><input type="radio" name="q_<?= $q ?>" value="B"> B</label>
                    <label class="opcao-radio"><input type="radio" name="q_<?= $q ?>" value="C"> C</label>
                    <label class="opcao-radio"><input type="radio" name="q_<?= $q ?>" value="D"> D</label>
                    <label class="opcao-radio"><input type="radio" name="q_<?= $q ?>" value="E"> E</label>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" name="avaliar" value="1" style="width: 100%; padding: 15px;">
                Submeter Respostas para Análise Probabilística
            </button>
        </form>
    </div>
</div>

</body>
</html>