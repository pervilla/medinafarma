// Cloudflare Worker para Matching de Productos Farmacéuticos

export default {
  async fetch(request, env) {
    if (request.method !== 'POST') {
      return new Response('Method not allowed', { status: 405 });
    }

    try {
      const { products, articles } = await request.json();
      const results = [];

      for (const product of products) {
        const matches = findMatches(product, articles);
        results.push({
          cod_prod: product.Cod_Prod,
          product_name: product.Nom_Prod,
          matches: matches
        });
      }

      return new Response(JSON.stringify({
        success: true,
        results: results,
        processed: products.length
      }), {
        headers: { 'Content-Type': 'application/json' }
      });

    } catch (error) {
      return new Response(JSON.stringify({
        success: false,
        error: error.message
      }), {
        status: 500,
        headers: { 'Content-Type': 'application/json' }
      });
    }
  }
};

function normalizeText(text) {
  return text.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

function extractConcentration(text) {
  const pattern = /(\d+(?:\.\d+)?)\s*(mg|g|ml|l|mcg|ui|%)/gi;
  const matches = text.match(pattern);
  return matches ? matches.join(' ').toLowerCase() : '';
}

function calculateSimilarity(str1, str2) {
  const s1 = normalizeText(str1);
  const s2 = normalizeText(str2);
  
  // Levenshtein distance
  const len1 = s1.length;
  const len2 = s2.length;
  const matrix = Array(len1 + 1).fill(null).map(() => Array(len2 + 1).fill(0));
  
  for (let i = 0; i <= len1; i++) matrix[i][0] = i;
  for (let j = 0; j <= len2; j++) matrix[0][j] = j;
  
  for (let i = 1; i <= len1; i++) {
    for (let j = 1; j <= len2; j++) {
      const cost = s1[i - 1] === s2[j - 1] ? 0 : 1;
      matrix[i][j] = Math.min(
        matrix[i - 1][j] + 1,
        matrix[i][j - 1] + 1,
        matrix[i - 1][j - 1] + cost
      );
    }
  }
  
  const distance = matrix[len1][len2];
  const maxLen = Math.max(len1, len2);
  return maxLen > 0 ? 1 - (distance / maxLen) : 1;
}

function jaccardSimilarity(str1, str2) {
  const words1 = new Set(normalizeText(str1).split(' '));
  const words2 = new Set(normalizeText(str2).split(' '));
  
  const intersection = new Set([...words1].filter(x => words2.has(x)));
  const union = new Set([...words1, ...words2]);
  
  return union.size > 0 ? intersection.size / union.size : 0;
}

function findMatches(product, articles) {
  const matches = [];
  const productName = product.Nom_Prod || '';
  const productConc = extractConcentration(product.Concent || '');
  
  for (const article of articles) {
    const articleName = article.ART_NOMBRE || '';
    const articleConc = extractConcentration(articleName);
    
    // Calculate similarities
    const levSim = calculateSimilarity(productName, articleName);
    const jacSim = jaccardSimilarity(productName, articleName);
    const nameSim = (levSim + jacSim) / 2;
    
    const concSim = productConc && articleConc 
      ? calculateSimilarity(productConc, articleConc) 
      : 0;
    
    // Combined score
    const score = (nameSim * 0.7) + (concSim * 0.3);
    
    if (score >= 0.5) {
      matches.push({
        art_key: article.ART_KEY,
        article_name: articleName,
        score: Math.round(score * 10000) / 10000,
        details: {
          name_similarity: Math.round(nameSim * 10000) / 10000,
          concentration_similarity: Math.round(concSim * 10000) / 10000
        }
      });
    }
  }
  
  // Sort by score and return top 5
  matches.sort((a, b) => b.score - a.score);
  return matches.slice(0, 5);
}
