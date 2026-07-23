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
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto('http://e3es2026.local/auto-login.php', { waitUntil: 'networkidle2' });
  await new Promise(resolve => setTimeout(resolve, 8000));

  // Find the project block, scroll to it
  await page.evaluate(() => {
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    const doc = iframe ? (iframe.contentDocument || iframe.contentWindow.document) : document;
    const project = doc.querySelector('.project-section');
    if (project) {
      project.scrollIntoView({ block: 'center' });
    }
  });
  await new Promise(resolve => setTimeout(resolve, 2000));

  // Capture ancestor styles and background colors
  const info = await page.evaluate(() => {
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    const doc = iframe ? (iframe.contentDocument || iframe.contentWindow.document) : document;
    const hero = doc.querySelector('.project-section__hero');
    if (!hero) return 'No hero found';

    const path = [];
    let current = hero;
    while (current && current !== doc.body) {
      const style = window.getComputedStyle(current);
      path.push({
        tag: current.tagName,
        class: current.className,
        background: style.background,
        backgroundImage: style.backgroundImage,
        backgroundColor: style.backgroundColor,
        opacity: style.opacity,
        zIndex: style.zIndex,
        display: style.display
      });
      current = current.parentElement;
    }
    return path;
  });

  console.log('--- ANCESTOR STYLE PATH ---');
  console.log(JSON.stringify(info, null, 2));

  // Take screenshot
  const screenshotPath = '/Users/bryanpaul/.gemini/antigravity/brain/b9c8b880-8835-4792-8e98-4e16468a4b3a/project-background-debug.png';
  await page.screenshot({ path: screenshotPath });
  console.log(`Saved screenshot to ${screenshotPath}`);

  await browser.close();
  if (fs.existsSync(autoLoginPath)) fs.unlinkSync(autoLoginPath);
}

run();
