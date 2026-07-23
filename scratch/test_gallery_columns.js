import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

async function run() {
  // 1. Create the auto-login.php file redirecting to post 1483
  const autoLoginPath = '/Users/bryanpaul/Local Sites/e3es2026/app/public/auto-login.php';
  const autoLoginContent = `<?php
require 'wp-load.php';
$user = get_user_by('id', 1);
if ($user) {
    clean_user_cache($user->ID);
    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);
    update_user_caches($user);
}
header('Location: /wp-admin/post.php?post=1483&action=edit');
exit;
`;
  fs.writeFileSync(autoLoginPath, autoLoginContent);
  console.log('Created auto-login.php for post 1483.');

  console.log('Launching browser...');
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });

  console.log('Navigating to auto-login.php...');
  await page.goto('http://e3es2026.local/auto-login.php', { waitUntil: 'networkidle2' });

  console.log('Waiting for Gutenberg editor skeleton...');
  try {
    await page.waitForSelector('.interface-interface-skeleton', { timeout: 15000 });
    console.log('Gutenberg skeleton loaded.');
  } catch (err) {
    console.log('Skeleton selector not found. Proceeding...');
  }

  // Wait 6 seconds for blocks to render
  console.log('Waiting for blocks to render...');
  await new Promise(resolve => setTimeout(resolve, 6000));

  console.log('Evaluating Gallery blocks in the visual editor...');
  const result = await page.evaluate(() => {
    function analyzeGallery(el) {
      if (!el) return null;
      const style = window.getComputedStyle(el);
      const figures = Array.from(el.querySelectorAll('.wp-block-image, .wp-block-gallery .wp-block-image'));
      const figureStyles = figures.map(fig => {
        const figStyle = window.getComputedStyle(fig);
        return {
          tag: fig.tagName,
          class: fig.className,
          display: figStyle.display,
          width: figStyle.width,
          height: figStyle.height,
          aspectRatio: figStyle.aspectRatio
        };
      });

      return {
        found: true,
        tag: el.tagName,
        class: el.className,
        display: style.display,
        gridTemplateColumns: style.gridTemplateColumns,
        gap: style.gap,
        width: style.width,
        maxWidth: style.maxWidth,
        margin: style.margin,
        figuresCount: figures.length,
        figures: figureStyles.slice(0, 3) // show first 3
      };
    }

    // Try finding inside Gutenberg iframe
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    if (iframe) {
      const doc = iframe.contentDocument || iframe.contentWindow.document;
      const galleries = Array.from(doc.querySelectorAll('.wp-block-gallery, .wp-block[data-type="core/gallery"]'));
      return {
        context: 'iframe',
        galleriesCount: galleries.length,
        galleries: galleries.map(gal => ({
          blockType: gal.getAttribute('data-type'),
          html: gal.outerHTML.substring(0, 500),
          info: analyzeGallery(gal.querySelector('.wp-block-gallery') || gal)
        }))
      };
    }

    // Fallback: search main document
    const galleries = Array.from(document.querySelectorAll('.wp-block-gallery, .wp-block[data-type="core/gallery"]'));
    return {
      context: 'parent',
      galleriesCount: galleries.length,
      galleries: galleries.map(gal => ({
        blockType: gal.getAttribute('data-type'),
        html: gal.outerHTML.substring(0, 500),
        info: analyzeGallery(gal.querySelector('.wp-block-gallery') || gal)
      }))
    };
  });

  console.log('--- GALLERY ANALYSIS RESULT ---');
  console.log(JSON.stringify(result, null, 2));

  // Scroll the first gallery into view
  await page.evaluate(() => {
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    const doc = iframe ? (iframe.contentDocument || iframe.contentWindow.document) : document;
    const gallery = doc.querySelector('.wp-block[data-type="core/gallery"], .wp-block-gallery');
    if (gallery) {
      gallery.scrollIntoView({ block: 'center' });
    }
  });
  await new Promise(resolve => setTimeout(resolve, 2000));

  // Take screenshot
  const screenshotPath = '/Users/bryanpaul/.gemini/antigravity/brain/b9c8b880-8835-4792-8e98-4e16468a4b3a/gallery-editor-screenshot.png';
  console.log(`Saving screenshot to ${screenshotPath}...`);
  await page.screenshot({ path: screenshotPath });
  console.log('Screenshot saved successfully.');

  await browser.close();

  // Clean up auto-login.php
  if (fs.existsSync(autoLoginPath)) {
    fs.unlinkSync(autoLoginPath);
    console.log('Cleaned up auto-login.php.');
  }
}

run().catch(err => {
  console.error('Execution failed:', err);
  // Clean up auto-login in case of failure
  const autoLoginPath = '/Users/bryanpaul/Local Sites/e3es2026/app/public/auto-login.php';
  if (fs.existsSync(autoLoginPath)) {
    fs.unlinkSync(autoLoginPath);
  }
  process.exit(1);
});
