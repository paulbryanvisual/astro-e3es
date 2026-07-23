import csv
import re

quotes = []
with open('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/quotes.csv', 'r') as f:
    reader = csv.reader(f)
    for row in reader:
        if len(row) >= 5:
            # speaker_name, quote_text, video_title
            quotes.append({
                'speaker': row[1],
                'text': row[2],
                'video': row[3]
            })

# Filter for Dr. Theresa Williams
target_quotes = [q['text'] for q in quotes if 'Theresa Williams' in q['video'] or q['speaker'] == 'Dr. Theresa Williams' or q['speaker'] == 'Benerre Ader']

print(f"Found {len(target_quotes)} quotes for Dr. Theresa Williams.")

# Merge them
merged_text = " ".join(target_quotes)

# Heuristic cleanup
def clean_transcript(text):
    # Remove double spaces
    text = re.sub(r'\s+', ' ', text)
    # Capitalize ' i ' or ' i\''
    text = re.sub(r'\bi\b', 'I', text)
    text = re.sub(r"\bi'm\b", "I'm", text)
    text = re.sub(r"\bi've\b", "I've", text)
    text = re.sub(r"\bi'd\b", "I'd", text)
    text = re.sub(r"\bi'll\b", "I'll", text)
    
    # Capitalize the first letter of the text
    if text:
        text = text[0].upper() + text[1:]
        
    return text

print("\n--- Raw Merged ---")
print(merged_text[:1000])

print("\n--- Cleaned Merged ---")
print(clean_transcript(merged_text)[:1000])
