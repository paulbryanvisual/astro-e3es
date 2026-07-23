import json
import re

def clean_heuristic(text):
    # Normalize spaces
    text = re.sub(r'\s+', ' ', text).strip()
    
    # Capitalize 'i' contractions and pronouns
    text = re.sub(r'\bi\b', 'I', text)
    text = re.sub(r"\bi'm\b", "I'm", text)
    text = re.sub(r"\bi've\b", "I've", text)
    text = re.sub(r"\bi'd\b", "I'd", text)
    text = re.sub(r"\bi'll\b", "I'll", text)
    
    # Capitalize first letter
    if not text:
        return ""
        
    # Split text into words and construct sentences of roughly 15-25 words
    words = text.split(' ')
    sentences = []
    current_sentence = []
    
    # Common words that end a sentence-like segment
    sentence_endings = ['isd', 'solutions', 'renovation', 'constantly', 'way', 'program', 'school', 'district', 'projects', 'equipment', 'renovations', 'improvements', 'standpoint', 'standpoints', 'savings', 'board', 'presentation', 'delivery', 'funding', 'oblgation', 'bonds', 'loan', 'program', 'system', 'systems', 'lighting', 'turbines', 'turbines', 'generation', 'cogeneration', 'microgrid', 'solar', 'resilience', 'future', 'development', 'management', 'controls', 'contract', 'contracts', 'procurement', 'guaranteed', 'energy', 'efficiency', 'chiller', 'chillers', 'boiler', 'boilers', 'maintenance', 'comfort', 'classroom', 'classrooms', 'learning', 'environment', 'building', 'buildings', 'audit', 'audits', 'engineering', 'design', 'build', 'construction', 'commissioning', 'operation', 'operations', 'service', 'services', 'utility', 'utilities', 'infrastructure']
    
    for i, w in enumerate(words):
        current_sentence.append(w)
        
        # Check if we should end the sentence
        should_end = False
        w_clean = w.lower().replace('.', '').replace(',', '')
        
        # If we have at least 12 words, and current word is in sentence_endings
        if len(current_sentence) >= 12 and w_clean in sentence_endings:
            should_end = True
            
        # Or if we hit 25 words, force end
        if len(current_sentence) >= 25:
            should_end = True
            
        # If next word is capitalized (e.g. name or acronym)
        if i < len(words) - 1:
            next_w = words[i+1]
            if next_w and next_w[0].isupper() and len(current_sentence) >= 10:
                should_end = True
                
        if should_end:
            # End sentence with period if not already punctuated
            s_text = " ".join(current_sentence).strip()
            if not s_text.endswith('.') and not s_text.endswith('?') and not s_text.endswith('!'):
                s_text += '.'
            # Capitalize
            if s_text:
                s_text = s_text[0].upper() + s_text[1:]
            sentences.append(s_text)
            current_sentence = []
            
    if current_sentence:
        s_text = " ".join(current_sentence).strip()
        if not s_text.endswith('.') and not s_text.endswith('?') and not s_text.endswith('!'):
            s_text += '.'
        if s_text:
            s_text = s_text[0].upper() + s_text[1:]
        sentences.append(s_text)
        
    final_text = " ".join(sentences[:2])
    # Fix double periods
    final_text = final_text.replace('..', '.')
    return final_text

with open('scratch/non_key_raw_quotes.json', 'r') as f:
    non_key = json.load(f)

output = []
for g in non_key:
    cleaned = clean_heuristic(g['raw_text'])
    output.append({
        'video': g['video'],
        'person_id': g['person_id'],
        'person_name': g['person_name'],
        'quote_ids': g['quote_ids'],
        'cleaned_text': cleaned
    })

with open('scratch/non_key_clean_quotes.json', 'w') as f:
    json.dump(output, f, indent=4)

print(f"Cleaned {len(output)} non-key groups using heuristics.")
