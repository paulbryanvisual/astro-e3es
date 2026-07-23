import os
import json
import cv2

# Load crop list
with open('/Users/bryanpaul/Local Sites/astro-e3es/scratch/crop_list.json', 'r') as f:
    crop_list = json.load(f)

print(f"Loaded {len(crop_list)} clients to crop layout photos for...")

cropped_count = 0
for client in crop_list:
    slug = client['slug']
    title = client['title']
    layout_file = client['layout_file']
    folder = client['folder']
    
    if not os.path.exists(layout_file):
        print(f"  [WARNING] Layout file not found for {slug}: {layout_file}")
        continue
        
    img = cv2.imread(layout_file)
    if img is None:
        print(f"  [WARNING] Could not read image for {slug}: {layout_file}")
        continue
        
    h, w, c = img.shape
    aspect_ratio = w / h
    
    images_dir = os.path.join(folder, 'images')
    os.makedirs(images_dir, exist_ok=True)
    out_path = os.path.join(images_dir, f"{slug}-cropped-layout-photo.jpg")
    
    if aspect_ratio > 1.2:
        # It's already a landscape photo (e.g. from Flickr or custom crops), do not crop it further
        cv2.imwrite(out_path, img)
        print(f"  [{cropped_count+1}] Copied full landscape photo for {slug} as-is: {out_path} ({w}x{h})")
    else:
        # Standard 18% to 32.8% height crop for vertical document layouts
        ymin = int(0.18 * h)
        ymax = int(0.328 * h)
        xmin = 0
        xmax = w
        
        crop = img[ymin:ymax, xmin:xmax]
        cv2.imwrite(out_path, crop)
        print(f"  [{cropped_count+1}] Cropped layout photo for {slug}: {out_path} ({crop.shape[1]}x{crop.shape[0]})")
        
    cropped_count += 1

print(f"\nDone! Cropped layout photos for {cropped_count} clients.")
