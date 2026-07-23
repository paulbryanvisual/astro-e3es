import urllib.request
import os

images = {
    'operation_room.jpg': 'https://www.e3es.com/wp-content/uploads/2024/08/Operation-room.jpg',
    'e3_torch_logo.png': 'https://www.e3es.com/wp-content/uploads/2024/08/E3_TORCH_2024-300x114-1.png'
}

dest_dir = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/torch_images'
os.makedirs(dest_dir, exist_ok=True)

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}

for name, url in images.items():
    dest_path = os.path.join(dest_dir, name)
    print(f"Downloading {url} -> {dest_path}")
    req = urllib.request.Request(url, headers=headers)
    try:
        with urllib.request.urlopen(req) as res:
            with open(dest_path, 'wb') as f:
                f.write(res.read())
            print("  Success!")
    except Exception as e:
        print(f"  Error: {e}")
