import xml.etree.ElementTree as ET
import re

tree = ET.parse("timeline.svg")
root = tree.getroot()
ns = {"svg": "http://www.w3.org/2000/svg"}
ET.register_namespace("", "http://www.w3.org/2000/svg")

layer = root.find(".//svg:g[@id=\"Layer_1-2\"]/svg:g", ns)

elements = []
child_map = {}
for i, child in enumerate(list(layer)):
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
    elem_data = {
        "id": f"c-{i}", "x": x, "y": y, "top": is_top, "cls": cls, "tag": tag, "node": child, "orig_idx": i
    }
    elements.append(elem_data)
    child_map[f"c-{i}"] = elem_data

# Clear the original layer so we can re-append grouped
for child in list(layer):
    layer.remove(child)

faster_ids = ["c-71", "c-80", "c-81", "c-82", "c-26", "c-27", "c-28", "c-29", "c-30", "c-31"]
backward_ids = ["c-38", "c-39", "c-51", "c-48", "c-49", "c-50", "c-47"]
main_elements = [e for e in elements if e["id"] not in faster_ids and e["id"] not in backward_ids]

columns = [
    (0, 50), (50, 100), (100, 200), (200, 260), (260, 315), 
    (315, 360), (360, 480), (480, 580), (580, 700), (700, 9999)
]

sequence = []
sequence.append(["c-64"]) 
sequence.append(["c-70"]) 
sequence.append(["c-17"]) 
sequence.append(["c-15"]) 
sequence.append(["c-36", "c-37"]) 
sequence.append(["c-68", "c-69"]) 

used = set(["c-64", "c-70", "c-17", "c-15", "c-36", "c-37", "c-68", "c-69"])

for c_min, c_max in columns:
    col_elems = [e for e in main_elements if c_min <= e["x"] < c_max and e["id"] not in used]
    top_elems = [e["id"] for e in col_elems if e["top"]]
    if top_elems:
        sequence.append(top_elems)
        used.update(top_elems)
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
g[id^="step-"] { opacity: 0; }
"""

delay = 0.0
step_duration = 0.8 

def create_step_group(step_id, elem_ids, delay, anim):
    global css
    g = ET.Element("g", {"id": step_id})
    # Sort elements by original DOM index to preserve Z-index within the group!
    sorted_elems = sorted([child_map[eid] for eid in elem_ids], key=lambda e: e["orig_idx"])
    for e in sorted_elems:
        g.append(e["node"])
    layer.append(g)
    css += f"#{step_id} {{ animation: {anim} 0.8s ease forwards {delay:.1f}s; }}\n"

for i, step_group in enumerate(sequence):
    if not step_group: continue
    create_step_group(f"step-{i}", step_group, delay, "wipeRight")
    delay += step_duration

create_step_group("step-backwards", backward_ids, delay, "wipeLeft")
delay += step_duration + 0.4
create_step_group("step-faster", faster_ids, delay, "wipeRight")

style_tag = root.find(".//svg:style", ns)
if style_tag is not None:
    style_tag.text += css

tree.write("animated-timeline.svg")
print("Done v5")
