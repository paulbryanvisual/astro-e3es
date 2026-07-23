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
  await page.goto('http://e3es2026.local/auto-login.php', { waitUntil: 'networkidle2' });
  await new Promise(resolve => setTimeout(resolve, 8000));

  const dom = await page.evaluate(() => {
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    const doc = iframe ? (iframe.contentDocument || iframe.contentWindow.document) : document;
    const gallery = doc.querySelector('.wp-block-gallery');
    if (!gallery) return 'No gallery found';
    
    // Dump outer HTML and child tags
    const children = Array.from(gallery.children).map(child => ({
      tag: child.tagName,
      class: child.className,
      style: child.getAttribute('style'),
      childCount: child.children.length,
      children: Array.from(child.children).map(c => ({
        tag: c.tagName,
        class: c.className
      }))
    }));
    
    return {
      galleryClass: gallery.className,
      galleryStyle: gallery.getAttribute('style'),
      children: children
    };
  });

  console.log(JSON.stringify(dom, null, 2));
  await browser.close();
  if (fs.existsSync(autoLoginPath)) fs.unlinkSync(autoLoginPath);
}

run();
