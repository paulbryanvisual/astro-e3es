import re

with open('src/pages/index.astro', 'r') as f:
    content = f.read()

imports = """
import { getImage, Picture } from 'astro:assets';
import bg1 from '../assets/images/png-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080.jpg';
import bg2 from '../assets/images/commerce-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080.jpg';
import bg3 from '../assets/images/water-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080.jpg';
import bg4 from '../assets/images/53969622794_b49535a782_k-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080.jpg';

import tipsLogo from '../assets/images/dl_TIPS-TC-Logo-300x132-1-800x600-800x600-800x600-800x600-800x600-800x600.png';
import coopLogo from '../assets/images/dl_images-800x600-800x600-800x600-800x600-800x600-800x600.png';
import constrImg from '../assets/images/Texas20Funding20Solutions-600x400-600x400-600x400-600x400-600x400-600x400.jpg';
import mapImg from '../assets/images/static-map-600x400-600x400-600x400.png';

const optBg1 = await getImage({src: bg1, format: 'webp'});
const optBg2 = await getImage({src: bg2, format: 'webp'});
const optBg3 = await getImage({src: bg3, format: 'webp'});
const optBg4 = await getImage({src: bg4, format: 'webp'});
"""

content = content.replace("import { getPosts } from '../lib/wordpress';", "import { getPosts } from '../lib/wordpress';\n" + imports)

content = content.replace("style=\"--bg: url('/images/png-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080.jpg');\"", "style={`--bg: url('${optBg1.src}');`}")
content = content.replace("style=\"--bg: url('/images/commerce-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080.jpg');\"", "style={`--bg: url('${optBg2.src}');`}")
content = content.replace("style=\"--bg: url('/images/water-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080.jpg');\"", "style={`--bg: url('${optBg3.src}');`}")
content = content.replace("style=\"--bg: url('/images/53969622794_b49535a782_k-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080-1920x1080.jpg');\"", "style={`--bg: url('${optBg4.src}');`}")

content = content.replace('<img src="/images/dl_TIPS-TC-Logo-300x132-1-800x600-800x600-800x600-800x600-800x600-800x600.png" alt="TIPS Purchasing Cooperative" style="max-height: 40px; max-width: 100%; object-fit: contain;">',
'<Picture src={tipsLogo} alt="TIPS Purchasing Cooperative" style="max-height: 40px; max-width: 100%; object-fit: contain;" widths={[150, 300]} sizes="(max-width: 768px) 150px, 300px" formats={["avif", "webp"]} />')

content = content.replace('<img src="/images/dl_images-800x600-800x600-800x600-800x600-800x600-800x600.png" alt="Purchasing Cooperatives" style="max-height: 40px; max-width: 100%; object-fit: contain;">',
'<Picture src={coopLogo} alt="Purchasing Cooperatives" style="max-height: 40px; max-width: 100%; object-fit: contain;" widths={[150, 300]} sizes="(max-width: 768px) 150px, 300px" formats={["avif", "webp"]} />')

content = content.replace('<img src="/images/Texas20Funding20Solutions-600x400-600x400-600x400-600x400-600x400-600x400.jpg" alt="Construction Progress" class="db-feature__image">',
'<Picture src={constrImg} alt="Construction Progress" class="db-feature__image" widths={[400, 800]} sizes="(max-width: 768px) 400px, 800px" formats={["avif", "webp"]} />')

content = content.replace('<img src="/images/static-map-600x400-600x400-600x400.png" alt="Texas Service Map" class="db-feature__image">',
'<Picture src={mapImg} alt="Texas Service Map" class="db-feature__image" widths={[400, 800]} sizes="(max-width: 768px) 400px, 800px" formats={["avif", "webp"]} />')

with open('src/pages/index.astro', 'w') as f:
    f.write(content)
