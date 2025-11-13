<?php
require_once 'config/helpers.php';
require_once 'config/database.php';

if (!usuario_autenticado()) {
    redirecionar('index.php');
}

$id_evento = intval($_GET['id_evento'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

$chave_api_mapas = "AIzaSyD1ymgJSOFD9yCS4hoC7hNeU8Km40bbQi0";

$consulta = $conexao->prepare("SELECT a.*, 
                        (SELECT COUNT(*) FROM reservas WHERE id_evento = a.id AND status = 'A') as inscricoes_ativas
                        FROM eventos a WHERE a.id = ?");
$consulta->bind_param("i", $id_evento);
$consulta->execute();
$resultado = $consulta->get_result();
$evento = $resultado->fetch_assoc();
$consulta->close();

if (!$evento) {
    redirecionar('dashboard.php?msg_erro=Evento não encontrado');
}

$coordenadas = explode(',', $evento['local']);
$latitude = floatval(trim($coordenadas[0] ?? 0));
$longitude = floatval(trim($coordenadas[1] ?? 0));

$vagas_livres = $evento['capacidade'] - $evento['inscricoes_ativas'];

$informacao_clima = null;

$data_evento = new DateTime($evento['data']);
$data_hoje = new DateTime();
$data_hoje->setTime(0,0,0);
$data_maxima_previsao = (clone $data_hoje)->modify('+16 days');

if ($data_evento >= $data_hoje && $data_evento <= $data_maxima_previsao && $latitude != 0 && $longitude != 0) {
    $data_inicio = $evento['data'];
    $data_fim = $evento['data'];
    
    $url_clima = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode&timezone=auto&start_date={$data_inicio}&end_date={$data_fim}";
    
    $resposta_clima = @file_get_contents($url_clima);
    
    if ($resposta_clima !== false) {
        $dados_clima = json_decode($resposta_clima, true);
        
        if (isset($dados_clima['daily']) && count($dados_clima['daily']['time']) > 0) {
            $codigo_clima = $dados_clima['daily']['weathercode'][0];
            $temp_maxima = $dados_clima['daily']['temperature_2m_max'][0];
            $temp_minima = $dados_clima['daily']['temperature_2m_min'][0];
            $precipitacao = $dados_clima['daily']['precipitation_sum'][0];
            
            $condicao = converter_codigo_clima($codigo_clima);
            $icone = obter_icone_clima($codigo_clima);
            
            $informacao_clima = [
                'condicao' => $condicao,
                'icone' => $icone,
                'temp_max' => round($temp_maxima),
                'temp_min' => round($temp_minima),
                'precipitacao' => $precipitacao,
                'chance_chuva' => $precipitacao > 0 ? 'Possível' : 'Improvável'
            ];
        }
    }
}

function converter_codigo_clima($codigo) {
    $codigos = [
        0 => 'Céu limpo', 1 => 'Principalmente limpo', 2 => 'Parcialmente nublado',
        3 => 'Nublado', 45 => 'Nevoeiro', 48 => 'Nevoeiro com geada',
        51 => 'Chuvisco leve', 53 => 'Chuvisco moderado', 55 => 'Chuvisco intenso',
        61 => 'Chuva leve', 63 => 'Chuva moderada', 65 => 'Chuva forte',
        71 => 'Neve leve', 73 => 'Neve moderada', 75 => 'Neve forte',
        80 => 'Pancadas de chuva leves', 81 => 'Pancadas moderadas', 82 => 'Pancadas fortes',
        95 => 'Tempestade', 96 => 'Tempestade com granizo', 99 => 'Tempestade intensa'
    ];
    return $codigos[$codigo] ?? 'Condição desconhecida';
}

function obter_icone_clima($codigo) {
    $icones = [
        0 => '☀️', 1 => '🌤️', 2 => '⛅', 3 => '☁️', 45 => '🌫️',
        51 => '🌦️', 53 => '🌦️', 61 => '🌧️', 63 => '🌧️', 65 => '⛈️',
        71 => '🌨️', 73 => '🌨️', 75 => '❄️', 80 => '🌦️', 95 => '⛈️'
    ];
    return $icones[$codigo] ?? '🌈';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verificar = $conexao->prepare("SELECT * FROM reservas WHERE login_usuario = ? AND id_evento = ? AND status = 'A'");
    $verificar->bind_param("si", $usuario_id, $id_evento);
    $verificar->execute();
    $inscricao_existente = $verificar->get_result()->fetch_assoc();
    $verificar->close();

    if ($inscricao_existente) {
        $mensagem_erro = "Você já possui uma inscrição ativa para este evento.";
    } elseif ($vagas_livres <= 0) {
        $mensagem_erro = "Este evento está esgotado.";
    } else {
        $inserir = $conexao->prepare("INSERT INTO reservas (login_usuario, id_evento, status) VALUES (?, ?, 'A')");
        $inserir->bind_param("si", $usuario_id, $id_evento);
        
        if ($inserir->execute()) {
            $inserir->close();
            redirecionar('dashboard.php?msg_sucesso=Inscrição realizada com sucesso!');
        } else {
            $mensagem_erro = "Erro ao realizar inscrição. Tente novamente.";
        }
        $inserir->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Inscrição</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
    <style>
        /* Estilos para altura completa do layout */
        html, body {
            height: 100%;
        }
        .container-layout {
            min-height: calc(100vh - 56px);
        }
        .card-altura-completa {
            height: 100%;
        }
        /* Novo estilo para card de clima minimalista e diferente */
        .clima-novo {
            background: linear-gradient(135deg, #251be6 0%, #2073df 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
        }
        .temperatura-grande {
            font-size: 3rem;
            font-weight: 300;
            line-height: 1;
        }
        .clima-detalhes {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            padding: 12px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="dashboard.php">
                <i class="bi bi-calendar-check me-2"></i>Sistema de Eventos
            </a>
            <!-- Adicionar menu de navegação completo -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="minhas_inscricoes.php">
                            <i class="bi bi-bookmark-check me-1"></i>Minhas Inscrições
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="processar/sair.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4 container-layout">
        <div class="row g-4 h-100">
            <!-- Coluna da esquerda: Card com foto no topo e informações abaixo -->
            <div class="col-lg-5">
                <div class="card shadow-sm fade-in card-altura-completa">
                    <!-- Foto do evento no topo do card -->
                    <img src="<?= htmlspecialchars($evento['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($evento['nome']) ?>" style="height: 250px; object-fit: cover;">
                    
                    <div class="card-body p-4 d-flex flex-column">
                        <h2 class="fw-bold mb-3"><?= htmlspecialchars($evento['nome']) ?></h2>
                        
                        <?php if (isset($mensagem_erro)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($mensagem_erro) ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <p class="text-muted mb-0"><?= htmlspecialchars($evento['descricao']) ?></p>
                        </div>

                        <div class="mb-3">
                            <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Informações do Evento</h5>
                            <div class="d-flex flex-column gap-2">
                                <p class="mb-2"><i class="bi bi-calendar3 text-primary me-2"></i><strong>Data:</strong> <?= date('d/m/Y', strtotime($evento['data'])) ?></p>
                                <p class="mb-2"><i class="bi bi-clock text-primary me-2"></i><strong>Horário:</strong> <?= date('H:i', strtotime($evento['hora'])) ?></p>
                                <p class="mb-0"><i class="bi bi-people text-primary me-2"></i><strong>Vagas disponíveis:</strong> <span class="badge bg-success"><?= $vagas_livres ?></span></p>
                            </div>
                        </div>

                        <?php if (is_array($informacao_clima)): ?>
                            <!-- Novo design completamente diferente para o card de clima -->
                            <div class="mb-4">
                                <div class="clima-novo">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="text-white-50 mb-1">Previsão do Tempo</h6>
                                            <p class="mb-0 fw-semibold"><?= $informacao_clima['condicao'] ?></p>
                                        </div>
                                        <div class="temperatura-grande"><?= $informacao_clima['icone'] ?></div>
                                    </div>
                                    
                                    <div class="clima-detalhes">
                                        <div class="row g-2 text-center">
                                            <div class="col-4">
                                                <div class="small text-white-50">Máxima</div>
                                                <div class="h4 mb-0"><?= $informacao_clima['temp_max'] ?>°</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="small text-white-50">Mínima</div>
                                                <div class="h4 mb-0"><?= $informacao_clima['temp_min'] ?>°</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="small text-white-50">Chuva</div>
                                                <div class="h6 mb-0"><?= $informacao_clima['chance_chuva'] ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Botões no final do card -->
                        <div class="mt-auto">
                            <?php if ($vagas_livres > 0): ?>
                                <form method="POST" class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="bi bi-check-circle me-2"></i>Confirmar Inscrição
                                    </button>
                                    <a href="dashboard.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-2"></i>Cancelar
                                    </a>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning mb-3" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Este evento está esgotado.
                                </div>
                                <a href="dashboard.php" class="btn btn-secondary w-100">
                                    <i class="bi bi-arrow-left me-2"></i>Voltar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna da direita: Mapa grande com altura completa -->
            <div class="col-lg-7">
                <div class="card shadow-sm fade-in card-altura-completa">
                    <div class="card-body p-0">
                        <div id="mapa" style="height: 100%; min-height: 600px; border-radius: 0.375rem;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function inicializarMapa() {
            const localizacao = { lat: <?= $latitude ?>, lng: <?= $longitude ?> };
            
            const mapa = new google.maps.Map(document.getElementById("mapa"), {
                zoom: 15,
                center: localizacao,
            });
            
            new google.maps.Marker({
                position: localizacao,
                map: mapa,
                title: "<?= htmlspecialchars($evento['nome']) ?>",
            });
        }
    </script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=<?= $chave_api_mapas ?>&callback=inicializarMapa"></script>
</body>
</html>
