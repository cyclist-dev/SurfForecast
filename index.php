<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surf Forecast Pro | Análise Técnica de Ondas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <h1>🌊 Surf Forecast Pro</h1>
                <p>Sistema Avançado de Análise de Condições de Surf</p>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="search-section">
                <h2>Análise de Condições de Surf</h2>
                <p class="subtitle">Insira as coordenadas ou nome do spot para análise técnica detalhada</p>
                
                <form method="POST" action="forecast.php" class="search-form">
                    <div class="input-group">
                        <input type="text" name="localizacao" placeholder="Ex: Pipeline, Jeffreys Bay, Ubatuba..." required>
                        <button type="submit" class="btn-analisar">
                            <span>🔍 Analisar Condições</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Cards -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="card-icon">📊</div>
                    <h3>Métricas Técnicas</h3>
                    <p>Altura de onda, período, direção do swell e vento</p>
                </div>
                <div class="info-card">
                    <div class="card-icon">🌡️</div>
                    <h3>Condições Ambientais</h3>
                    <p>Temperatura da água, vento offshore/onshore</p>
                </div>
                <div class="info-card">
                    <div class="card-icon">⚡</div>
                    <h3>Índice de Qualidade</h3>
                    <p>Score baseado em múltiplos fatores técnicos</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>