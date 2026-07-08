# Plan to Fix Headless Previews
1. Add `/preview-data` endpoint to `e3es-headless-helper.php` to securely return the drafted/autosaved post title and content based on `wp_edit_token`.
2. Add a global preview script to Astro's `Layout.astro` that intercepts `wp_edit_token`, fetches the data, and updates the DOM dynamically.
