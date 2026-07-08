const url = 'http://e3es2026.local/wp-json/wp/v2/services?slug=ventilation-upgrades-2';
fetch(url)
  .then(res => res.json())
  .then(data => {
    if(data.length > 0) {
      console.log(data[0].content.rendered.substring(0, 1000));
    } else {
      console.log("Not found");
    }
  })
  .catch(console.error);
