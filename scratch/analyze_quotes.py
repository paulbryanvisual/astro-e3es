import json

with open('/Users/bryanpaul/Local Sites/astro-e3es/scratch/quotes_audit.json') as f:
    data = json.load(f)

missing = []
for idx, q in enumerate(data):
    if not q['person_id'] or q['person_id'] == '0' or q['person_name'] == 'MISSING/NOT FOUND':
        missing.append(q)

print(f"Total quotes: {len(data)}")
print(f"Missing person quotes: {len(missing)}")
if missing:
    print("First 10 missing:")
    for m in missing[:10]:
        print(f"ID: {m['id']} - Title: {m['title']}")
