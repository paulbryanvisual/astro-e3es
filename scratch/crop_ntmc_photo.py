import cv2
import numpy as np
import os

path = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets/North Texas Medical Center/Jason Flowers - North Texas Medical Center.png'
out_path = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets/North Texas Medical Center/images/ntmc-cropped-photo.png'

print("📷 Slicing and cropping the project photo from NTMC layout sheet...")

img = cv2.imread(path)
if img is None:
    print("Error loading image!")
    exit(1)

h, w, c = img.shape
print(f"Image dimensions: {w}x{h}")

# We will divide the image into 10x10 blocks and compute standard deviation of colors in each block
grid_x = 50
grid_y = 50
block_w = w // grid_x
block_h = h // grid_y

stds = np.zeros((grid_y, grid_x))
for y in range(grid_y):
    for x in range(grid_x):
        block = img[y*block_h:(y+1)*block_h, x*block_w:(x+1)*block_w]
        stds[y, x] = np.std(block)

# We threshold the stds to find cells with high detail (photo area)
# Whitespace and text areas will have low/moderate standard deviation, while photographs have high standard deviation
threshold = np.percentile(stds, 70) # top 30% of detail
active_cells = stds > threshold

# Find the bounding box of active cells in the top-right / middle region
# In standard E3 reference sheets, the sidebar is on the left (first 30% width), so we ignore X < 0.3 * grid_x
# The header is at the top (first 15% height), so we ignore Y < 0.15 * grid_y
# The footer/text is at the bottom, so we ignore Y > 0.8 * grid_y
min_x = int(0.25 * grid_x)
max_x = int(0.95 * grid_x)
min_y = int(0.12 * grid_y)
max_y = int(0.75 * grid_y)

ys, xs = np.where(active_cells[min_y:max_y, min_x:max_x])
if len(xs) > 0:
    crop_x1 = (min_x + np.min(xs)) * block_w
    crop_x2 = (min_x + np.max(xs) + 1) * block_w
    crop_y1 = (min_y + np.min(ys)) * block_h
    crop_y2 = (min_y + np.max(ys) + 1) * block_h
    
    # Pad a bit
    crop_x1 = max(0, crop_x1 - 20)
    crop_x2 = min(w, crop_x2 + 20)
    crop_y1 = max(0, crop_y1 - 20)
    crop_y2 = min(h, crop_y2 + 20)
    
    print(f"Cropped Box: X1={crop_x1}, Y1={crop_y1}, X2={crop_x2}, Y2={crop_y2}")
    
    crop = img[crop_y1:crop_y2, crop_x1:crop_x2]
    os.makedirs(os.path.dirname(out_path), exist_ok=True)
    cv2.imwrite(out_path, crop)
    print(f"Successfully saved cropped raw photo to: {out_path}")
else:
    # Fallback to a hardcoded typical reference sheet photo crop box
    print("Could not detect photo region dynamically. Using template bounding box coordinates...")
    # Typically: X=950, Y=500, W=1450, H=1100
    crop_x1, crop_y1, crop_w, crop_h = 920, 480, 1500, 1150
    crop = img[crop_y1:crop_y1+crop_h, crop_x1:crop_x1+crop_w]
    os.makedirs(os.path.dirname(out_path), exist_ok=True)
    cv2.imwrite(out_path, crop)
    print(f"Saved fallback cropped photo to: {out_path}")
