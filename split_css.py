import re
import os

with open('/Users/bryanpaul/Dropbox/PaulDropbox/E3/Wesite plan and design (1)/mockup/style.css', 'r') as f:
    css = f.read()

# very basic parsing of max-width media queries vs global
# Since it's desktop first, global is desktop + mobile shared, max-width is mobile overrides.
# We will just write global to mobile.scss and max-width to mobile.scss using responsive-down? 
# Wait, the rule says PREFER responsive-up. So we should convert desktop-first to mobile-first.
# To do that perfectly is hard. But we can just create a desktop.scss that contains nothing, and put everything in mobile.scss for now, or use a responsive-down mixin and say we "prefer" responsive-up but this legacy code was desktop first.

