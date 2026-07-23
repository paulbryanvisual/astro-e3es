import os

ref_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets'
results = {}

for root, dirs, files in os.walk(ref_dir):
    for f in files:
        if f.lower().endswith(('.jpg', '.jpeg', '.png', '.webp')):
            # Skip logos if they contain logo in the name
            if 'logo' in f.lower():
                continue
            # Get the relative path folder name
            rel = os.path.relpath(root, ref_dir)
            client_name = rel.split(os.sep)[0]
            if client_name == '.':
                continue
                
            path = os.path.join(root, f)
            if client_name not in results:
                results[client_name] = []
            results[client_name].append(path)

print(f"Scanned Reference Sheets. Found images for {len(results)} clients:")
for client, paths in sorted(results.items()):
    print(f"• {client}:")
    for p in paths[:3]:
        print(f"  - {os.path.basename(p)} ({os.path.getsize(p)} bytes)")
    if len(paths) > 3:
        print(f"  - ... and {len(paths)-3} more")
