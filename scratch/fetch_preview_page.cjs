const fs = require('fs');

fetch('http://localhost:4383/services/water-wastewater/?wp_edit_token=NaDpLLFVEBPSZesYqDDZUwjUrWVPStvT')
  .then(res => res.text())
  .then(html => {
    fs.writeFileSync('scratch/preview_water_page.html', html, 'utf8');
    console.log('Saved preview page HTML to scratch/preview_water_page.html');
    
    // Check if FAQ title is present in body
    const bodyStart = html.indexOf('<body');
    const bodyContent = bodyStart !== -1 ? html.substring(bodyStart) : html;
    
    let pos = 0;
    console.log('--- SEARCHING IN PREVIEW BODY ---');
    while ((pos = bodyContent.indexOf('faq-section', pos)) !== -1) {
      console.log(`\nFound 'faq-section' at offset ${pos}:`);
      console.log(bodyContent.substring(pos - 150, pos + 250));
      pos += 11;
    }
  })
  .catch(err => console.error('Fetch error:', err));
