import bs4

html_path = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/live_torch.html'
with open(html_path, 'r', encoding='utf-8') as f:
    html_content = f.read()

soup = bs4.BeautifulSoup(html_content, 'html.parser')

print("=== HEADINGS ===")
for h in soup.find_all(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']):
    print(f"{h.name}: {h.get_text(strip=True)}")

container = soup.find(class_='e3-page-container')
if container:
    print("\nFound container! Children:")
    for child in container.children:
        if child.name:
            print(f"  <{child.name} class='{child.get('class', [])}'>")
            # print snippet
            print("    Text snippet:", child.get_text(strip=True)[:200])
else:
    print("Container not found.")
