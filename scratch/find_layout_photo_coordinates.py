import cv2
import numpy as np

layout_path = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets/Anderson-Shiro CISD/Jason Flowers - Anderson-Shiro CISD.jpg'
raw_path = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets/Anderson-Shiro CISD/images/extracted-docx-image1.png'

print("🔍 Comparing layout and raw photo to find coordinates...")

# Load images using OpenCV
layout_img = cv2.imread(layout_path)
raw_img = cv2.imread(raw_path)

if layout_img is None or raw_img is None:
    print("Error loading images!")
    exit(1)

print("Layout dimensions:", layout_img.shape)
print("Raw photo dimensions:", raw_img.shape)

# Let's resize raw photo if it was resized when embedded.
# InDesign usually scales the photo. Let's find it using template matching at multiple scales,
# or since the photo might be cropped inside InDesign, let's match features or do template matching!
# Let's convert to grayscale
layout_gray = cv2.cvtColor(layout_img, cv2.COLOR_BGR2GRAY)
raw_gray = cv2.cvtColor(raw_img, cv2.COLOR_BGR2GRAY)

# InDesign template matching at multiple scales
found = None
for scale in np.linspace(0.2, 1.5, 50):
    # Resize the raw image
    w = int(raw_gray.shape[1] * scale)
    h = int(raw_gray.shape[0] * scale)
    if w > layout_gray.shape[1] or h > layout_gray.shape[0]:
        continue
        
    resized = cv2.resize(raw_gray, (w, h))
    res = cv2.matchTemplate(layout_gray, resized, cv2.TM_CCOEFF_NORMED)
    min_val, max_val, min_loc, max_loc = cv2.minMaxLoc(res)
    
    if found is None or max_val > found[0]:
        found = (max_val, max_loc, scale, w, h)

max_val, max_loc, scale, w, h = found
print(f"Match found: score={max_val:.4f}, location={max_loc}, scale={scale:.4f}, width={w}, height={h}")

# Bounding box in layout image
x, y = max_loc
x2, y2 = x + w, y + h
print(f"Bounding Box: X={x}, Y={y}, W={w}, H={h} (Normalized: X={x/2550:.3f}, Y={y/3300:.3f}, W={w/2550:.3f}, H={h/3300:.3f})")
