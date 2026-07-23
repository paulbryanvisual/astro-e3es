import os

urls = [
    "http://e3es2026.local/wp-admin/post.php?post=1432&action=edit",
    "http://e3es2026.local/clients/anderson-shiro-cisd/",
    "http://e3es2026.local/clients/boyd-isd/",
    "http://e3es2026.local/clients/desoto-isd/",
    "http://e3es2026.local/clients/chico-isd/",
    "http://e3es2026.local/clients/gainesville-isd/",
    "http://e3es2026.local/clients/woodville-isd/"
]

for url in urls:
    os.system(f'open -a "Google Chrome" "{url}"')
    print(f"Opened: {url}")
