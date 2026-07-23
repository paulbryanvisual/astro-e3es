import cv2
import os

path = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets/North Texas Medical Center/Jason Flowers - North Texas Medical Center.png'
out_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets/North Texas Medical Center/images'

print("🔍 Starting contour detection to crop the embedded photo...")

img = cv2.imread(path)
if img is None:
    print("Failed to load image!")
    exit(1)

# Convert to grayscale
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

# Bilateral filter to preserve edges
blurred = cv2.bilateralFilter(gray, 9, 75, 75)

# Canny edge detection
edged = cv2.Canny(blurred, 30, 150)

# Find contours
contours, _ = cv2.findContours(edged.copy(), cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

print(f"Found {len(contours)} total external contours.")

# Filter contours
candidates = []
for c in contours:
    x, y, w, h = cv2.boundingRect(c)
    aspect_ratio = float(w) / h
    # We expect a photo to be reasonably large (e.g., width > 400, height > 300)
    # and have an aspect ratio typical of photographs (e.g., 4:3 = 1.33, 3:2 = 1.5, 16:9 = 1.77, etc.)
    if 400 < w < 2400 and 300 < h < 2400:
        if 1.0 < aspect_ratio < 2.0:
            candidates.append((w * h, (x, y, w, h)))

if not candidates:
    # Try with fewer constraints (any large non-text block)
    print("No constrained candidates found, loosening bounds...")
    for c in contours:
        x, y, w, h = cv2.boundingRect(c)
        if w > 300 and h > 200 and w < 2500 and h < 3200:
            candidates.append((w * h, (x, y, w, h)))

if candidates:
    # Sort by area (largest first)
    candidates.sort(key=lambda item: item[0], reverse=True)
    
    # Save the top 3 candidates as crops for inspection
    os.makedirs(out_dir, exist_ok=True)
    for idx, (area, box) in enumerate(candidates[:3]):
        x, y, w, h = box
        print(f"Candidate {idx+1}: Box X={x}, Y={y}, W={w}, H={h}, Area={area}, Aspect={w/h:.3f}")
        crop = img[y:y+h, x:x+w]
        dest = os.path.join(out_dir, f"crop_{idx+1}.png")
        cv2.imwrite(dest, crop)
        print(f"  Saved crop to: {dest}")
else:
    print("No photo candidates found!")
