import bs4

html_path = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/live_water.html'
with open(html_path, 'r', encoding='utf-8') as f:
    html_content = f.read()

soup = bs4.BeautifulSoup(html_content, 'html.parser')
container = soup.find(class_='e3-page-container')

if not container:
    print("Container not found")
    exit()

rows = container.find_all(class_='vc_row')
row1 = rows[1]

# Find columns inside row 1
cols = row1.find_all(class_='wpb_column', recursive=False)
if not cols:
    # Try finding inside vc_row-fluid
    cols = row1.find_all(class_='vc_column_container')

print(f"Found {len(cols)} columns in Row 1.")

for idx, col in enumerate(cols):
    print(f"\n================ COLUMN {idx} ================")
    # Print the hierarchy of tags and their texts
    for elem in col.find_all(['h3', 'h4', 'h5', 'p', 'a', 'li']):
        name = elem.name
        text = elem.get_text(strip=True)
        if text:
            href = elem.get('href')
            print(f"[{name}]: {text}")
            if href:
                print(f"  -> Href: {href}")
