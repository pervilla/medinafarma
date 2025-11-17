<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AIMatching extends BaseConfig
{
    // Cloudflare Worker Configuration
    public $cloudflareWorkerUrl = 'https://pharma-matching.perezvillalta.workers.dev';
    
    // Matching Thresholds
    public $autoMatchThreshold = 0.85;  // 85% confidence for automatic matching
    public $suggestThreshold = 0.30;    // 30% confidence for suggestions (bajado para testing)
    public $minThreshold = 0.20;        // 20% minimum to consider (bajado para testing)
    
    // Batch Processing
    public $batchSize = 50;
    public $maxSuggestions = 5;
    
    // Text Normalization
    public $stopWords = ['mg', 'ml', 'g', 'l', 'tab', 'caps', 'amp', 'frasco', 'caja'];
    
    // Pharmaceutical Synonyms
    public $synonyms = [
        'paracetamol' => ['acetaminofen', 'acetaminofén'],
        'ibuprofeno' => ['ibuprofén'],
        'tableta' => ['tab', 'comprimido', 'gragea'],
        'capsula' => ['caps', 'cápsula'],
        'ampolla' => ['amp', 'ampolleta'],
        'jarabe' => ['jbe', 'suspension'],
        'inyectable' => ['iny', 'inyect'],
    ];
    
    // Pharmaceutical Forms
    public $formasFarmaceuticas = [
        'tableta', 'capsula', 'jarabe', 'suspension', 'solucion', 
        'crema', 'gel', 'pomada', 'unguento', 'ampolla', 'inyectable',
        'supositorio', 'ovulo', 'gotas', 'spray', 'aerosol', 'polvo'
    ];
}
