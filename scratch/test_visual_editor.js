import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

async function run() {
  console.log('Launching browser...');
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });

  console.log('Logging in and navigating to post 1459 edit page...');
  await page.goto('http://e3es2026.local/auto-login.php', { waitUntil: 'networkidle2' });

  console.log('Waiting for Gutenberg editor to initialize...');
  try {
    await page.waitForSelector('.interface-interface-skeleton', { timeout: 15000 });
    console.log('Gutenberg interface skeleton detected.');
  } catch (err) {
    console.log('Warning: interface skeleton selector not found. Attempting to proceed anyway...');
  }

  // Wait 5 seconds to ensure editor iframe styles and JS blocks are fully rendered
  console.log('Waiting for blocks and styles to render...');
  await new Promise(resolve => setTimeout(resolve, 5000));

  console.log('Evaluating block rendering and visual editor CSS...');
  const result = await page.evaluate(() => {
    // Helper to extract computed styles from an element
    function checkElement(el) {
      if (!el) return null;
      const style = window.getComputedStyle(el);
      const titleEl = el.querySelector('.db-video-section__title');
      const introEl = el.querySelector('.db-video-section__intro');
      const wrapperEl = el.querySelector('.db-video-wrapper');
      
      const parentLayout = el.parentElement ? window.getComputedStyle(el.parentElement) : null;
      const sectionEl = el.querySelector('.db-video-section');
      const sectionStyle = sectionEl ? window.getComputedStyle(sectionEl) : null;
      
      return {
        found: true,
        tag: el.tagName,
        class: el.className,
        maxWidth: style.maxWidth,
        width: style.width,
        parent: el.parentElement ? {
          tag: el.parentElement.tagName,
          class: el.parentElement.className,
          width: parentLayout.width,
          maxWidth: parentLayout.maxWidth
        } : null,
        section: sectionEl ? {
          width: sectionStyle.width,
          maxWidth: sectionStyle.maxWidth,
          padding: sectionStyle.padding
        } : null,
        title: titleEl ? {
          text: titleEl.textContent,
          color: window.getComputedStyle(titleEl).color,
          fontFamily: window.getComputedStyle(titleEl).fontFamily,
          fontWeight: window.getComputedStyle(titleEl).fontWeight,
          fontSize: window.getComputedStyle(titleEl).fontSize
        } : null,
        intro: introEl ? {
          color: window.getComputedStyle(introEl).color,
          fontFamily: window.getComputedStyle(introEl).fontFamily,
          fontSize: window.getComputedStyle(introEl).fontSize,
          lineHeight: window.getComputedStyle(introEl).lineHeight
        } : null,
        wrapper: wrapperEl ? {
          borderRadius: window.getComputedStyle(wrapperEl).borderRadius,
          boxShadow: window.getComputedStyle(wrapperEl).boxShadow
        } : null
      };
    }

    // Try finding inside Gutenberg editor iframe if active
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    let block = null;
    let projectBlock = null;
    let bannerBlock = null;
    if (iframe) {
      const doc = iframe.contentDocument || iframe.contentWindow.document;
      block = doc.querySelector('.wp-block[data-type="e3es/video-embed"]');
      projectBlock = doc.querySelector('.wp-block[data-type="e3es/project"]');
      bannerBlock = doc.querySelector('.wp-block[data-type="e3es/intro-banner"]');
      if (block) {
        return { 
          context: 'iframe', 
          data: checkElement(block),
          project: projectBlock ? { width: window.getComputedStyle(projectBlock).width, maxWidth: window.getComputedStyle(projectBlock).maxWidth } : null,
          banner: bannerBlock ? { width: window.getComputedStyle(bannerBlock).width, maxWidth: window.getComputedStyle(bannerBlock).maxWidth } : null
        };
      }
    }

    // Try finding in standard DOM (non-iframe editor)
    block = document.querySelector('.wp-block[data-type="e3es/video-embed"]');
    projectBlock = document.querySelector('.wp-block[data-type="e3es/project"]');
    bannerBlock = document.querySelector('.wp-block[data-type="e3es/intro-banner"]');
    if (block) {
      return { 
        context: 'parent', 
        data: checkElement(block),
        project: projectBlock ? { width: window.getComputedStyle(projectBlock).width, maxWidth: window.getComputedStyle(projectBlock).maxWidth, class: projectBlock.className } : null,
        banner: bannerBlock ? { width: window.getComputedStyle(bannerBlock).width, maxWidth: window.getComputedStyle(bannerBlock).maxWidth, class: bannerBlock.className } : null
      };
    }

    // Fallback: search by class name
    block = document.querySelector('.db-video-section');
    if (block) {
      return { context: 'fallback-parent-class', data: checkElement(block) };
    }
    
    if (iframe) {
      const doc = iframe.contentDocument || iframe.contentWindow.document;
      block = doc.querySelector('.db-video-section');
      if (block) {
        return { context: 'fallback-iframe-class', data: checkElement(block) };
      }
    }

    return { found: false, html: document.body.innerHTML.substring(0, 1000) };
  });

  console.log('--- BLOCK ANALYSIS RESULT ---');
  console.log(JSON.stringify(result, null, 2));

  // Scroll the video block into view
  await page.evaluate(() => {
    const block = document.querySelector('.wp-block[data-type="e3es/video-embed"]');
    if (block) {
      block.scrollIntoView({ block: 'center' });
    } else {
      const iframe = document.querySelector('iframe[name="editor-canvas"]');
      if (iframe) {
        const doc = iframe.contentDocument || iframe.contentWindow.document;
        const iframeBlock = doc.querySelector('.wp-block[data-type="e3es/video-embed"]');
        if (iframeBlock) iframeBlock.scrollIntoView({ block: 'center' });
      }
    }
  });
  await new Promise(resolve => setTimeout(resolve, 2000));

  // Take a screenshot of the block editor to confirm visually
  const screenshotPath = '/Users/bryanpaul/.gemini/antigravity/brain/b9c8b880-8835-4792-8e98-4e16468a4b3a/edcouch-editor-screenshot.png';
  console.log(`Saving screenshot to ${screenshotPath}...`);
  await page.screenshot({ path: screenshotPath, fullPage: false });
  console.log('Screenshot saved.');

  await browser.close();

  // Clean up auto-login.php
  const autoLoginPath = '/Users/bryanpaul/Local Sites/e3es2026/app/public/auto-login.php';
  if (fs.existsSync(autoLoginPath)) {
    fs.unlinkSync(autoLoginPath);
    console.log('Cleaned up auto-login.php.');
  }
}

run().catch(err => {
  console.error('Test script failed:', err);
  process.exit(1);
});
