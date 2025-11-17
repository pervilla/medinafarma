import json
import boto3
import re
from difflib import SequenceMatcher

comprehend_medical = boto3.client('comprehendmedical', region_name='us-east-1')

def normalize_text(text):
    """Normalize pharmaceutical text"""
    text = text.lower()
    text = re.sub(r'[áàäâ]', 'a', text)
    text = re.sub(r'[éèëê]', 'e', text)
    text = re.sub(r'[íìïî]', 'i', text)
    text = re.sub(r'[óòöô]', 'o', text)
    text = re.sub(r'[úùüû]', 'u', text)
    text = re.sub(r'[^a-z0-9\s]', ' ', text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def extract_concentration(text):
    """Extract concentration from text"""
    pattern = r'(\d+(?:\.\d+)?)\s*(mg|g|ml|l|mcg|ui|%)'
    matches = re.findall(pattern, text, re.IGNORECASE)
    return ' '.join([f"{m[0]}{m[1]}" for m in matches])

def extract_entities_aws(text):
    """Extract pharmaceutical entities using AWS Comprehend Medical"""
    try:
        response = comprehend_medical.detect_entities_v2(Text=text)
        
        entities = {
            'medications': [],
            'dosages': [],
            'forms': []
        }
        
        for entity in response.get('Entities', []):
            if entity['Category'] == 'MEDICATION':
                entities['medications'].append(entity['Text'])
            elif entity['Type'] == 'DOSAGE':
                entities['dosages'].append(entity['Text'])
            elif entity['Type'] == 'FORM':
                entities['forms'].append(entity['Text'])
        
        return entities
    except Exception as e:
        print(f"AWS Comprehend error: {str(e)}")
        return None

def calculate_similarity(str1, str2):
    """Calculate similarity between two strings"""
    return SequenceMatcher(None, normalize_text(str1), normalize_text(str2)).ratio()

def match_products(product, articles):
    """Match a product against articles"""
    matches = []
    
    product_name = product.get('Nom_Prod', '')
    product_conc = extract_concentration(product.get('Concent', ''))
    
    # Try AWS Comprehend Medical for advanced entity extraction
    aws_entities = extract_entities_aws(product_name)
    
    for article in articles:
        article_name = article.get('ART_NOMBRE', '')
        article_conc = extract_concentration(article_name)
        
        # Calculate name similarity
        name_sim = calculate_similarity(product_name, article_name)
        
        # Calculate concentration similarity
        conc_sim = 0
        if product_conc and article_conc:
            conc_sim = calculate_similarity(product_conc, article_conc)
        
        # Combined score
        score = (name_sim * 0.7) + (conc_sim * 0.3)
        
        if score >= 0.5:
            matches.append({
                'art_key': article.get('ART_KEY'),
                'article_name': article_name,
                'score': round(score, 4),
                'details': {
                    'name_similarity': round(name_sim, 4),
                    'concentration_similarity': round(conc_sim, 4),
                    'aws_entities': aws_entities
                }
            })
    
    # Sort by score and return top 5
    matches.sort(key=lambda x: x['score'], reverse=True)
    return matches[:5]

def lambda_handler(event, context):
    """AWS Lambda handler"""
    try:
        products = event.get('products', [])
        articles = event.get('articles', [])
        
        results = []
        
        for product in products:
            matches = match_products(product, articles)
            results.append({
                'cod_prod': product.get('Cod_Prod'),
                'product_name': product.get('Nom_Prod'),
                'matches': matches
            })
        
        return {
            'statusCode': 200,
            'body': json.dumps({
                'success': True,
                'results': results,
                'processed': len(products)
            })
        }
        
    except Exception as e:
        return {
            'statusCode': 500,
            'body': json.dumps({
                'success': False,
                'error': str(e)
            })
        }
