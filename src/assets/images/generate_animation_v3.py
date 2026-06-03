import xml.etree.ElementTree as ET
import re

tree = ET.parse("timeline.svg")
root = tree.getroot()
ns = {"svg": "http://www.w3.org/2000/svg"}
ET.register_namespace("", "http://www.w3.org/2000/svg")

layer = root.find(".//svg:g[@id=\"Layer_1-2\"]/svg:g", ns)

elements_pos = {}
for i, child in enumerate(layer):
    child.set("id", f"c-{i}")
    tag = child.tag.split("}")[1]
    x, y = 9999, 9999
    if tag == "rect" and child.get("x") is not None:
        x, y = float(child.get("x")), float(child.get("y"))
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
    elements_pos[f"c-{i}"] = (x, y)

# Manually assigned
step1 = ["c-17"] 
step2 = ["c-64", "c-70", "c-16", "c-15"]
step3 = ["c-36", "c-37", "c-68", "c-69"]
step4 = ["c-5", "c-54", "c-40", "c-4", "c-60", "c-76", "c-78", "c-77"]
step5 = ["c-1", "c-41", "c-42"] # Top Estimate & Bid. 
step6 = ["c-6", "c-74", "c-75", "c-7", "c-39", "c-61"]
step7 = ["c-10", "c-56", "c-12", "c-59", "c-2", "c-38", "c-58", "c-57", "c-62", "c-63"]
step8 = ["c-79", "c-82", "c-11", "c-13", "c-55"]

backwards = ["c-43", "c-44", "c-51", "c-48", "c-49", "c-50", "c-47"]
bottom_funding = ["c-8", "c-52", "c-53"]
faster_2x = ["c-71", "c-80", "c-81", "c-26", "c-27", "c-28", "c-29", "c-30", "c-31", "c-24", "c-25", "c-22", "c-18", "c-21"]

all_assigned = step1 + step2 + step3 + step4 + step5 + step6 + step7 + step8 + backwards + bottom_funding + faster_2x

unassigned = [f"c-{i}" for i in range(83) if f"c-{i}" not in all_assigned]

for u in unassigned:
    x, y = elements_pos[u]
    if y > 150 and x > 300:
        # Bottom timeline slow load
        if x < 400: backwards.append(u)
        elif x < 600: bottom_funding.append(u)
        else: faster_2x.append(u)
    else:
        # Top timeline & general
        if x < 40: step1.append(u)
        elif x < 80: step2.append(u)
        elif x < 130: step3.append(u)
        elif x < 250: step4.append(u)
        elif x < 320: step5.append(u)
        elif x < 366: step6.append(u)
        elif x < 600: step7.append(u)
        else: step8.append(u)

css = """
/* Animations */
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

def add_step(groups, delay, dur="0.8s", anim="wipeRight"):
    global css
    if groups:
        css += f"{', '.join('#'+g for g in groups)} {{ animation: {anim} {dur} ease forwards {delay}s; }}\n"

add_step(step1, 0)
add_step(step2, 0.8)
add_step(step3, 1.6)
add_step(step4, 2.4)
add_step(step5, 3.2)
add_step(step6, 3.8) # Overlap
add_step(step7, 4.6)
add_step(step8, 5.4)

add_step(backwards, 6.2, anim="wipeLeft")
add_step(bottom_funding, 7.2)
add_step(faster_2x, 8.2)

style_tag = root.find(".//svg:style", ns)
if style_tag is not None:
    style_tag.text += css

tree.write("animated-timeline.svg")
print("Generated animated-timeline.svg")
