import puppeteer from 'puppeteer-core';
import fs from 'fs';

async function run() {
  const autoLoginPath = '/Users/bryanpaul/Local Sites/e3es2026/app/public/auto-login.php';
  const autoLoginContent = `<?php
require 'wp-load.php';
$user = get_user_by('id', 1);
if ($user) {
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);
}
header('Location: /wp-admin/post.php?post=1483&action=edit');
exit;
`;
  fs.writeFileSync(autoLoginPath, autoLoginContent);

  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  const page = await browser.newPage();
  
  // Collect network requests to see if the background image is requested and if it returns 404
  const requests = [];
  page.on('response', response => {
    const url = response.url();
    if (url.includes('E3-background-layered-1920x1080.jpg')) {
      requests.push({
        url: url,
        status: response.status()
      });
    }
  });

  await page.goto('http://e3es2026.local/auto-login.php', { waitUntil: 'networkidle2' });
  await new Promise(resolve => setTimeout(resolve, 8000));

  const result = await page.evaluate(() => {
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    const doc = iframe ? (iframe.contentDocument || iframe.contentWindow.document) : document;
    
    // Find all project blocks
    const projects = Array.from(doc.querySelectorAll('.project-section'));
    return projects.map(proj => {
      const hero = proj.querySelector('.project-section__hero');
      const heroStyle = hero ? window.getComputedStyle(hero) : null;
      return {
        classes: proj.className,
        parentClasses: proj.parentElement ? proj.parentElement.className : null,
        heroFound: !!hero,
        heroBgImage: heroStyle ? heroStyle.backgroundImage : null,
        heroBgColor: heroStyle ? heroStyle.backgroundColor : null,
        heroHeight: heroStyle ? heroStyle.height : null
      };
    });
  });

  console.log('--- PROJECT BLOCKS BACKGROUND ANALYSIS ---');
  console.log(JSON.stringify(result, null, 2));
  console.log('--- IMAGE NETWORK REQUESTS ---');
  console.log(JSON.stringify(requests, null, 2));

  await browser.close();
  if (fs.existsSync(autoLoginPath)) fs.unlinkSync(autoLoginPath);
}

run();
