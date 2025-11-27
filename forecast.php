<?php

include_once 'api/wave_api.php';

class SurfAnalysis {
    private $waveAPI;
    
    public function __construct() {
        $this->waveAPI = new WaveAnalysisAPI();
    }
    
    public function analisarCondicoes($localizacao) {
        
        $dadosBrutos = $this->waveAPI->getTechnicalData($localizacao);
        
        return $this->processarAnaliseTecnica($dadosBrutos);
    }
    
    private function processarAnaliseTecnica($dados) {
        
        $sqi = $this->calcularSQI($dados);
        
        
        $analise = [
            'metricas_tecnicas' => [
                'altura_onda' => $dados['swell_height'],
                'periodo_onda' => $dados['swell_period'],
                'direcao_swell' => $dados['swell_direction'],
                'vento_velocidade' => $dados['wind_speed'],
                'vento_direcao' => $dados['wind_direction'],
                'mare' => $dados['tide'],
                'temperatura_agua' => $dados['water_temp']
            ],
            'indices' => [
                'sqi' => $sqi,
                'potencia_onda' => $this->calcularPotenciaOnda($dados),
                'qualidade_pico' => $this->avaliarQualidadePico($dados),
                'consistencia' => $this->avaliarConsistencia($dados)
            ],
            'analise_condicoes' => $this->gerarAnaliseCondicoes($dados, $sqi),
            'recomendacoes' => $this->gerarRecomendacoesTecnicas($dados, $sqi)
        ];
        
        return $analise;
    }
    
    private function calcularSQI($dados) {
        
        $score = (
            ($dados['swell_height'] * 2) +
            ($dados['swell_period'] * 3) +
            (($dados['wind_speed'] < 15) ? 20 : (($dados['wind_speed'] < 25) ? 10 : 0)) +
            ($dados['water_temp'] * 0.5)
        ) / 10;
        
        return min(100, max(0, $score));
    }
    
    private function calcularPotenciaOnda($dados) {
        return ($dados['swell_height'] * $dados['swell_period']) / 10;
    }
    
    private function avaliarQualidadePico($dados) {
        $score = 0;
        
       
        if ($this->isVentoOffshore($dados['wind_direction'], $dados['swell_direction'])) {
            $score += 30;
        }
        
        
        if ($dados['swell_period'] >= 12) $score += 40;
        elseif ($dados['swell_period'] >= 8) $score += 25;
        
        
        if ($dados['swell_height'] >= 1.5 && $dados['swell_height'] <= 8) $score += 30;
        
        return $score;
    }
    
    private function isVentoOffshore($ventoDir, $swellDir) {
        $diff = abs($ventoDir - $swellDir);
        return $diff > 100 && $diff < 260;
    }
    
    private function avaliarConsistencia($dados) {
        return ($dados['swell_period'] >= 10) ? 'Alta' : (($dados['swell_period'] >= 7) ? 'Média' : 'Baixa');
    }
    
    private function gerarAnaliseCondicoes($dados, $sqi) {
        $condicoes = [];
        
        
        if ($dados['swell_period'] >= 14) {
            $condicoes['swell'] = "🔄 SWELL DE LONGA PERÍODIA - Energia excelente para formação de ondas";
        } elseif ($dados['swell_period'] >= 10) {
            $condicoes['swell'] = "📊 SWELL CONSISTENTE - Boa energia e intervalos regulares";
        } else {
            $condicoes['swell'] = "🌊 SWELL CURTO - Ondas menos organizadas";
        }
        
        
        if ($this->isVentoOffshore($dados['wind_direction'], $dados['swell_direction'])) {
            $condicoes['vento'] = "💨 VENTO OFFSHORE IDEAL - Condições ótimas para formação de paredes";
        } elseif ($dados['wind_speed'] < 8) {
            $condicoes['vento'] = "🌬️ VENTO FRACO - Condições glassy favoráveis";
        } else {
            $condicoes['vento'] = "⚠️ VENTO ONSHORE - Pode afetar a qualidade das ondas";
        }
        
        
        if ($sqi >= 80) {
            $condicoes['geral'] = "🚀 CONDIÇÕES EXCELENTES - Pico em funcionamento ideal";
        } elseif ($sqi >= 60) {
            $condicoes['geral'] = "✅ CONDIÇÕES BOAS - Sessão recomendada";
        } elseif ($sqi >= 40) {
            $condicoes['geral'] = "⚠️ CONDIÇÕES REGULARES - Possível, mas com limitações";
        } else {
            $condicoes['geral'] = "❌ CONDIÇÕES POBRES - Melhor aguardar";
        }
        
        return $condicoes;
    }
    
    private function gerarRecomendacoesTecnicas($dados, $sqi) {
        $recomendacoes = [];
        
       
        if ($dados['swell_height'] >= 6) {
            $recomendacoes[] = "🏄‍♂️ PRANCHA: Gun ou semi-gun para ondas grandes";
        } elseif ($dados['swell_height'] >= 3) {
            $recomendacoes[] = "🏄‍♂️ PRANCHA: Shortboard performance";
        } else {
            $recomendacoes[] = "🏄‍♂️ PRANCHA: Fish ou funboard para ondas pequenas";
        }
        
        
        if ($dados['swell_period'] >= 12) {
            $recomendacoes[] = "⚡ TÉCNICA: Posicionamento preciso essencial - ondas mais rápidas";
        }
        
        if ($this->isVentoOffshore($dados['wind_direction'], $dados['swell_direction'])) {
            $recomendacoes[] = "💎 CONDIÇÃO: Ideal para manobras aéreos e tubos";
        }
        
        
        if ($dados['swell_height'] >= 8) {
            $recomendacoes[] = "🦺 SEGURANÇA: Recomendado uso de leash reforçado ";
        }
        
        return $recomendacoes;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $localizacao = $_POST['localizacao'] ?? '';
    
    if (empty($localizacao)) {
        header('Location: index.php?error=Localização não informada');
        exit;
    }
    
    $analisador = new SurfAnalysis();
    $resultado = $analisador->analisarCondicoes($localizacao);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análise Técnica - <?php echo htmlspecialchars($localizacao); ?> | Surf Forecast Pro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container forecast-container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <h1>🌊 Surf Forecast Pro</h1>
                <p>Análise Técnica Detalhada</p>
            </div>
        </header>

        <!-- Analysis Header -->
        <div class="analysis-header">
            <h2 class="location-title"><?php echo htmlspecialchars(ucfirst($localizacao)); ?></h2>
            <p class="coordinates">Análise em tempo real - <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <!-- Quality Score -->
        <div class="conditions-card">
            <div class="quality-score">
                SQI: <?php echo round($resultado['indices']['sqi']); ?> / 100
            </div>
            <h3><?php echo $resultado['analise_condicoes']['geral']; ?></h3>
        </div>

        <!-- Technical Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-value"><?php echo $resultado['metricas_tecnicas']['altura_onda']; ?>m</div>
                <div class="metric-label">Altura do Swell</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?php echo $resultado['metricas_tecnicas']['periodo_onda']; ?>s</div>
                <div class="metric-label">Período</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?php echo $resultado['metricas_tecnicas']['vento_velocidade']; ?> km/h</div>
                <div class="metric-label">Velocidade do Vento</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?php echo $resultado['metricas_tecnicas']['temperatura_agua']; ?>°C</div>
                <div class="metric-label">Temp. Água</div>
            </div>
        </div>

        <!-- Technical Analysis -->
        <div class="conditions-card">
            <div class="technical-analysis">
                <div class="analysis-section">
                    <h4>📊 Análise do Swell</h4>
                    <p><?php echo $resultado['analise_condicoes']['swell']; ?></p>
                </div>
                
                <div class="analysis-section">
                    <h4>💨 Condições de Vento</h4>
                    <p><?php echo $resultado['analise_condicoes']['vento']; ?></p>
                </div>
                
                <div class="analysis-section">
                    <h4>⚡ Índices Técnicos</h4>
                    <p><strong>Potência da Onda:</strong> <?php echo round($resultado['indices']['potencia_onda'], 1); ?> kW/m</p>
                    <p><strong>Qualidade do Pico:</strong> <?php echo $resultado['indices']['qualidade_pico']; ?>%</p>
                    <p><strong>Consistência:</strong> <?php echo $resultado['indices']['consistencia']; ?></p>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="recommendations">
            <h3>🎯 Recomendações Técnicas</h3>
            <ul class="equipment-list">
                <?php foreach ($resultado['recomendacoes'] as $recomendacao): ?>
                    <li><?php echo $recomendacao; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Navigation -->
        <div class="nav-actions">
            <a href="index.php" class="btn btn-primary">🔄 Nova Análise</a>
            <a href="#" class="btn btn-secondary">📈 Histórico</a>
        </div>
    </div>
</body>
</html>