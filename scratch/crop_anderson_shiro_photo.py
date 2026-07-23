import cv2
import numpy as np

# Load the layout image
img_path = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets/Anderson-Shiro CISD/images/Jason Flowers - Anderson-Shiro CISD.jpg'
img = cv2.imread(img_path)

if img is None:
    print("Error: Could not load image from " + img_path)
    exit(1)

h, w, c = img.shape
print(f"Loaded image: {w}x{h}")

# The photo spans the full width and sits in the upper part of the letter layout page.
# Based on the proportions of a standard 1978x2560 sheet:
# Top green bar ends at approx y = 208 (8.1% of height).
# Main project photo goes from y = 208 down to approx y = 840 (32.8% of height).
ymin = int(0.18 * h)
ymax = int(0.328 * h)
xmin = 0
xmax = w

crop = img[ymin:ymax, xmin:xmax]

# Save cropped photo
out_path = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets/Anderson-Shiro CISD/images/anderson-shiro-cisd-photo.jpg'
cv2.imwrite(out_path, crop)
print(f"Successfully cropped and saved project photo to: {out_path} (size: {crop.shape[1]}x{crop.shape[0]})")
