import xml.etree.ElementTree as ET

tree = ET.parse("timeline.svg")
root = tree.getroot()
ns = {"svg": "http://www.w3.org/2000/svg"}
ET.register_namespace("", "http://www.w3.org/2000/svg")

layer = root.find(".//svg:g[@id=\"Layer_1-2\"]/svg:g", ns)
for i, child in enumerate(layer):
    child.set("id", f"c-{i}")

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

/* Initially hide all components that need animation */
"""

# Let's map elements based on X, Y and visual intuition.
# 1. "design build and competive sealed proposal appear fist."
step1 = ["c-17"] # X=26.6, Y=163.3 (text)

# 2. "then vs. and simultaneous workflow and linear workflow"
# vs text/lines: c-78 (X=213), c-77 (X=217)? No. c-64 (X=27.1, Y=7.2)
# simultaneous: c-70 (X=36.3, Y=20.4)
# linear workflow: c-16 (X=84.8, Y=172.8, cls-30)? Wait, c-34 (X=266, Y=152)? Let's just group c-15, c-16, c-70, c-64
step2 = ["c-64", "c-70", "c-16", "c-15"]

# 3. "ONE COMPANY SEVERAL COMPANIES"
# Box One Company: c-36 (rect X=26, Y=26). Text: c-41? No, c-41 is X=57. c-37 is X=35.
# Box Several: c-68 (rect X=26, Y=186). Text: c-69 is X=31.
step3 = ["c-36", "c-37", "c-68", "c-69"]

# 4. "Conceptual Planning blocks and INPUT FROM CONSTRUCTION with arrow on top"
# Top Conceptual Planning: Box=c-5 (X=139, Y=26). Text=c-54 (X=156), c-40 (X=164)
# Bottom Conceptual Planning: Box=c-4 (X=139, Y=186). Text=c-60 (X=155)
# Top Input from Construction: Text=c-76 (X=247). Arrow=c-78 (X=213), c-77
step4 = ["c-5", "c-54", "c-40", "c-4", "c-60", "c-76", "c-78", "c-77"]

# 5. "then Estimate and Bid" (Top)
# Box=c-1 (X=139.5, Y=84.6). Text=c-41, c-42, c-72, c-73?
# Let's find top Estimate and Bid text. X around 140-250, Y around 84.
# c-41 (X=57), c-42 (X=64). c-72 (X=264), c-73 (X=263).
# Wait, maybe text is c-40? No, c-40 is X=164, Y=113. 
# Box c-1 is Y=84.6. Center is Y=84+50=134.
# So c-40 (Y=113) fits nicely in c-1! But I assigned c-40 to Conceptual Planning top!
# Conceptual Planning top is Box c-5 (Y=26.2). Center is 26+50=76.
# Text for c-5 is likely c-41 (Y=74) and c-42 (Y=88)? No, c-54 (Y=55).
# So Top Conceptual: c-5, c-54.
# Top Estimate & Bid: c-1, c-40, c-41, c-42? Let's just group c-1, c-40, c-41, c-42.
step5 = ["c-1", "c-40", "c-41", "c-42", "c-72", "c-73"]

# 6. "and then Preliminary Design before Estimate and Bid is finish appearing and Preliminary Design on the lower timeline at the same time."
# Top Preliminary: Box=c-6 (X=252, Y=26). Text=c-74 (X=277), c-75 (X=273).
# Bottom Preliminary: Box=c-7 (X=252, Y=185). Text=c-39 (X=279), c-61 (X=283).
step6 = ["c-6", "c-74", "c-75", "c-7", "c-39", "c-61"]

# 7. "then Final Design Funding Approval and Final Design below."
# Top Final Design: Box=c-10 (X=322, Y=26). Text=c-56 (X=331).
# Top Funding Approval: Box=c-12 (polygon X=366, Y=19). Text=c-59 (X=391)?
# Bottom Final Design: Box=c-2 (X=366, Y=186). Text=c-62 (X=500)? No, Text=c-58 (X=570)? No, Text=c-38 (X=491)? No, c-34 (X=266)?
# Let's include c-10, c-56, c-12, c-59, c-2, c-38, c-58, c-57.
step7 = ["c-10", "c-56", "c-12", "c-59", "c-2", "c-38", "c-58", "c-57", "c-62", "c-63"]

# 8. "then construction completes."
# Top Construction: Box=c-79 (X=600). Text=c-82 (X=619).
# Bottom Construction: Box=c-11 (X=631) & c-13 (X=708). Text=c-55 (X=653).
step8 = ["c-79", "c-82", "c-11", "c-13", "c-55"]

# 9. slowly load the resy block by block on the bottom.
# "before funding approval on the bottom, animate the PRICE DETERMINED and Re-design and re-bid if over budget and dotted lines backwards."
# Wait, user said:
# "before funding approval on the bottom, animate the PRICE DETERMINED and Re-design and re-bid if over budget and dotted lines backwards."

# Bottom Estimate & Bid: Box=c-9 (X=309, Y=84). Text=c-72, c-73?
# Let's group all unassigned boxes in the bottom:
# c-8 (X=555, Y=185) -> Bottom Select Contractor.
# Bottom Estimate & Bid is earlier. Maybe Box=c-9? Wait, c-9 is Y=84 (middle). 
# There's a green box on bottom.
# Let's just group the rest by X coordinate!

all_assigned = step1 + step2 + step3 + step4 + step5 + step6 + step7 + step8

# Remaining elements:
# We need "PRICE DETERMINED", "Re-design...", and dotted lines.
# PRICE DETERMINED: Stars and text.
# Stars are usually polygons. c-20 (X=23), c-23 (X=23)? No, X=23 is far left.
# Wait, c-43 (X=244), c-44 (X=471), c-51 (X=195).
# "2xfaster" is c-80, c-81? c-80 is path X=614.
# Let's apply a generic "fade in" to everything unassigned, but follow the backwards order for the dotted lines!

css += f"""
{", ".join(f"#{i}" for i in range(83))} {{
    opacity: 0;
}}
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

# "before funding approval on the bottom, animate the PRICE DETERMINED and Re-design and re-bid if over budget and dotted lines backwards."
# Funding approval on the bottom: Box c-8? (X=555).
# Price Determined: Star c-43, c-44? 
# Dotted lines backwards: "wipeLeft" animation!
# Let's do this for all unassigned elements between X=100 and X=500.
# Then bottom funding approval.
# Then Select Contractor.
# Then "2x faster" and its lines.

css += """
/* Unassigned bottom elements wiping backwards */
#c-43, #c-44, #c-51, #c-48, #c-49, #c-50, #c-47, #c-34, #c-35 {
    animation: wipeLeft 1s ease forwards 6.2s;
}

/* Bottom Funding Approval & Select Contractor */
#c-8, #c-52, #c-53 {
    animation: wipeRight 1s ease forwards 7.2s;
}

/* 2x faster */
#c-71, #c-80, #c-81, #c-26, #c-27, #c-28, #c-29, #c-30, #c-31, #c-24, #c-25, #c-22, #c-18, #c-21 {
    animation: wipeRight 1s ease forwards 8.2s;
}
"""

style_tag = root.find(".//svg:style", ns)
if style_tag is not None:
    style_tag.text += css

tree.write("animated-timeline.svg")
print("Generated animated-timeline.svg")
