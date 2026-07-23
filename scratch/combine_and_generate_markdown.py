import json
import os

# Load all cleaned quote JSONs
with open('/Users/bryanpaul/Local Sites/astro-e3es/scratch/key_clean_quotes_batch1.json', 'r') as f:
    batch1 = json.load(f)

with open('/Users/bryanpaul/Local Sites/astro-e3es/scratch/key_clean_quotes_batch2.json', 'r') as f:
    batch2 = json.load(f)

with open('/Users/bryanpaul/Local Sites/astro-e3es/scratch/non_key_clean_quotes.json', 'r') as f:
    non_key = json.load(f)

# Load raw merged quotes for reference comparison
with open('/Users/bryanpaul/Local Sites/astro-e3es/scratch/raw_merged_quotes.json', 'r') as f:
    raw_quotes = json.load(f)

raw_lookup = {g['video'] + '|||' + str(g['person_id']): g['raw_text'] for g in raw_quotes}

# Combine and normalize batch formats
combined = []
for item in batch1 + batch2:
    key = item['video'] + '|||' + str(item['person_id'])
    raw_text = raw_lookup.get(key, "")
    combined.append({
        'video': item['video'],
        'person_id': item['person_id'],
        'person_name': item['person_name'],
        'quote_ids': item['quote_ids'],
        'raw_text': raw_text,
        'cleaned_text': item['cleaned_text'],
        'is_key': True
    })

for item in non_key:
    key = item['video'] + '|||' + str(item['person_id'])
    raw_text = raw_lookup.get(key, "")
    combined.append({
        'video': item['video'],
        'person_id': item['person_id'],
        'person_name': item['person_name'],
        'quote_ids': item['quote_ids'],
        'raw_text': raw_text,
        'cleaned_text': item['cleaned_text'],
        'is_key': False
    })

# Save combined clean quotes
with open('/Users/bryanpaul/Local Sites/astro-e3es/scratch/cleaned_merged_quotes.json', 'w') as f:
    json.dump(combined, f, indent=4)

print(f"Combined {len(combined)} groups in total.")

# Generate proposed_merged_quotes.md for User Review
md_path = '/Users/bryanpaul/.gemini/antigravity/brain/fd3a018d-6d66-4014-a832-26235d4188b8/proposed_merged_quotes.md'

with open(md_path, 'w') as f:
    f.write("# Proposed Merged and Cleaned Quotes for User Review\n\n")
    f.write("Below is the complete list of proposed merged and copywritten quotes, grouped by video and speaker. The raw transcription segment texts have been joined chronologically, with sentence boundary restoration, punctuation addition, and phonetic typo corrections applied.\n\n")
    
    # 1. Key Testimonials Section
    f.write("## Section 1 — Key Client Testimonials & Case Studies\n\n")
    f.write("These represent K-12 client stories, case studies, and superintendent testimonials that E3 would prominently display on its website. They have been cleaned and polished by professional copywriter subagents.\n\n")
    
    key_items = [item for item in combined if item['is_key']]
    for i, item in enumerate(key_items):
        f.write(f"### {i+1}. {item['person_name']} on \"{item['video']}\"\n")
        f.write(f"- **Video Title**: {item['video']}\n")
        f.write(f"- **Speaker**: {item['person_name']} (ID: {item['person_id']})\n")
        f.write(f"- **Original Quote Count**: {len(item['quote_ids'])} segments\n\n")
        f.write("> [!NOTE]\n")
        f.write(f"> **Raw Combined Transcript**:\n")
        f.write(f"> {item['raw_text']}\n\n")
        f.write("> [!TIP]\n")
        f.write(f"> **Proposed Testimonial Quote**:\n")
        f.write(f"> {item['cleaned_text']}\n\n")
        f.write("---\n\n")
        
    f.write("## Section 2 — Internal & Slide Presentation Quotes\n\n")
    f.write("These represent internal training, webinars, or long presentation slideshows. They have been cleaned, capitalized, and limited to exactly 1 or 2 sentences maximum for a concise and direct layout.\n\n")
    
    non_key_items = [item for item in combined if not item['is_key']]
    for i, item in enumerate(non_key_items):
        f.write(f"### {i+1}. {item['person_name']} on \"{item['video']}\"\n")
        f.write(f"- **Video Title**: {item['video']}\n")
        f.write(f"- **Speaker**: {item['person_name']} (ID: {item['person_id']})\n")
        f.write(f"- **Original Quote Count**: {len(item['quote_ids'])} segments\n\n")
        f.write("> [!NOTE]\n")
        f.write(f"> **Raw Combined Transcript**:\n")
        f.write(f"> {item['raw_text']}\n\n")
        f.write("> [!TIP]\n")
        f.write(f"> **Proposed Cleaned Text**:\n")
        f.write(f"> {item['cleaned_text']}\n\n")
        f.write("---\n\n")

print(f"Generated proposed_merged_quotes.md at {md_path}")
