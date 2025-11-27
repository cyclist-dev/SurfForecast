<?php
class WaveAnalysisAPI {
    private $spotsData;
    
    public function __construct() {
        $this->spotsData = [
            'pipeline' => [
                'swell_height' => rand(15, 30) / 10,
                'swell_period' => rand(12, 18),
                'swell_direction' => rand(300, 330),
                'wind_speed' => rand(5, 20),
                'wind_direction' => rand(0, 360),
                'tide' => rand(0, 3),
                'water_temp' => rand(24, 28)
            ],
            'jeffreys bay' => [
                'swell_height' => rand(10, 25) / 10,
                'swell_period' => rand(10, 16),
                'swell_direction' => rand(200, 240),
                'wind_speed' => rand(8, 25),
                'wind_direction' => rand(0, 360),
                'tide' => rand(1, 4),
                'water_temp' => rand(16, 22)
            ],
            'ubatuba' => [
                'swell_height' => rand(8, 20) / 10,
                'swell_period' => rand(8, 14),
                'swell_direction' => rand(150, 210),
                'wind_speed' => rand(5, 18),
                'wind_direction' => rand(0, 360),
                'tide' => rand(0, 2),
                'water_temp' => rand(22, 26)
            ],
            'guaruja' => [
                'swell_height' => rand(5, 15) / 10,
                'swell_period' => rand(6, 12),
                'swell_direction' => rand(120, 180),
                'wind_speed' => rand(10, 22),
                'wind_direction' => rand(0, 360),
                'tide' => rand(1, 3),
                'water_temp' => rand(20, 25)
            ]
        ];
    }

    private function getCoordenadas($localizacao) {
        $coords = [
            'ubatuba' => ['lat' => -23.4339, 'lng' => -45.0711],
            'guaruja' => ['lat' => -23.9931, 'lng' => -46.2564],
            'pipeline' => ['lat' => 21.6631, 'lng' => -158.0537],
            'jeffreys bay' => ['lat' => -34.0500, 'lng' => 24.9167]
        ];
        
        $local = strtolower($localizacao);
        foreach ($coords as $cidade => $coord) {
            if (strpos($local, $cidade) !== false) {
                return $coord;
            }
        }
        
        return ['lat' => -23.4339, 'lng' => -45.0711];
    }

    private function getDadosSimulados($localizacao) {
        foreach ($this->spotsData as $spot => $data) {
            if (strpos(strtolower($localizacao), $spot) !== false) {
                return $data;
            }
        }
        return $this->spotsData['ubatuba'];
    }
    
    public function getTechnicalData($localizacao) {
        $api_key = 'd6165d90-ca1d-11f0-a0d3-0242ac130003-d6165e62-ca1d-11f0-a0d3-0242ac130003';
        $coordenadas = $this->getCoordenadas($localizacao);
        
        $url = "https://api.stormglass.io/v2/weather/point?lat={$coordenadas['lat']}&lng={$coordenadas['lng']}&params=swellHeight,swellPeriod,windSpeed,windDirection,waterTemperature";
        
        $context = stream_context_create([
            'http' => [
                'header' => "Authorization: {$api_key}\r\n"
            ]
        ]);
        
        try {
            $response = file_get_contents($url, false, $context);
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['hours']) || !isset($data['hours'][0])) {
                throw new Exception('Dados da API inválidos');
            }
            
            $hourData = $data['hours'][0];
            
            return [
                'swell_height' => $hourData['swellHeight'][0]['value'] ?? rand(8, 20) / 10,
                'swell_period' => $hourData['swellPeriod'][0]['value'] ?? rand(8, 14),
                'swell_direction' => $hourData['swellDirection'][0]['value'] ?? rand(150, 210),
                'wind_speed' => $hourData['windSpeed'][0]['value'] ?? rand(5, 18),
                'wind_direction' => $hourData['windDirection'][0]['value'] ?? rand(0, 360),
                'water_temp' => $hourData['waterTemperature'][0]['value'] ?? rand(22, 26),
                'tide' => 1.5
            ];
        } catch (Exception $e) {
            return $this->getDadosSimulados($localizacao);
        }
    }
}
?>