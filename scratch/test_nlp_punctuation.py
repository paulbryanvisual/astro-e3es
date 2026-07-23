import json
import re

def clean_and_punctuate(text):
    # Normalize spaces
    text = re.sub(r'\s+', ' ', text).strip()
    
    # Capitalize 'i' contractions and pronouns
    text = re.sub(r'\bi\b', 'I', text)
    text = re.sub(r"\bi'm\b", "I'm", text)
    text = re.sub(r"\bi've\b", "I've", text)
    text = re.sub(r"\bi'd\b", "I'd", text)
    text = re.sub(r"\bi'll\b", "I'll", text)
    
    # Simple rule-based sentence boundary detector:
    # A sentence boundary often occurs before:
    # - I, we, they, he, she, it, our, their, this, when, if, because, as, but, and (when preceded by a non-conjunction)
    # - and followed by a verb or normal speech flow.
    # Let's insert sentence breaks using a list of break words
    break_words = [
        'I', 'we', 'they', 'he', 'she', 'our', 'their', 'this', 'when', 'if', 
        'because', 'the', 'then', 'so', 'as', 'but', 'and', 'there', 'what', 
        'where', 'how', 'why', 'who', 'you', 'it'
    ]
    
    # We will split text into words and try to insert periods
    words = text.split(' ')
    processed_words = []
    
    for i, w in enumerate(words):
        processed_words.append(w)
        if i < len(words) - 1:
            next_w = words[i+1].replace("'", "").replace('"', '')
            # If the next word is a capitalized break word, or a pronoun, we might want to end the sentence
            # but only if the current word doesn't look like an abbreviation (e.g. Dr, Mr, ISD, PE, CEM)
            curr_clean = w.upper().replace('.', '').replace(',', '')
            if curr_clean in ['DR', 'MR', 'MRS', 'MS', 'ISD', 'PE', 'CEM', 'LTD', 'CO', 'INC', 'ST', 'AVE', 'VS']:
                continue
                
            # Heuristic sentence breaks
            should_break = False
            
            # Ending words that typically end a clause/sentence
            if curr_clean in ['NORM', 'GOAL', 'LEADERSHIP', 'US', 'YEAR', 'MONTH', 'DAY', 'TIME', 'SCHOOL', 'DISTRICT', 'PROJECT', 'PARTNERSHIP', 'SAVINGS', 'BUDGET', 'UTILITY', 'BILLS', 'TRACKING', 'CLASSES', 'OPERATIONS', 'ROOF', 'ROOFS', 'COMMUNITY', 'RESILIENCE', 'ENVIRONMENT', 'CURRICULUM', 'CLASSROOM', 'TEMPERATURE', 'SITUATION', 'TAXPAYERS', 'BOARD', 'PRESENTATIONS', 'QUESTIONS', 'SYSTEM', 'SYSTEMS', 'LIGHTING', 'RETROFITS', 'INVESTMENT', 'FUTURE', 'DEVELOPMENT', 'DECISIONS', 'SUPPORT', 'SECURITY', 'TEACHERS', 'STAFF', 'MEMBERS', 'MORNING', 'SUN', 'DONE', 'WELL', 'GREAT', 'BEST', 'FINISH', 'SELF', 'WISDOM', 'PEOPLE', 'INSPIRED', 'SUPERINTENDENT', 'ADVISORY', 'STUDENTS', 'EDUCATION', 'GOVERNMENT', 'HEALTH']:
                should_break = True
                
            # If next word is in break_words and current word is reasonably long, or next is capitalized
            if next_w in break_words and len(w) > 3 and not should_break:
                # Add clause/sentence breaks based on some common combinations
                if w.lower() in ['sore', 'sore', 'sore', 'sore', 'hard', 'better', 'now', 'failure', 'success', 'long', 'quick', 'seamless', 'right', 'solutions', 'problems', 'difference', 'capacity', 'challenging', 'tough', 'worth', 'proud']:
                    should_break = True
            
            if should_break:
                # If current word doesn't already end with punctuation
                if not w.endswith('.') and not w.endswith(',') and not w.endswith('!') and not w.endswith('?'):
                    processed_words[-1] = w + '.'
                    
    # Join and capitalize the first letter of each word following a period
    final_text = " ".join(processed_words)
    sentences = final_text.split('. ')
    capitalized_sentences = []
    for s in sentences:
        s = s.strip()
        if s:
            s = s[0].upper() + s[1:]
            capitalized_sentences.append(s)
            
    final_text = ". ".join(capitalized_sentences)
    if final_text and not final_text.endswith('.'):
        final_text += '.'
        
    return final_text

with open('/Users/bryanpaul/Local Sites/astro-e3es/scratch/raw_merged_quotes.json', 'r') as f:
    groups = json.load(f)

for g in groups[:10]:
    print(f"\n🎥 Video: {g['video']} | 👥 Speaker: {g['person_name']}")
    print("--- Raw ---")
    print(g['raw_text'][:500])
    print("--- Heuristically Punctuted ---")
    print(clean_and_punctuate(g['raw_text'])[:500])
