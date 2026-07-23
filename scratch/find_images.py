import bs4

html_path = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/live_water.html'
with open(html_path, 'r', encoding='utf-8') as f:
    html_content = f.read()

soup = bs4.BeautifulSoup(html_content, 'html.parser')

print("=== IMAGE TAGS ===")
for img in soup.find_all('img'):
    print("src:", img.get('src'))
    print("class:", img.get('class'))
    print("alt:", img.get('alt'))

print("\n=== BACKGROUND IMAGES ===")
import re
bg_urls = re.findall(r'url\((.*?)\)', html_content)
for url in set(bg_urls):
    print("Bg URL:", url)
