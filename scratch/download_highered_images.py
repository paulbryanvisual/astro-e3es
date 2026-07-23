import urllib.request
import os

images = {
    'highered_bg.jpg': 'https://www.e3es.com/wp-content/uploads/2026/03/E3_higherEd_sm.jpg',
    'b_mund.jpg': 'https://www.e3es.com/wp-content/uploads/2026/01/B-Mund.jpg',
    'dan_schmitz.jpg': 'https://www.e3es.com/wp-content/uploads/2026/01/Dan-Schmitz-600x450-1.jpg',
    'bca.jpg': 'https://www.e3es.com/wp-content/uploads/2026/03/BCA-1024x684.jpg',
    'buyboard.png': 'https://www.e3es.com/wp-content/uploads/2026/03/images.png',
    'tips.png': 'https://www.e3es.com/wp-content/uploads/2026/03/TIPS-TC-Logo-300x132-1.png'
}

dest_dir = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/highered_images'
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
