import json
import re
import sys

def is_emoji(char):
    cp = ord(char)
    return (
        (0x1F300 <= cp <= 0x1F9FF) or
        (0x1FA70 <= cp <= 0x1FAFF) or
        (0x2600 <= cp <= 0x27BF) or
        (0x1F1E6 <= cp <= 0x1F1FF)
    )

def split_sentences(text):
    abbrevs = {
        r'\bMr\.': 'Mr_DOT_',
        r'\bMrs\.': 'Mrs_DOT_',
        r'\bMs\.': 'Ms_DOT_',
        r'\bDr\.': 'Dr_DOT_',
        r'\bProf\.': 'Prof_DOT_',
        r'\bSr\.': 'Sr_DOT_',
        r'\bJr\.': 'Jr_DOT_',
        r'\bCo\.': 'Co_DOT_',
        r'\bCorp\.': 'Corp_DOT_',
        r'\bInc\.': 'Inc_DOT_',
        r'\bLtd\.': 'Ltd_DOT_',
        r'\bvs\.': 'vs_DOT_',
        r'\be\.g\.': 'eg_DOT_DOT_',
        r'\bi\.e\.': 'ie_DOT_DOT_',
        r'\bSt\.': 'St_DOT_',
        r'\bAssoc\.': 'Assoc_DOT_',
        r'\bDept\.': 'Dept_DOT_',
        r'\bSupt\.': 'Supt_DOT_',
    }
    temp_text = text
    # Protect decimals
    temp_text = re.sub(r'(\d+)\.(\d+)', r'\1_DECIMALDOT_\2', temp_text)
    for abbrev, placeholder in abbrevs.items():
        temp_text = re.sub(abbrev, placeholder, temp_text)
    
    # Split using lookbehind for .!? or .!? followed by single/double quote
    sentences = re.split(r'(?<=[.!?])\s+|(?<=[.!?][\'\x22])\s+', temp_text)
    
    restored_sentences = []
    for sent in sentences:
        s = sent
        for abbrev, placeholder in abbrevs.items():
            literal_key = abbrev.replace(r'\b', '').replace(r'\.', '.')
            s = s.replace(placeholder, literal_key)
        s = re.sub(r'(\d+)_DECIMALDOT_(\d+)', r'\1.\2', s)
        s = s.strip()
        if s:
            restored_sentences.append(s)
    return restored_sentences

def validate(file_path):
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception as e:
        print(f"Error parsing JSON file {file_path}: {e}")
        return False

    if not isinstance(data, list):
        print(f"JSON root is not a list. Type is {type(data)}.")
        return False

    required_keys = {'video', 'person_id', 'person_name', 'quote_ids', 'cleaned_text'}
    failures = []

    for idx, item in enumerate(data):
        item_errors = []
        if not isinstance(item, dict):
            item_errors.append("Item is not an object/dictionary.")
            failures.append((idx, item, item_errors))
            continue

        # Check keys
        keys = set(item.keys())
        missing = required_keys - keys
        if missing:
            item_errors.append(f"Missing keys: {missing}")

        cleaned_text = item.get('cleaned_text')
        if cleaned_text is None:
            item_errors.append("Missing key: 'cleaned_text'")
        elif not isinstance(cleaned_text, str):
            item_errors.append(f"'cleaned_text' is not a string. Type: {type(cleaned_text)}")
        else:
            # Check sentence count
            sentences = split_sentences(cleaned_text)
            sent_count = len(sentences)
            if sent_count < 1 or sent_count > 2:
                item_errors.append(f"Sentence count is {sent_count} (must be 1 or 2). Sentences: {sentences}")

            # Check emojis
            emojis = [c for c in cleaned_text if is_emoji(c)]
            if emojis:
                item_errors.append(f"Contains emojis: {''.join(emojis)}")

        if item_errors:
            failures.append((idx, item, item_errors))

    if failures:
        print(f"Validation FAILED! Found {len(failures)} invalid items out of {len(data)} items total.\n")
        for idx, item, errors in failures:
            person = item.get('person_name', 'Unknown') if isinstance(item, dict) else 'Unknown'
            video = item.get('video', 'Unknown') if isinstance(item, dict) else 'Unknown'
            print(f"--- Item {idx} (Person: {person}, Video: {video}) ---")
            for err in errors:
                print(f"  * {err}")
            if isinstance(item, dict) and 'cleaned_text' in item:
                print(f"  Text: \"{item['cleaned_text']}\"")
            print()
        return False
    else:
        print("Validation PASSED! All items conform to rules.")
        return True

if __name__ == '__main__':
    file_path = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/key_clean_quotes_batch1.json'
    success = validate(file_path)
    sys.exit(0 if success else 1)
