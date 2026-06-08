import xml.etree.ElementTree as ET
import re

tree = ET.parse("timeline.svg")
root = tree.getroot()
ns = {"svg": "http://www.w3.org/2000/svg"}
layer = root.find(".//svg:g[@id=\"Layer_1-2\"]/svg:g", ns)

elements = []
for i, child in enumerate(layer):
    tag = child.tag.split("}")[1]
    cls = child.get("class", "")
    x, y, w = 9999, 9999, 0
    if tag == "rect" and child.get("x") is not None:
        x, y, w = float(child.get("x")), float(child.get("y")), float(child.get("width"))
    elif tag == "polygon":
        pts = child.get("points")
        if pts:
            nums = [float(n) for n in re.findall(r"-?\d+\.?\d*", pts)]
            if nums:
                xs, ys = nums[0::2], nums[1::2]
                x, y, w = min(xs), min(ys), max(xs)-min(xs)
    elif tag == "g":
        paths = child.findall(".//svg:path", ns)
        if paths:
            d = paths[0].get("d", "")
            m = re.match(r"[Mm]\s*(-?\d+\.?\d*)[,\s]+(-?\d+\.?\d*)", d)
            if m:
                x, y, w = float(m.group(1)), float(m.group(2)), 0
    elif tag == "line":
        x1, y1 = float(child.get("x1", 9999)), float(child.get("y1", 9999))
        x2, y2 = float(child.get("x2", 9999)), float(child.get("y2", 9999))
        x, y, w = min(x1, x2), min(y1, y2), abs(x2 - x1)
    elif tag == "path":
        d = child.get("d", "")
        m = re.match(r"[Mm]\s*(-?\d+\.?\d*)[,\s]+(-?\d+\.?\d*)", d)
        if m:
            x, y, w = float(m.group(1)), float(m.group(2)), 0

    if x != 9999:
        elements.append({"id": i, "tag": tag, "x": x, "y": y, "w": w, "cls": cls})

elements.sort(key=lambda e: (e["x"], e["y"]))
for e in elements:
    print(f"ID {e['id']:2d} | {e['tag']:7s} | X={e['x']:6.1f} | Y={e['y']:6.1f} | W={e['w']:5.1f} | cls={e['cls']}")
