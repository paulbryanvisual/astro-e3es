import { execSync } from 'child_process';

async function run() {
    try {
        const res = await fetch('http://localhost:4008/clients');
        if (!res.ok) {
            console.error('Failed to fetch clients page:', res.status, res.statusText);
            return;
        }
        const html = await res.text();
        
        // Find all client cards
        // They look like: <a href="..." class="client-card" ...>
        const cardRegex = /<a\s+[^>]*class=["'][^"']*client-card[^"']*["'][^>]*>/g;
        let match;
        const cards = [];
        
        while ((match = cardRegex.exec(html)) !== null) {
            const cardTag = match[0];
            const hrefMatch = cardTag.match(/href=["']([^"']+)["']/);
            const href = hrefMatch ? hrefMatch[1] : '';
            
            // Extract the data attributes
            const nameMatch = cardTag.match(/data-name=["']([^"']+)["']/);
            const industryMatch = cardTag.match(/data-industry=["']([^"']+)["']/);
            const regionMatch = cardTag.match(/data-region=["']([^"']+)["']/);
            
            cards.push({
                href,
                name: nameMatch ? nameMatch[1] : '',
                industry: industryMatch ? industryMatch[1] : '',
                region: regionMatch ? regionMatch[1] : '',
                tag: cardTag
            });
        }
        
        console.log(`Total Client Cards Found: ${cards.length}`);
        console.log('First 5 cards:', cards.slice(0, 5));
        
        // Check for south-texas and gwh
        const southTexas = cards.filter(c => c.href.includes('south-texas') || c.name.includes('south texas'));
        const gwh = cards.filter(c => c.href.includes('gwh') || c.href.includes('goodall-witcher') || c.name.includes('goodall-witcher'));
        
        console.log(`South Texas matches: ${southTexas.length}`, southTexas);
        console.log(`GWH matches: ${gwh.length}`, gwh);
    } catch (err) {
        console.error('Error fetching clients:', err);
    }
}

run();
