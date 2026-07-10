<?php
// perfil.php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['usuario_id'];
$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nu_ano = 2025;
    
    // Lista completa com as 10 variáveis de perfil e as 25 socioeconômicas
    $campos = [
        'tp_faixa_etaria', 'tp_sexo', 'tp_estado_civil', 'tp_cor_raca', 'tp_nacionalidade',
        'tp_st_conclusao', 'tp_ano_concluiu', 'tp_ensino', 'in_treineiro', 'sg_uf_prova',
        'q001', 'q002', 'q003', 'q004', 'q005', 'q006', 'q007', 'q008', 'q009', 'q010',
        'q011', 'q012', 'q013', 'q014', 'q015', 'q016', 'q017', 'q018', 'q019', 'q020',
        'q021', 'q022', 'q023', 'q024', 'q025'
    ];
    
    $valores = [$userId, $nu_ano];
    $partesUpdate = [];
    
    foreach ($campos as $campo) {
        $valores[] = $_POST[$campo] ?? null;
        $partesUpdate[] = "$campo = VALUES($campo)";
    }
    
    $placeholders = implode(', ', array_fill(0, count($valores), '?'));
    $strCampos = implode(', ', $campos);
    $strUpdate = implode(', ', $partesUpdate);
    
    // Executa o Upsert (Insert ou Update)
    $sql = "INSERT INTO perfil_socioeconomico (usuario_id, nu_ano, $strCampos) 
            VALUES ($placeholders) 
            ON DUPLICATE KEY UPDATE $strUpdate";
            
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($valores)) {
        $mensagem = "Perfil atualizado com sucesso!";
    }
}

$stmt = $pdo->prepare("SELECT * FROM perfil_socioeconomico WHERE usuario_id = ?");
$stmt->execute([$userId]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC) ?: []; 

function sel($campo, $valor, $array) {
    return (isset($array[$campo]) && $array[$campo] == $valor) ? 'selected' : '';
}

// Agrupamento de perguntas para renderização limpa no HTML[cite: 2]
$perguntasMasc = [
    'q008' => 'Na sua residência tem banheiro?',
    'q009' => 'Na sua residência tem quartos para dormir?',
    'q010' => 'Na sua residência tem carro?',
    'q013' => 'Na sua residência tem freezer (independente ou segunda porta)?',
    'q016' => 'Na sua residência tem forno micro-ondas?',
    'q022' => 'Na sua residência tem telefone celular?',
    'q024' => 'Na sua residência tem computador?'
];

$perguntasFem = [
    'q011' => 'Na sua residência tem motocicleta?',
    'q012' => 'Na sua residência tem geladeira?',
    'q014' => 'Na sua residência tem máquina de lavar roupa? (tanquinho NÃO)',
    'q015' => 'Na sua residência tem máquina de secar roupa?',
    'q017' => 'Na sua residência tem máquina de lavar louça?',
    'q019' => 'Na sua residência tem televisão em cores?'
];

$perguntasSimNao = [
    'q018' => 'Na sua residência tem aspirador de pó?',
    'q020' => 'Na sua residência tem aparelho de DVD?',
    'q021' => 'Na sua residência tem TV por assinatura?',
    'q023' => 'Na sua residência tem telefone fixo?',
    'q025' => 'Na sua residência tem acesso à Internet?'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil Oficial ENEM</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f4f9; color: #333; } 
        .container { background: white; padding: 30px; max-width: 800px; margin: 0 auto; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h3 { border-bottom: 2px solid #003366; padding-bottom: 5px; color: #003366; margin-top: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px;}
        select, input[type="number"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #003366; color: white; border: none; padding: 15px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 20px; }
        button:hover { background: #002244; }
        .msg { padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .msg-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📋 Perfil Escolar e Socioeconômico</h2>
        <a href="index.php" style="color: #003366; text-decoration: none; font-weight: bold;">⬅ Voltar ao Simulador</a>
        
        <?php if($mensagem): ?>
            <div class="msg msg-success"><?= $mensagem ?></div>
        <?php endif; ?>
        
        <form method="POST">
            
            <!-- ================= DADOS PESSOAIS ================= -->
            <h3>👤 Dados Pessoais</h3>
            
            <div class="form-group">
                <label>Sexo:</label>
                <select name="tp_sexo">
                    <option value="">Selecione...</option>
                    <option value="M" <?= sel('tp_sexo', 'M', $perfil) ?>>Masculino</option>
                    <option value="F" <?= sel('tp_sexo', 'F', $perfil) ?>>Feminino</option>
                </select>
            </div>

            <div class="form-group">
                <label>Estado Civil:</label>
                <select name="tp_estado_civil">
                    <option value="">Selecione...</option>
                    <option value="0" <?= sel('tp_estado_civil', '0', $perfil) ?>>Não Informado</option>
                    <option value="1" <?= sel('tp_estado_civil', '1', $perfil) ?>>Solteiro(a)</option>
                    <option value="2" <?= sel('tp_estado_civil', '2', $perfil) ?>>Casado(a) / Mora com companheiro(a)</option>
                    <option value="3" <?= sel('tp_estado_civil', '3', $perfil) ?>>Divorciado(a) / Desquitado(a) / Separado(a)</option>
                    <option value="4" <?= sel('tp_estado_civil', '4', $perfil) ?>>Viúvo(a)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Cor/Raça:</label>
                <select name="tp_cor_raca">
                    <option value="0" <?= sel('tp_cor_raca', '0', $perfil) ?>>Não Declarado</option>
                    <option value="1" <?= sel('tp_cor_raca', '1', $perfil) ?>>Branca</option>
                    <option value="2" <?= sel('tp_cor_raca', '2', $perfil) ?>>Preta</option>
                    <option value="3" <?= sel('tp_cor_raca', '3', $perfil) ?>>Parda</option>
                    <option value="4" <?= sel('tp_cor_raca', '4', $perfil) ?>>Amarela</option>
                    <option value="5" <?= sel('tp_cor_raca', '5', $perfil) ?>>Indígena</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nacionalidade:</label>
                <select name="tp_nacionalidade">
                    <option value="1" <?= sel('tp_nacionalidade', '1', $perfil) ?>>Brasileiro(a)</option>
                    <option value="2" <?= sel('tp_nacionalidade', '2', $perfil) ?>>Brasileiro(a) Naturalizado(a)</option>
                    <option value="3" <?= sel('tp_nacionalidade', '3', $perfil) ?>>Estrangeiro(a)</option>
                    <option value="4" <?= sel('tp_nacionalidade', '4', $perfil) ?>>Brasileiro(a) Nato(a), nascido(a) no exterior</option>
                </select>
            </div>

            <div class="form-group">
                <label>Faixa Etária:</label>
                <select name="tp_faixa_etaria">
                    <option value="1" <?= sel('tp_faixa_etaria', '1', $perfil) ?>>Menor de 17 anos</option>
                    <option value="2" <?= sel('tp_faixa_etaria', '2', $perfil) ?>>17 anos</option>
                    <option value="3" <?= sel('tp_faixa_etaria', '3', $perfil) ?>>18 anos</option>
                    <option value="4" <?= sel('tp_faixa_etaria', '4', $perfil) ?>>19 anos</option>
                    <option value="5" <?= sel('tp_faixa_etaria', '5', $perfil) ?>>20 anos</option>
                    <option value="11" <?= sel('tp_faixa_etaria', '11', $perfil) ?>>Entre 26 e 30 anos</option>
                    <option value="20" <?= sel('tp_faixa_etaria', '20', $perfil) ?>>Maior de 70 anos</option>
                </select>
            </div>

            <!-- ================= DADOS ESCOLARES ================= -->
            <h3>🏫 Dados Escolares e de Prova</h3>

            <div class="form-group">
                <label>Situação de Conclusão do Ensino Médio:</label>
                <select name="tp_st_conclusao">
                    <option value="1" <?= sel('tp_st_conclusao', '1', $perfil) ?>>Já concluí o Ensino Médio</option>
                    <option value="2" <?= sel('tp_st_conclusao', '2', $perfil) ?>>Estou cursando e concluirei este ano</option>
                    <option value="3" <?= sel('tp_st_conclusao', '3', $perfil) ?>>Estou cursando e concluirei após este ano</option>
                    <option value="4" <?= sel('tp_st_conclusao', '4', $perfil) ?>>Não concluí e não estou cursando</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ano de Conclusão do Ensino Médio:</label>
                <select name="tp_ano_concluiu">
                    <option value="0" <?= sel('tp_ano_concluiu', '0', $perfil) ?>>Não Informado / Não Concluído</option>
                    <option value="1" <?= sel('tp_ano_concluiu', '1', $perfil) ?>>2024</option>
                    <option value="2" <?= sel('tp_ano_concluiu', '2', $perfil) ?>>2023</option>
                    <option value="3" <?= sel('tp_ano_concluiu', '3', $perfil) ?>>2022</option>
                    <option value="19" <?= sel('tp_ano_concluiu', '19', $perfil) ?>>Antes de 2007</option>
                </select>
            </div>

            <div class="form-group">
                <label>Tipo de Instituição do Ensino Médio:</label>
                <select name="tp_ensino">
                    <option value="1" <?= sel('tp_ensino', '1', $perfil) ?>>Ensino Regular</option>
                    <option value="2" <?= sel('tp_ensino', '2', $perfil) ?>>Educação Especial</option>
                </select>
            </div>

            <div class="form-group">
                <label>Você é Treineiro(a)?</label>
                <select name="in_treineiro">
                    <option value="0" <?= sel('in_treineiro', '0', $perfil) ?>>Não</option>
                    <option value="1" <?= sel('in_treineiro', '1', $perfil) ?>>Sim (Faço a prova apenas para treinar)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Estado da Aplicação da Prova (UF):</label>
                <select name="sg_uf_prova">
                    <option value="SP" <?= sel('sg_uf_prova', 'SP', $perfil) ?>>São Paulo</option>
                    <option value="MG" <?= sel('sg_uf_prova', 'MG', $perfil) ?>>Minas Gerais</option>
                    <option value="RJ" <?= sel('sg_uf_prova', 'RJ', $perfil) ?>>Rio de Janeiro</option>
                    <option value="BA" <?= sel('sg_uf_prova', 'BA', $perfil) ?>>Bahia</option>
                    <option value="CE" <?= sel('sg_uf_prova', 'CE', $perfil) ?>>Ceará</option>
                </select>
            </div>

            <!-- ================= SOCIOECONÔMICO ================= -->
            <h3>👨‍👩‍👧‍👦 Estrutura Familiar e Renda</h3>
            
            <div class="form-group">
                <label>Até que série seu pai, ou o homem responsável por você, estudou?</label>
                <select name="q001">
                    <option value="A" <?= sel('q001', 'A', $perfil) ?>>A - Nunca estudou</option>
                    <option value="B" <?= sel('q001', 'B', $perfil) ?>>B - Não completou a 4ª série/5º ano do Ensino Fundamental</option>
                    <option value="C" <?= sel('q001', 'C', $perfil) ?>>C - Completou a 4ª série/5º ano, mas não a 8ª série/9º ano</option>
                    <option value="D" <?= sel('q001', 'D', $perfil) ?>>D - Completou a 8ª série/9º ano, mas não o Ensino Médio</option>
                    <option value="E" <?= sel('q001', 'E', $perfil) ?>>E - Completou o Ensino Médio, mas não a Faculdade</option>
                    <option value="F" <?= sel('q001', 'F', $perfil) ?>>F - Completou a Faculdade, mas não a Pós-graduação</option>
                    <option value="G" <?= sel('q001', 'G', $perfil) ?>>G - Completou a Pós-graduação</option>
                    <option value="H" <?= sel('q001', 'H', $perfil) ?>>H - Não sei</option>
                </select>
            </div>

            <div class="form-group">
                <label>Até que série sua mãe, ou a mulher responsável por você, estudou?</label>
                <select name="q002">
                    <option value="A" <?= sel('q002', 'A', $perfil) ?>>A - Nunca estudou</option>
                    <option value="B" <?= sel('q002', 'B', $perfil) ?>>B - Não completou a 4ª série/5º ano do Ensino Fundamental</option>
                    <option value="C" <?= sel('q002', 'C', $perfil) ?>>C - Completou a 4ª série/5º ano, mas não a 8ª série/9º ano</option>
                    <option value="D" <?= sel('q002', 'D', $perfil) ?>>D - Completou a 8ª série/9º ano, mas não o Ensino Médio</option>
                    <option value="E" <?= sel('q002', 'E', $perfil) ?>>E - Completou o Ensino Médio, mas não a Faculdade</option>
                    <option value="F" <?= sel('q002', 'F', $perfil) ?>>F - Completou a Faculdade, mas não a Pós-graduação</option>
                    <option value="G" <?= sel('q002', 'G', $perfil) ?>>G - Completou a Pós-graduação</option>
                    <option value="H" <?= sel('q002', 'H', $perfil) ?>>H - Não sei</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ocupação do seu pai ou homem responsável:</label>
                <select name="q003">
                    <option value="A" <?= sel('q003', 'A', $perfil) ?>>A - Grupo 1 (Lavrador, agricultor, pescador, etc.)</option>
                    <option value="B" <?= sel('q003', 'B', $perfil) ?>>B - Grupo 2 (Diarista, motorista particular, porteiro, etc.)</option>
                    <option value="C" <?= sel('q003', 'C', $perfil) ?>>C - Grupo 3 (Padeiro, operário, encanador, taxista, etc.)</option>
                    <option value="D" <?= sel('q003', 'D', $perfil) ?>>D - Grupo 4 (Professor médio/fund., técnico, policial, corretor, etc.)</option>
                    <option value="E" <?= sel('q003', 'E', $perfil) ?>>E - Grupo 5 (Médico, engenheiro, advogado, professor univ., etc.)</option>
                    <option value="F" <?= sel('q003', 'F', $perfil) ?>>F - Não sei</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ocupação da sua mãe ou mulher responsável:</label>
                <select name="q004">
                    <option value="A" <?= sel('q004', 'A', $perfil) ?>>A - Grupo 1 (Lavradora, agricultora, pescadora, etc.)</option>
                    <option value="B" <?= sel('q004', 'B', $perfil) ?>>B - Grupo 2 (Diarista, cuidadora, vendedora, caixa, etc.)</option>
                    <option value="C" <?= sel('q004', 'C', $perfil) ?>>C - Grupo 3 (Padeira, costureira, operária, etc.)</option>
                    <option value="D" <?= sel('q004', 'D', $perfil) ?>>D - Grupo 4 (Professora média/fund., técnica, policial, etc.)</option>
                    <option value="E" <?= sel('q004', 'E', $perfil) ?>>E - Grupo 5 (Médica, engenheira, advogada, diretora, etc.)</option>
                    <option value="F" <?= sel('q004', 'F', $perfil) ?>>F - Não sei</option>
                </select>
            </div>

            <div class="form-group">
                <label>Incluindo você, quantas pessoas moram atualmente em sua residência?</label>
                <input type="number" name="q005" min="1" max="20" value="<?= $perfil['q005'] ?? '' ?>" placeholder="Ex: 4">
            </div>

            <div class="form-group">
                <label>Qual é a renda mensal de sua família? (Soma de todos)</label>
                <select name="q006">
                    <option value="A" <?= sel('q006', 'A', $perfil) ?>>A - Nenhuma Renda</option>
                    <option value="B" <?= sel('q006', 'B', $perfil) ?>>B - Até R$ 1.212,00</option>
                    <option value="C" <?= sel('q006', 'C', $perfil) ?>>C - De R$ 1.212,01 até R$ 1.818,00</option>
                    <option value="D" <?= sel('q006', 'D', $perfil) ?>>D - De R$ 1.818,01 até R$ 2.424,00</option>
                    <option value="E" <?= sel('q006', 'E', $perfil) ?>>E - De R$ 2.424,01 até R$ 3.030,00</option>
                    <option value="F" <?= sel('q006', 'F', $perfil) ?>>F - De R$ 3.030,01 até R$ 3.636,00</option>
                    <option value="G" <?= sel('q006', 'G', $perfil) ?>>G - De R$ 3.636,01 até R$ 4.848,00</option>
                    <option value="H" <?= sel('q006', 'H', $perfil) ?>>H - De R$ 4.848,01 até R$ 6.060,00</option>
                    <option value="I" <?= sel('q006', 'I', $perfil) ?>>I - De R$ 6.060,01 até R$ 7.272,00</option>
                    <option value="J" <?= sel('q006', 'J', $perfil) ?>>J - De R$ 7.272,01 até R$ 8.484,00</option>
                    <option value="K" <?= sel('q006', 'K', $perfil) ?>>K - De R$ 8.484,01 até R$ 9.696,00</option>
                    <option value="L" <?= sel('q006', 'L', $perfil) ?>>L - De R$ 9.696,01 até R$ 10.908,00</option>
                    <option value="M" <?= sel('q006', 'M', $perfil) ?>>M - De R$ 10.908,01 até R$ 12.120,00</option>
                    <option value="N" <?= sel('q006', 'N', $perfil) ?>>N - De R$ 12.120,01 até R$ 14.544,00</option>
                    <option value="O" <?= sel('q006', 'O', $perfil) ?>>O - De R$ 14.544,01 até R$ 18.180,00</option>
                    <option value="P" <?= sel('q006', 'P', $perfil) ?>>P - De R$ 18.180,01 até R$ 24.240,00</option>
                    <option value="Q" <?= sel('q006', 'Q', $perfil) ?>>Q - Acima de R$ 24.240,00</option>
                </select>
            </div>

            <div class="form-group">
                <label>Em sua residência trabalha empregado(a) doméstico(a)?</label>
                <select name="q007">
                    <option value="A" <?= sel('q007', 'A', $perfil) ?>>A - Não</option>
                    <option value="B" <?= sel('q007', 'B', $perfil) ?>>B - Sim, um ou dois dias por semana</option>
                    <option value="C" <?= sel('q007', 'C', $perfil) ?>>C - Sim, três ou quatro dias por semana</option>
                    <option value="D" <?= sel('q007', 'D', $perfil) ?>>D - Sim, pelo menos cinco dias por semana</option>
                </select>
            </div>

            <h3>🏠 Bens e Infraestrutura da Residência</h3>

            <?php foreach ($perguntasMasc as $chave => $texto): ?>
            <div class="form-group">
                <label><?= $texto ?></label>
                <select name="<?= $chave ?>">
                    <option value="A" <?= sel($chave, 'A', $perfil) ?>>A - Não</option>
                    <option value="B" <?= sel($chave, 'B', $perfil) ?>>B - Sim, um</option>
                    <option value="C" <?= sel($chave, 'C', $perfil) ?>>C - Sim, dois</option>
                    <option value="D" <?= sel($chave, 'D', $perfil) ?>>D - Sim, três</option>
                    <option value="E" <?= sel($chave, 'E', $perfil) ?>>E - Sim, quatro ou mais</option>
                </select>
            </div>
            <?php endforeach; ?>

            <?php foreach ($perguntasFem as $chave => $texto): ?>
            <div class="form-group">
                <label><?= $texto ?></label>
                <select name="<?= $chave ?>">
                    <option value="A" <?= sel($chave, 'A', $perfil) ?>>A - Não</option>
                    <option value="B" <?= sel($chave, 'B', $perfil) ?>>B - Sim, uma</option>
                    <option value="C" <?= sel($chave, 'C', $perfil) ?>>C - Sim, duas</option>
                    <option value="D" <?= sel($chave, 'D', $perfil) ?>>D - Sim, três</option>
                    <option value="E" <?= sel($chave, 'E', $perfil) ?>>E - Sim, quatro ou mais</option>
                </select>
            </div>
            <?php endforeach; ?>

            <?php foreach ($perguntasSimNao as $chave => $texto): ?>
            <div class="form-group">
                <label><?= $texto ?></label>
                <select name="<?= $chave ?>">
                    <option value="A" <?= sel($chave, 'A', $perfil) ?>>A - Não</option>
                    <option value="B" <?= sel($chave, 'B', $perfil) ?>>B - Sim</option>
                </select>
            </div>
            <?php endforeach; ?>

            <button type="submit">Salvar Perfil Completo</button>
        </form>
    </div>
</body>
</html>