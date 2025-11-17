<?php

namespace App\Controllers;

use App\Models\AIMatchingModel;

class AIMatching extends BaseController
{
    protected $matchingModel;
    protected $config;
    
    public function __construct()
    {
        helper('pharma');
        $this->matchingModel = new AIMatchingModel();
        $this->config = config('AIMatching');
    }
    
    // Main dashboard
    public function index()
    {
        $data['stats'] = $this->matchingModel->getMatchingStats();
        return view('ai_matching/dashboard', $data);
    }
    
    // Process batch matching
    public function processBatch()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('aimatching');
        }
        
        $batchSize = $this->request->getPost('batch_size') ?? $this->config->batchSize;
        
        // Get unmatched products
        $unmatchedProducts = $this->matchingModel->getUnmatchedDigemidProducts($batchSize);
        
        if (empty($unmatchedProducts)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'No hay productos sin emparejar',
                'processed' => 0
            ]);
        }
        
        // Get all articles for matching
        $articles = $this->matchingModel->getEstablishmentArticles();
        
        // Process matching
        $processed = 0;
        $suggestions = 0;
        
        foreach ($unmatchedProducts as $product) {
            $matches = $this->findMatches($product, $articles);
            
            foreach ($matches as $match) {
                if ($match['score'] >= $this->config->suggestThreshold) {
                    $this->matchingModel->saveSuggestion([
                        'cod_prod' => $product['Cod_Prod'],
                        'art_key' => $match['art_key'],
                        'score' => $match['score'],
                        'metadata' => [
                            'product_name' => $product['Nom_Prod'],
                            'article_name' => $match['article_name'],
                            'algorithm' => 'local',
                            'details' => $match['details']
                        ]
                    ]);
                    $suggestions++;
                    
                    // Auto-approve high confidence matches
                    if ($match['score'] >= $this->config->autoMatchThreshold) {
                        // Will be auto-approved in review
                    }
                }
            }
            $processed++;
        }
        
        return $this->response->setJSON([
            'success' => true,
            'processed' => $processed,
            'suggestions' => $suggestions,
            'message' => "Procesados {$processed} productos, {$suggestions} sugerencias generadas"
        ]);
    }
    
    // Find matches for a product
    private function findMatches($product, $articles)
    {
        $matches = [];
        
        // Use full concatenated text for better matching
        $productFullText = $product['Nom_Prod_Full'] ?? $product['Nom_Prod'];
        
        // Prepare product data
        $productData = [
            'nombre' => aplicar_sinonimos($productFullText),
            'concentracion' => extraer_concentracion($productFullText),
            'forma_farmaceutica' => extraer_forma_farmaceutica($productFullText)
        ];
        
        foreach ($articles as $article) {
            $articleFullText = $article['ART_NOMBRE_Full'] ?? $article['ART_NOMBRE'];
            
            $articleData = [
                'nombre' => aplicar_sinonimos($articleFullText),
                'concentracion' => extraer_concentracion($articleFullText),
                'forma_farmaceutica' => extraer_forma_farmaceutica($articleFullText)
            ];
            
            $score = calcular_score_matching($productData, $articleData);
            
            if ($score >= $this->config->minThreshold) {
                $matches[] = [
                    'art_key' => $article['ART_KEY'],
                    'article_name' => $article['ART_NOMBRE'],
                    'score' => round($score, 4),
                    'details' => [
                        'product_normalized' => $productData['nombre'],
                        'article_normalized' => $articleData['nombre'],
                        'concentration_match' => $productData['concentracion'] === $articleData['concentracion']
                    ]
                ];
            }
        }
        
        // Sort by score and limit
        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($matches, 0, $this->config->maxSuggestions);
    }
    
    // Process with Cloudflare Worker
    public function processWithCloudflare()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('aimatching');
        }
        
        $workerUrl = $this->config->cloudflareWorkerUrl ?? '';
        
        if (empty($workerUrl)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Configure cloudflareWorkerUrl en AIMatching.php'
            ]);
        }
        
        try {
            $unmatchedProducts = $this->matchingModel->getUnmatchedDigemidProducts(10);
            $articles = $this->matchingModel->getEstablishmentArticles();
            
            // Log data being sent
            log_message('info', 'Cloudflare Request - Products: ' . count($unmatchedProducts) . ', Articles: ' . count($articles));
            log_message('debug', 'Sample product: ' . json_encode($unmatchedProducts[0] ?? []));
            log_message('debug', 'Sample article: ' . json_encode($articles[0] ?? []));
            
            $client = \Config\Services::curlrequest();
            $response = $client->post($workerUrl, [
                'json' => [
                    'products' => $unmatchedProducts,
                    'articles' => $articles
                ],
                'timeout' => 30
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if ($result['success']) {
                $saved = 0;
                foreach ($result['results'] as $productResult) {
                    foreach ($productResult['matches'] as $match) {
                        $this->matchingModel->saveSuggestion([
                            'cod_prod' => $productResult['cod_prod'],
                            'art_key' => $match['art_key'],
                            'score' => $match['score'],
                            'metadata' => [
                                'product_name' => $productResult['product_name'],
                                'article_name' => $match['article_name'],
                                'algorithm' => 'cloudflare',
                                'details' => $match['details']
                            ]
                        ]);
                        $saved++;
                    }
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'processed' => $result['processed'],
                    'suggestions' => $saved,
                    'message' => "Procesados {$result['processed']} productos, {$saved} sugerencias"
                ]);
            }
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    // Review suggestions
    public function review()
    {
        $data['suggestions'] = $this->matchingModel->getPendingSuggestions();
        return view('ai_matching/review', $data);
    }
    
    // Approve suggestion
    public function approve()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('aimatching/review');
        }
        
        $id = $this->request->getPost('id');
        $userId = session()->get('user_id') ?? 1;
        
        $success = $this->matchingModel->approveSuggestion($id, $userId);
        
        return $this->response->setJSON([
            'success' => $success,
            'message' => $success ? 'Sugerencia aprobada' : 'Error al aprobar'
        ]);
    }
    
    // Reject suggestion
    public function reject()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('aimatching/review');
        }
        
        $id = $this->request->getPost('id');
        $reason = $this->request->getPost('reason') ?? '';
        $userId = session()->get('user_id') ?? 1;
        
        $success = $this->matchingModel->rejectSuggestion($id, $userId, $reason);
        
        return $this->response->setJSON([
            'success' => $success,
            'message' => $success ? 'Sugerencia rechazada' : 'Error al rechazar'
        ]);
    }
    
    // Batch approve high confidence
    public function batchApprove()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('aimatching/review');
        }
        
        $threshold = $this->config->autoMatchThreshold;
        $suggestions = $this->matchingModel->getPendingSuggestions();
        $userId = session()->get('user_id') ?? 1;
        
        $approved = 0;
        foreach ($suggestions as $suggestion) {
            if ($suggestion['score'] >= $threshold) {
                if ($this->matchingModel->approveSuggestion($suggestion['id'], $userId)) {
                    $approved++;
                }
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'approved' => $approved,
            'message' => "{$approved} sugerencias aprobadas automáticamente"
        ]);
    }
    
    // Batch reject all pending
    public function batchReject()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('aimatching/review');
        }
        
        $suggestions = $this->matchingModel->getPendingSuggestions();
        $userId = session()->get('user_id') ?? 1;
        
        $rejected = 0;
        foreach ($suggestions as $suggestion) {
            if ($this->matchingModel->rejectSuggestion($suggestion['id'], $userId, 'Rechazado en lote')) {
                $rejected++;
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'rejected' => $rejected,
            'message' => "{$rejected} sugerencias rechazadas"
        ]);
    }
    
    // Get statistics
    public function stats()
    {
        $stats = $this->matchingModel->getMatchingStats();
        return $this->response->setJSON($stats);
    }
}
