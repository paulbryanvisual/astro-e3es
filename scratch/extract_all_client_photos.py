import os
import re
import zipfile
import shutil
import fitz  # PyMuPDF

ref_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets'
word_new_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Word/clients/New'
word_prev_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Word/clients/Previous'

# Normalization map for slugs to titles
local_clients = [
    ('Bishop CISD', 'bishop-cisd'),
    ('Ennis ISD', 'ennis-isd'),
    ('Little Elm ISD', 'little-elm-isd'),
    ('Plano ISD', 'plano-isd'),
    ('West Hardin CCISD', 'west-hardin-ccisd'),
    ('Edgewood ISD', 'edgewood-isd'),
    ('Gainesville ISD', 'gainesville-isd'),
    ('Galena Park ISD', 'galena-park-isd'),
    ('Gruver ISD', 'gruver-isd'),
    ('Hardin County', 'hardin-county'),
    ('Hardin-Jefferson ISD', 'hardin-jefferson-isd'),
    ('Hawkins ISD', 'hawkins-isd'),
    ('IDEA Public Schools', 'idea-public-schools'),
    ('Ingram ISD', 'ingram-isd'),
    ('Italy ISD', 'italy-isd'),
    ('Jasper ISD', 'jasper-isd'),
    ('Katy ISD', 'katy-isd'),
    ('Lancaster ISD', 'lancaster-isd'),
    ('Liberty ISD', 'liberty-isd'),
    ('Llano ISD', 'llano-isd'),
    ('Lubbock ISD', 'lubbock-isd'),
    ('Lyford ISD', 'lyford-isd'),
    ('Marble Falls ISD', 'marble-falls-isd'),
    ('Mesquite ISD', 'mesquite-isd'),
    ('Moulton ISD', 'moulton-isd'),
    ('Nacogdoches ISD', 'nacogdoches-isd'),
    ('New Boston ISD', 'new-boston-isd'),
    ('Nocona ISD', 'nocona-isd'),
    ('Normangee ISD', 'normangee-isd'),
    ('North Texas Medical Center', 'north-texas-medical-center'),
    ('Odem-Edroy ISD', 'odem-edroy-isd'),
    ('Pecos ISD', 'pecos-isd'),
    ('Pilot Point ISD', 'pilot-point-isd'),
    ('Poolville ISD', 'poolville-isd'),
    ('Robstown ISD', 'robstown-isd'),
    ('Roscoe Collegiate ISD', 'roscoe-collegiate-isd'),
    ('Rusk ISD', 'rusk-isd'),
    ('Saint Jo ISD', 'saint-jo-isd'),
    ('San Benito CISD', 'san-benito-cisd'),
    ('San Jacinto Community College', 'san-jacinto-community-college'),
    ('Santa Fe ISD', 'santa-fe-isd'),
    ('Silsbee ISD', 'silsbee-isd'),
    ('Skidmore-Tynan ISD', 'skidmore-tynan-isd'),
    ('Texas Facilities Commission', 'texas-facilities-commission'),
    ('Tom Bean ISD', 'tom-bean-isd'),
    ('Trenton ISD', 'trenton-isd'),
    ('Trinity ISD', 'trinity-isd'),
    ('Valley View ISD', 'valley-view-isd'),
    ('Vernon ISD', 'vernon-isd'),
    ('Waxahachie ISD', 'waxahachie-isd'),
    ('Weslaco ISD', 'weslaco-isd'),
    ('Woodville ISD', 'woodville-isd'),
    ('Anderson-Shiro CISD', 'anderson-shiro-cisd'),
    ('Bellevue ISD', 'bellevue-isd'),
    ('Big Sandy ISD', 'big-sandy-isd'),
    ('Bowie ISD', 'bowie-isd'),
    ('Brenham ISD', 'brenham-isd'),
    ('Brownsville ISD', 'brownsville-isd'),
    ('Caddo Mills ISD', 'caddo-mills-isd'),
    ('Castleberry ISD', 'castleberry-isd'),
    ('Cedar Hill ISD', 'cedar-hill-isd'),
    ('Chico ISD', 'chico-isd'),
    ('Cleveland ISD', 'cleveland-isd'),
    ('Columbia-Brazoria ISD', 'columbia-brazoria-isd'),
    ('Corsicana ISD', 'corsicana-isd'),
    ('DeSoto ISD', 'desoto-isd')
]

# Map Reference Sheet Folders fuzzy
ref_folders = os.listdir(ref_dir)
def get_ref_folder(title):
    title_clean = re.sub(r'[^a-z0-9]', '', title.lower().replace('isd', '').replace('cisd', '').replace('ccisd', ''))
    for folder in ref_folders:
        folder_clean = re.sub(r'[^a-z0-9]', '', folder.lower().replace('isd', '').replace('cisd', '').replace('ccisd', ''))
        if title_clean == folder_clean or title_clean in folder_clean or folder_clean in title_clean:
            return os.path.join(ref_dir, folder)
    return os.path.join(ref_dir, title) # Fallback

def extract_from_docx(docx_path, out_images_dir, slug):
    extracted = []
    if not os.path.exists(docx_path):
        return extracted
    try:
        with zipfile.ZipFile(docx_path) as z:
            media_files = [f for f in z.namelist() if f.startswith('word/media/')]
            for name in media_files:
                base = os.path.basename(name)
                # Skip logos or tiny icons (e.g. arrows, checkmarks)
                if 'logo' in base.lower() or 'image1.png' in base.lower() or 'image1.jpg' in base.lower():
                    # Check size before skipping
                    info = z.getinfo(name)
                    if info.file_size < 15000: # <15KB is likely a tiny layout asset
                        continue
                
                dest = os.path.join(out_images_dir, f"{slug}-extracted-docx-{base}")
                with z.open(name) as src, open(dest, 'wb') as dst:
                    shutil.copyfileobj(src, dst)
                # Verify size
                if os.path.getsize(dest) > 15000:
                    extracted.append(dest)
                else:
                    os.remove(dest)
    except Exception as e:
        print(f"  Error unzipping {docx_path}: {e}")
    return extracted

def extract_from_pdf(pdf_path, out_images_dir, slug):
    extracted = []
    if not os.path.exists(pdf_path):
        return extracted
    try:
        doc = fitz.open(pdf_path)
        img_idx = 1
        for page_index in range(len(doc)):
            page = doc[page_index]
            image_list = page.get_images()
            for img in image_list:
                xref = img[0]
                base_image = doc.extract_image(xref)
                image_bytes = base_image["image"]
                image_ext = base_image["ext"]
                
                # Filter out small graphics (<20KB)
                if len(image_bytes) < 20000:
                    continue
                    
                dest = os.path.join(out_images_dir, f"{slug}-extracted-pdf-image{img_idx}.{image_ext}")
                with open(dest, "wb") as f:
                    f.write(image_bytes)
                extracted.append(dest)
                img_idx += 1
    except Exception as e:
        print(f"  Error reading PDF {pdf_path}: {e}")
    return extracted

print("📷 Starting raw photo extraction...")

summary = {}

for title, slug in local_clients:
    print(f"Processing: {title} ({slug})...")
    
    # Target directory in Reference Sheets
    client_folder = get_ref_folder(title)
    images_dir = os.path.join(client_folder, 'images')
    os.makedirs(images_dir, exist_ok=True)
    
    extracted_photos = []
    
    # 1. Try DOCX New
    docx_new = os.path.join(word_new_dir, f"{slug}.docx")
    extracted_photos = extract_from_docx(docx_new, images_dir, slug)
    
    # 2. Try DOCX Previous if empty
    if not extracted_photos:
        docx_prev = os.path.join(word_prev_dir, f"{slug}.docx")
        extracted_photos = extract_from_docx(docx_prev, images_dir, slug)
        
    # 3. Try PDF inside Reference Sheet folder if still empty
    if not extracted_photos:
        # Search for PDF files
        if os.path.exists(client_folder):
            pdf_files = [f for f in os.listdir(client_folder) if f.lower().endswith('.pdf')]
            for pdf in pdf_files:
                pdf_path = os.path.join(client_folder, pdf)
                extracted_photos.extend(extract_from_pdf(pdf_path, images_dir, slug))
                
    # Filter/clean extracted files, sorting by size (largest first)
    if extracted_photos:
        extracted_photos = list(set(extracted_photos))
        extracted_photos.sort(key=os.path.getsize, reverse=True)
        summary[slug] = extracted_photos
        print(f"  Successfully extracted {len(extracted_photos)} raw photos.")
    else:
        print(f"  [WARNING] No raw photos extracted.")
        summary[slug] = []

print("\n🏁 Extraction complete summary:")
missing_count = 0
for slug, photos in sorted(summary.items()):
    if not photos:
        print(f"  - {slug}: MISSING")
        missing_count += 1
    else:
        print(f"  - {slug}: {len(photos)} photos (largest: {os.path.basename(photos[0])} - {round(os.path.getsize(photos[0])/1024, 1)} KB)")

print(f"\nTotal client slugs analyzed: {len(local_clients)}")
print(f"Successfully extracted photos for: {len(local_clients) - missing_count}")
print(f"Failed/Missing: {missing_count}")
