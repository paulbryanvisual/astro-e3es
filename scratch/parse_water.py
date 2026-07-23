import bs4

html_path = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/live_water.html'
with open(html_path, 'r', encoding='utf-8') as f:
    html_content = f.read()

soup = bs4.BeautifulSoup(html_content, 'html.parser')
container = soup.find(class_='e3-page-container')

if container:
    print("Found container!")
    # Let's inspect everything inside the container
    # We can print all headers and the paragraphs following them
    current_element = container.find(class_='vc_row')
    # Let's just traverse and print the text hierarchy inside Row 1
    row1 = container.find_all(class_='vc_row')[1]
    
    # We want to understand the structure of Row 1: Columns, text blocks, etc.
    # Visual Composer uses vc_col-sm-X classes. Let's find all column divs.
    cols = row1.find_all(class_='wpb_column')
    print(f"Found {len(cols)} columns inside Row 1.")
    for j, col in enumerate(cols):
        classes = col.get('class', [])
        # Only print direct columns of Row 1 (not nested ones, if any)
        if 'vc_inner' not in str(col.parent.get('class')):
            print(f"\n===== Column {j} classes: {classes} =====")
            # Print all headings and text paragraphs inside this column
            for elem in col.find_all(['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'ul', 'li', 'a']):
                # Only print if it's a direct child of some content wrapper to avoid duplicate text from nested tags
                if elem.name in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'a']:
                    print(f"  [{elem.name}]: {elem.get_text(strip=True)}")
                    if elem.name == 'a' and elem.get('href'):
                        print(f"    Href: {elem.get('href')}")
else:
    print("Container not found.")
