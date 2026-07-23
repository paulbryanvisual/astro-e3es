import urllib.request
import os

images = {
    'stockdale_bg.jpg': 'https://www.e3es.com/wp-content/uploads/2025/10/stockdale.jpg',
    'larry_jones.jpg': 'https://www.e3es.com/wp-content/uploads/2023/12/Larry-Jones-600x450-1-300x225.jpg',
    'rich_gibbens.jpg': 'https://www.e3es.com/wp-content/uploads/2025/09/Richard-Gibbens-600x450-1.jpg',
    'timothy_davis.jpg': 'https://www.e3es.com/wp-content/uploads/2026/01/Timothy-Davis-600x450-1.jpg',
    'ron_mcvey.jpg': 'https://www.e3es.com/wp-content/uploads/2026/01/Ron-McVey-600x450-1.jpg',
    'weimar_well.jpg': 'https://www.e3es.com/wp-content/uploads/2026/03/weimar-well-before-and-after-300x270.jpg'
}

dest_dir = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/water_images'
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
