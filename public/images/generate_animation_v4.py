import xml.etree.ElementTree as ET
import re

tree = ET.parse("timeline.svg")
root = tree.getroot()
ns = {"svg": "http://www.w3.org/2000/svg"}
ET.register_namespace("", "http://www.w3.org/2000/svg")

layer = root.find(".//svg:g[@id=\"Layer_1-2\"]/svg:g", ns)

elements = []
for i, child in enumerate(layer):
    child.set("id", f"c-{i}")
    tag = child.tag.split("}")[1]
    cls = child.get("class", "")
    
    x, y, w = 9999, 9999, 0
    if tag == "rect" and child.get("x") is not None:
        x, y, w = float(child.get("x")), float(child.get("y")), float(child.get("width"))
    elif tag == "polygon":
        pts = child.get("points")
        if pts:
            nums = [float(n) for n in re.findall(r"-?\d+\.?\d*", pts)]
            if nums: x, y = min(nums[0::2]), min(nums[1::2])
    elif tag in ["g", "path"]:
        paths = child.findall(".//svg:path", ns) if tag == "g" else [child]
        if paths:
            m = re.match(r"[Mm]\s*(-?\d+\.?\d*)[,\s]+(-?\d+\.?\d*)", paths[0].get("d", ""))
            if m: x, y = float(m.group(1)), float(m.group(2))
    elif tag == "line":
        x = min(float(child.get("x1", 9999)), float(child.get("x2", 9999)))
        y = min(float(child.get("y1", 9999)), float(child.get("y2", 9999)))
    
    if x == 9999: x, y = 0, 0
    
    is_top = y < 130
    elements.append({
        "id": f"c-{i}", "x": x, "y": y, "top": is_top, "cls": cls, "tag": tag
    })

# Special handling: 
# "2x faster" and its dotted lines are at the very end.
faster_ids = ["c-71", "c-80", "c-81", "c-82", "c-26", "c-27", "c-28", "c-29", "c-30", "c-31"]

# "PRICE DETERMINED and Re-design and re-bid if over budget" (Bottom)
# This includes c-38 (PRICE DETERMINED text), c-39 (Re-design text), 
# c-51 (Star polygon?), c-48, c-49, c-50, c-47 (Dotted lines).
backward_ids = ["c-38", "c-39", "c-51", "c-48", "c-49", "c-50", "c-47"]

# Remove special elements from the main flow
main_elements = [e for e in elements if e["id"] not in faster_ids and e["id"] not in backward_ids]

# Sort main elements by X coordinate columns, then Top/Bottom
columns = [
    (0, 50),
    (50, 100),
    (100, 200),
    (200, 260),
    (260, 315),
    (315, 360),
    (360, 480),
    (480, 580),
    (580, 700),
    (700, 9999)
]

sequence = []

# First, user specifically asked for:
# DESIGN-BUILD (top) -> simultaneous workflow (top) -> COMPETITIVE SEALED PROPOSAL (bottom) -> linear workflow (bottom)
# Then "ONE COMPANY" (top) -> "SEVERAL COMPANIES" (bottom)

col1_top = [e["id"] for e in main_elements if e["x"] < 50 and e["top"] and e["tag"] == "g"]
col1_bot = [e["id"] for e in main_elements if e["x"] < 50 and not e["top"] and e["tag"] == "g"]

sequence.append(["c-64"]) # DESIGN-BUILD
sequence.append(["c-70"]) # simultaneous workflow
sequence.append(["c-17"]) # COMPETITIVE SEALED PROPOSAL
sequence.append(["c-15"]) # linear workflow
sequence.append(["c-36", "c-37"]) # ONE COMPANY (box + text)
sequence.append(["c-68", "c-69"]) # SEVERAL COMPANIES (box + text)

# Now iterate the rest of the columns
used = set(["c-64", "c-70", "c-17", "c-15", "c-36", "c-37", "c-68", "c-69"])

for c_min, c_max in columns:
    col_elems = [e for e in main_elements if c_min <= e["x"] < c_max and e["id"] not in used]
    
    # Top elements
    top_elems = [e["id"] for e in col_elems if e["top"]]
    if top_elems:
        sequence.append(top_elems)
        used.update(top_elems)
        
    # Bottom elements
    bot_elems = [e["id"] for e in col_elems if not e["top"]]
    if bot_elems:
        sequence.append(bot_elems)
        used.update(bot_elems)

css = """
@keyframes wipeRight {
    0% { clip-path: inset(0 100% 0 0); opacity: 0; }
    1% { opacity: 1; }
    100% { clip-path: inset(0 0 0 0); opacity: 1; }
}
@keyframes wipeLeft {
    0% { clip-path: inset(0 0 0 100%); opacity: 0; }
    1% { opacity: 1; }
    100% { clip-path: inset(0 0 0 0); opacity: 1; }
}
[id^="c-"] { opacity: 0; }
"""

delay = 0.0
step_duration = 0.8 # Longer delay between steps

for step_group in sequence:
    if not step_group: continue
    css += f"{', '.join('#'+g for g in step_group)} {{ animation: wipeRight 0.8s ease forwards {delay:.1f}s; }}\n"
    delay += step_duration

# Now the "backwards" items (PRICE DETERMINED & Re-design)
css += f"{', '.join('#'+g for g in backward_ids)} {{ animation: wipeLeft 1s ease forwards {delay:.1f}s; }}\n"
delay += 1.2

# Now the 2x faster items
css += f"{', '.join('#'+g for g in faster_ids)} {{ animation: wipeRight 1s ease forwards {delay:.1f}s; }}\n"

style_tag = root.find(".//svg:style", ns)
if style_tag is not None:
    style_tag.text += css

tree.write("animated-timeline.svg")
print("Done v4")
