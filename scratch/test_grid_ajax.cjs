const fs = require('fs');

async function run() {
  const url = 'https://www.e3es.com/wp-admin/admin-ajax.php';
  const bodyParams = new URLSearchParams();
  bodyParams.append('action', 'vc_get_vc_grid_data');
  bodyParams.append('vc_action', 'vc_get_vc_grid_data');
  bodyParams.append('tag', 'vc_masonry_media_grid');
  bodyParams.append('data[page_id]', '5599');
  bodyParams.append('data[style]', 'lazy-masonry');
  bodyParams.append('data[action]', 'vc_get_vc_grid_data');
  bodyParams.append('data[shortcode_id]', '1751482599976-2c901626-631f-7');
  bodyParams.append('data[items_per_page]', '10');
  bodyParams.append('data[tag]', 'vc_masonry_media_grid');
  bodyParams.append('vc_post_id', '5599');
  bodyParams.append('_wpnonce', 'd62d15fc04');

  const res = await fetch(url, {
    method: 'POST',
    body: bodyParams,
    headers: {
      'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
      'Content-Type': 'application/x-www-form-urlencoded'
    }
  });
  const text = await res.text();
  console.log('Response length:', text.length);
  fs.writeFileSync('scratch/grid_response.html', text);
}
run().catch(err => console.error(err));
