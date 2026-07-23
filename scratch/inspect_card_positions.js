import puppeteer from 'puppeteer-core';

async function run() {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto('http://localhost:4008/clients', { waitUntil: 'networkidle2' });

  const cardsInfo = await page.evaluate(() => {
    const cards = Array.from(document.querySelectorAll('.client-card'));
    return cards.slice(0, 5).map(card => {
      const cardStyle = window.getComputedStyle(card);
      const imgWrap = card.querySelector('div'); // first child
      const imgWrapStyle = imgWrap ? window.getComputedStyle(imgWrap) : null;
      const infoBox = card.querySelector('div:nth-of-type(2)') || card.lastElementChild; // second child
      const infoBoxStyle = infoBox ? window.getComputedStyle(infoBox) : null;
      
      const title = card.querySelector('h3');
      const titleStyle = title ? window.getComputedStyle(title) : null;
      
      return {
        title: title ? title.textContent : 'No Title',
        card: {
          display: cardStyle.display,
          flexDirection: cardStyle.flexDirection,
          justifyContent: cardStyle.justifyContent,
          height: card.getBoundingClientRect().height,
          computedHeight: cardStyle.height
        },
        imgWrap: imgWrap ? {
          height: imgWrap.getBoundingClientRect().height,
          computedHeight: imgWrapStyle.height
        } : null,
        infoBox: infoBox ? {
          tag: infoBox.tagName,
          class: infoBox.className,
          display: infoBoxStyle.display,
          flexDirection: infoBoxStyle.flexDirection,
          justifyContent: infoBoxStyle.justifyContent,
          height: infoBox.getBoundingClientRect().height,
          computedHeight: infoBoxStyle.height,
          marginTop: infoBoxStyle.marginTop,
          padding: infoBoxStyle.padding
        } : null
      };
    });
  });

  console.log('--- CARDS GEOMETRY ANALYSIS ---');
  console.log(JSON.stringify(cardsInfo, null, 2));
  await browser.close();
}

run();
