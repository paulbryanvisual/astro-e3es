import json

with open('scratch/key_raw_quotes.json', 'r') as f:
    key_groups = json.load(f)

half = len(key_groups) // 2

batch1 = key_groups[:half]
batch2 = key_groups[half:]

with open('scratch/key_raw_quotes_batch1.json', 'w') as f:
    json.dump(batch1, f, indent=4)

with open('scratch/key_raw_quotes_batch2.json', 'w') as f:
    json.dump(batch2, f, indent=4)

print(f"Split {len(key_groups)} groups into batch1 ({len(batch1)}) and batch2 ({len(batch2)}).")
