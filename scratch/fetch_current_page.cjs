const fs = require('fs');

fetch('http://localhost:4383/services/water-wastewater/')
  .then(res => res.text())
  .then(html => {
    fs.writeFileSync('scratch/current_water_page.html', html, 'utf8');
    console.log('Saved current page HTML to scratch/current_water_page.html');
    
    // Check if FAQ title is present
    const idx = html.indexOf('faq-section__title');
    if (idx !== -1) {
      console.log('FOUND faq-section__title in current page HTML!');
      console.log(html.substring(idx - 100, idx + 200));
    } else {
      console.log('NOT FOUND faq-section__title in current page HTML!');
    }
  })
  .catch(err => console.error('Fetch error:', err));
