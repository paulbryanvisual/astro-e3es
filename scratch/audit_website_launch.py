import os
import re
import urllib.request
import urllib.parse
from html.parser import HTMLParser

class AuditParser(HTMLParser):
    def __init__(self, current_file_path, dist_root):
        super().__init__()
        self.current_file_path = current_file_path
        self.dist_root = dist_root
        self.links = []
        self.images = []
        self.headings = []
        self.forms = []
        self.has_main = False
        self.active_tag = None
        self.current_form = None

    def handle_starttag(self, tag, attrs):
        attrs_dict = dict(attrs)
        self.active_tag = tag

        if tag == 'main':
            self.has_main = True

        elif tag == 'a':
            if 'href' in attrs_dict:
                self.links.append({
                    'href': attrs_dict['href'],
                    'line': self.getpos()[0],
                    'text': ''
                })

        elif tag == 'img':
            self.images.append({
                'src': attrs_dict.get('src', ''),
                'alt': attrs_dict.get('alt', None),
                'line': self.getpos()[0]
            })

        elif tag in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']:
            self.headings.append({
                'level': int(tag[1]),
                'line': self.getpos()[0],
                'text': ''
            })

        elif tag == 'form':
            self.current_form = {
                'id': attrs_dict.get('id', ''),
                'inputs': [],
                'labels': []
            }
            self.forms.append(self.current_form)

        elif tag in ['input', 'select', 'textarea']:
            if self.current_form is not None:
                self.current_form['inputs'].append({
                    'type': attrs_dict.get('type', tag),
                    'id': attrs_dict.get('id', ''),
                    'name': attrs_dict.get('name', ''),
                    'line': self.getpos()[0]
                })

        elif tag == 'label':
            if self.current_form is not None:
                self.current_form['labels'].append({
                    'for': attrs_dict.get('for', ''),
                    'line': self.getpos()[0]
                })

    def handle_endtag(self, tag):
        if tag == 'form':
            self.current_form = None
        self.active_tag = None

    def handle_data(self, data):
        if self.active_tag == 'a' and self.links:
            self.links[-1]['text'] += data
        elif self.active_tag in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] and self.headings:
            self.headings[-1]['text'] += data

def check_external_url(url):
    try:
        req = urllib.request.Request(
            url, 
            headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'},
            method='HEAD'
        )
        with urllib.request.urlopen(req, timeout=3) as resp:
            return resp.status in [200, 301, 302, 303, 307, 308]
    except Exception:
        # Retry with GET in case HEAD is blocked
        try:
            req = urllib.request.Request(
                url, 
                headers={'User-Agent': 'Mozilla/5.0'},
                method='GET'
            )
            with urllib.request.urlopen(req, timeout=3) as resp:
                return resp.status in [200, 301, 302]
        except Exception:
            return False

def audit_site():
    dist_root = '/Users/bryanpaul/Local Sites/astro-e3es/dist'
    if not os.path.exists(dist_root):
        print(f"Error: dist folder not found at {dist_root}. Run npm run build first.")
        return

    html_files = []
    for root, dirs, files in os.walk(dist_root):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))

    print(f"Found {len(html_files)} HTML pages to audit.")

    audit_results = {}
    external_links_cache = {}

    for file_path in html_files:
        rel_url = '/' + os.path.relpath(file_path, dist_root)
        if rel_url.endswith('/index.html'):
            rel_url = rel_url[:-10]
        elif rel_url.endswith('.html'):
            rel_url = rel_url[:-5]
        if not rel_url:
            rel_url = '/'

        # Skip trashed pages
        if '__trashed' in rel_url:
            continue

        print(f"Auditing page: {rel_url}...")

        with open(file_path, 'r', encoding='utf-8') as f:
            html_content = f.read()

        parser = AuditParser(file_path, dist_root)
        parser.feed(html_content)

        page_issues = {
            'broken_links': [],
            'broken_images': [],
            'a11y_warnings': []
        }

        # 1. Check Links
        for link in parser.links:
            href = link['href'].strip()
            link_text = link['text'].strip().replace('\n', ' ')

            if not href or href == '#':
                page_issues['a11y_warnings'].append(
                    f"Line {link['line']}: Link \"{link_text}\" has empty or placeholder href (href=\"{href}\")."
                )
                continue

            if href.startswith('mailto:') or href.startswith('tel:'):
                continue

            # Parse URL
            parsed = urllib.parse.urlparse(href)
            if parsed.scheme in ['http', 'https']:
                # External Link
                if href not in external_links_cache:
                    print(f"  Checking external link: {href}...")
                    external_links_cache[href] = check_external_url(href)
                if not external_links_cache[href]:
                    page_issues['broken_links'].append(
                        f"Line {link['line']}: Broken external link \"{link_text}\" to {href}"
                    )
            else:
                # Internal Link
                path = parsed.path
                if not path.startswith('/'):
                    # Relative link, resolve against current page path
                    current_dir = os.path.dirname(os.path.relpath(file_path, dist_root))
                    path = '/' + os.path.normpath(os.path.join(current_dir, path))

                # Normalize path (e.g. check for index.html)
                clean_path = path.rstrip('/')
                file_target = None
                
                # Check options:
                # A: dist/clean_path/index.html
                # B: dist/clean_path.html
                # C: dist/clean_path
                opt_a = os.path.join(dist_root, clean_path.lstrip('/'), 'index.html')
                opt_b = os.path.join(dist_root, clean_path.lstrip('/') + '.html')
                opt_c = os.path.join(dist_root, clean_path.lstrip('/'))

                if os.path.exists(opt_a) and os.path.isfile(opt_a):
                    file_target = opt_a
                elif os.path.exists(opt_b) and os.path.isfile(opt_b):
                    file_target = opt_b
                elif os.path.exists(opt_c) and os.path.isfile(opt_c):
                    file_target = opt_c

                if not file_target and clean_path != '':
                    page_issues['broken_links'].append(
                        f"Line {link['line']}: Broken internal link \"{link_text}\" to {href} (target not found in dist)"
                    )

        # 2. Check Images
        for img in parser.images:
            src = img['src'].strip()
            alt = img['alt']

            if alt is None:
                page_issues['a11y_warnings'].append(
                    f"Line {img['line']}: Image {src} is missing alt attribute."
                )
            elif alt.strip() == '':
                # Decorative image, allowed but log as info
                pass

            if not src:
                page_issues['broken_images'].append(
                    f"Line {img['line']}: Image has empty src attribute."
                )
                continue

            parsed_src = urllib.parse.urlparse(src)
            if parsed_src.scheme in ['http', 'https']:
                # External image
                if src not in external_links_cache:
                    external_links_cache[src] = check_external_url(src)
                if not external_links_cache[src]:
                    page_issues['broken_images'].append(
                        f"Line {img['line']}: Broken external image src: {src}"
                    )
            else:
                # Local image
                clean_src = parsed_src.path.lstrip('/')
                local_img_path = os.path.join(dist_root, clean_src)
                if not os.path.exists(local_img_path):
                    # Check in public folder just in case
                    public_img_path = os.path.join('/Users/bryanpaul/Local Sites/astro-e3es/public', clean_src)
                    if not os.path.exists(public_img_path):
                        page_issues['broken_images'].append(
                            f"Line {img['line']}: Local image file not found: /{clean_src}"
                        )

        # 3. Check Headings Hierarchy
        if not parser.headings:
            page_issues['a11y_warnings'].append("Page has no heading tags (h1-h6).")
        else:
            prev_level = 0
            has_h1 = False
            for heading in parser.headings:
                level = heading['level']
                text = heading['text'].strip()
                if level == 1:
                    has_h1 = True
                
                # Check for skipped levels
                if prev_level > 0 and level > prev_level + 1:
                    page_issues['a11y_warnings'].append(
                        f"Line {heading['line']}: Skipped heading level from H{prev_level} to H{level} (\"{text}\")."
                    )
                prev_level = level
            
            if not has_h1:
                page_issues['a11y_warnings'].append("Page is missing an H1 main heading.")

        # 4. Check main tag
        if not parser.has_main:
            page_issues['a11y_warnings'].append("Page is missing a <main> landmark element.")

        # 5. Check forms labels
        for form in parser.forms:
            input_ids = [inp['id'] for inp in form['inputs'] if inp['id'] and inp['type'] not in ['submit', 'button', 'hidden']]
            label_fors = [lbl['for'] for lbl in form['labels'] if lbl['for']]
            
            for inp in form['inputs']:
                if inp['type'] in ['submit', 'button', 'hidden']:
                    continue
                inp_id = inp['id']
                if not inp_id:
                    page_issues['a11y_warnings'].append(
                        f"Line {inp['line']}: Form input of type \"{inp['type']}\" is missing an id attribute."
                    )
                elif inp_id not in label_fors:
                    page_issues['a11y_warnings'].append(
                        f"Line {inp['line']}: Form input with id \"{inp_id}\" has no matching <label for=\"{inp_id}\">."
                    )

        # Only add pages with issues
        if page_issues['broken_links'] or page_issues['broken_images'] or page_issues['a11y_warnings']:
            audit_results[rel_url] = page_issues

    # Write Markdown Report
    report_path = '/Users/bryanpaul/Local Sites/astro-e3es/docs/website_launch_audit_report.md'
    os.makedirs(os.path.dirname(report_path), exist_ok=True)
    
    with open(report_path, 'w', encoding='utf-8') as f:
        f.write("# Website Launch Quality & Accessibility Audit Report\n\n")
        f.write(f"This report lists all detected broken links, broken images, and WCAG/a11y warnings across all compiled page templates in the `dist` directory.\n\n")
        f.write("## Summary of Findings\n\n")
        
        total_pages_with_issues = len(audit_results)
        f.write(f"- **Total Pages Audited**: {len(html_files)}\n")
        f.write(f"- **Pages with Warnings/Errors**: {total_pages_with_issues}\n\n")
        
        if total_pages_with_issues == 0:
            f.write("### ✅ All Pages Passed! No broken links, broken images, or a11y violations found.\n\n")
        else:
            f.write("## Detailed Audit Log by Page\n\n")
            for page, issues in audit_results.items():
                f.write(f"### 📄 Page: `{page}`\n\n")
                
                if issues['broken_links']:
                    f.write("#### 🔗 Broken Links\n")
                    for issue in issues['broken_links']:
                        f.write(f"- [ ] {issue}\n")
                    f.write("\n")
                
                if issues['broken_images']:
                    f.write("#### 🖼️ Broken Images\n")
                    for issue in issues['broken_images']:
                        f.write(f"- [ ] {issue}\n")
                    f.write("\n")
                
                if issues['a11y_warnings']:
                    f.write("#### ⚠️ Accessibility (a11y) Warnings\n")
                    for issue in issues['a11y_warnings']:
                        f.write(f"- [ ] {issue}\n")
                    f.write("\n")
                f.write("---\n\n")
                
    print(f"Successfully generated launch audit report at: {report_path}")

if __name__ == '__main__':
    audit_site()
