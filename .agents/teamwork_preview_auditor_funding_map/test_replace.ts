import { processWordPressHtml, TEXAS_MAP_SVG } from '../../src/lib/wordpress';

async function main() {
  const WP_URL = 'http://e3es2026.local/wp-json/wp/v2';
  console.log('Fetching page 9...');
  const res = await fetch(`${WP_URL}/pages/9`);
  const page = await res.json();
  const rawHtml = page.content.rendered;
  
  console.log('--- Tracing Inline Implementation ---');
  let processedHtmlInline = rawHtml;
  const mapRegex = /<img[^>]*static-map-600x400\.png[^>]*>/gi;
  
  const testVal = mapRegex.test(processedHtmlInline);
  console.log('mapRegex.test returned:', testVal);
  console.log('lastIndex after test:', mapRegex.lastIndex);
  
  if (testVal) {
    console.log('Running replace...');
    processedHtmlInline = processedHtmlInline.replace(mapRegex, 'REPLACED_SVG_MAP');
  }
  
  console.log('Inline processed contains static-map:', processedHtmlInline.includes('static-map-600x400.png'));
  console.log('Inline processed contains REPLACED_SVG_MAP:', processedHtmlInline.includes('REPLACED_SVG_MAP'));
  
  console.log('--- Tracing Imported function ---');
  const processedHtmlImported = processWordPressHtml(rawHtml);
  console.log('Imported processed contains static-map:', processedHtmlImported.includes('static-map-600x400.png'));
  console.log('Imported processed contains texas-map-svg:', processedHtmlImported.includes('texas-map-svg'));
}

main().catch(console.error);
