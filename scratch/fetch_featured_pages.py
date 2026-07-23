import os
import re
import time
import urllib.request

cache_dir = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/live_project_pages_cache'
os.makedirs(cache_dir, exist_ok=True)

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language': 'en-US,en;q=0.5'
}

# Mapping of local client slugs to live site project-item slugs
slug_mappings = {
    'goodall-witcher-hospital': 'gwh',
    'houston-community-college': 'houston-cc',
    'cooke-county': 'cooke-county-2',
    'donna-isd': 'donna-isd-2',
    'caldwell-isd': 'caldwell-isd-2',
    'carrizo-springs-cisd': 'carrizo-springs-consolidated-isd',
    'manor-isd': 'manor-isd-2',
}

def get_live_slug(local_slug):
    if local_slug in slug_mappings:
        return slug_mappings[local_slug]
    return local_slug

print("Fetching clients index page to list live featured project slugs...")
try:
    req = urllib.request.Request('https://www.e3es.com/clients/', headers=headers)
    with urllib.request.urlopen(req, timeout=15) as response:
        html = response.read().decode('utf-8')
    
    live_links = re.findall(r'href="https://www.e3es.com/projects-item/([^"/]+)/"', html)
    live_slugs = list(set(live_links))
    print(f"Found {len(live_slugs)} live project slugs: {live_slugs}")
except Exception as e:
    print(f"Error fetching clients index: {e}")
    live_slugs = []

# List of local clients
local_clients = [
    'boyd-isd', 'bryan-isd', 'caldwell-isd', 'carrizo-springs-cisd', 'cooke-county',
    'donna-isd', 'edcouch-elsa-isd', 'ferris-isd', 'glen-rose-medical-center',
    'goodall-witcher-hospital', 'granbury-isd', 'greenville-isd', 'hondo-isd',
    'houston-community-college', 'kountze-isd', 'lake-worth-isd', 'manor-isd',
    'mercedes-isd', 'needville-isd', 'port-neches-groves-isd', 'prosper-isd',
    'raymondville-isd', 'ricardo-isd', 'rio-hondo-isd', 'royal-isd', 'sanger-isd'
]

success_count = 0
for local_slug in local_clients:
    live_slug = get_live_slug(local_slug)
    cache_file = os.path.join(cache_dir, f"{local_slug}.html")
    
    # Check if we already have a valid cache (>10KB)
    if os.path.exists(cache_file) and os.path.getsize(cache_file) > 10000:
        print(f"[{local_slug}] Already cached (size: {os.path.getsize(cache_file)} bytes)")
        success_count += 1
        continue
        
    url = f"https://www.e3es.com/projects-item/{live_slug}/"
    print(f"Fetching: {url} -> {local_slug}.html")
    
    try:
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=15) as response:
            content = response.read()
            
        # Check if we got a Cloudflare challenge
        html_text = content.decode('utf-8', errors='ignore')
        if 'Client Challenge' in html_text or 'cloudflare' in html_text.lower():
            print(f"  [WARNING] Cloudflare challenge triggered for {local_slug}!")
            # Save it temporarily but don't count as success
            with open(cache_file, 'wb') as f:
                f.write(content)
        else:
            with open(cache_file, 'wb') as f:
                f.write(content)
            print(f"  Saved {len(content)} bytes.")
            success_count += 1
    except Exception as e:
        print(f"  Error: {e}")
        
    # Sleep to avoid rate limiting
    time.sleep(2)

print(f"\nFetch complete! Successfully cached {success_count} / {len(local_clients)} pages.")
