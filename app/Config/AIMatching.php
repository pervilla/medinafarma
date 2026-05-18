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
        // Genéricos y Variaciones Técnicas
        'paracetamol' => ['acetaminofen', 'acetaminofén', 'panadol', 'umbral', 'kitadol', 'antalgina'],
        'ibuprofeno' => ['ibuprofen', 'doloral', 'advil', 'motrin', 'pyridium'],
        'amoxicilina' => ['amoxil', 'amoxigobens', 'moxilin', 'velamox'],
        'naproxeno' => ['naproxen', 'apronax', 'flanax', 'aleve'],
        'diclofenaco' => ['diclofenac', 'voltaren', 'clofinac', 'diclok'],
        'loratadina' => ['claritine', 'alermisol', 'loritil'],
        'cetirizina' => ['zyrtec', 'alercet', 'histax'],
        'omeprazol' => ['losec', 'uclid', 'gasec'],
        'azitromicina' => ['zitromax', 'azitromin', 'treon'],
        'dexametasona' => ['decadron', 'dexacort', 'cortidex'],
        'ciprofloxacino' => ['ciprofloxacina', 'cipro', 'itacipro'],
        'metformina' => ['glucophage', 'glifortex', 'dianben'],
        'salbutamol' => ['albuterol', 'ventolin', 'asmalina'],
        'enalapril' => ['renitec', 'pressil', 'lotrial'],
        'losartan' => ['cozaar', 'presartan', 'covance'],
        'atorvastatina' => ['lipitor', 'storvas', 'atovar'],
        
        // Formas Farmacéuticas y Abreviaturas
        'tableta' => ['tab', 'comprimido', 'gragea', 'comp', 'tabs'],
        'capsula' => ['caps', 'cápsula', 'cap', 'perla'],
        'ampolla' => ['amp', 'ampolleta', 'vial', 'frasco ampolla'],
        'jarabe' => ['jbe', 'suspension', 'susp', 'solucion oral', 'jar'],
        'inyectable' => ['iny', 'inyect', 'im', 'iv', 'ev'],
        'crema' => ['crm', 'topico', 'unguento', 'pomada', 'ung'],
        'oftalmico' => ['oft', 'colirio', 'gotas oftalmicas'],
        'pediatrico' => ['pdt', 'ped', 'niños'],
    ];
    
    // Pharmaceutical Forms
    public $formasFarmaceuticas = [
        'tableta', 'capsula', 'jarabe', 'suspension', 'solucion', 
        'crema', 'gel', 'pomada', 'unguento', 'ampolla', 'inyectable',
        'supositorio', 'ovulo', 'gotas', 'spray', 'aerosol', 'polvo'
    ];
}
