const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = 4011;
const JSON_PATH = '/Users/bryanpaul/Local Sites/astro-e3es/src/data/progress.json';
const MD_PATH = '/Users/bryanpaul/.gemini/antigravity/brain/b9c8b880-8835-4792-8e98-4e16468a4b3a/progress.md';

// Helper to convert JSON back to the exact progress.md layout
function syncToMarkdown(tasks) {
  let md = '# E3 Website Implementation Progress\n\n---\n\n## Section 1: Currently Working On\n\n';
  const working = tasks.filter(t => t.status === 'working');
  if (working.length === 0) {
    md += '*(No tasks currently in progress)*\n';
  } else {
    working.forEach(t => {
      md += `<div style="font-size: 3.5rem; margin: 2rem 0 0.5rem 0;">${t.emoji}</div>\n\n`;
      md += `**${t.name}**: ${t.description}`;
      if (t.links && t.links.length > 0) {
        md += ' ' + t.links.map(l => `[${l.text}](${l.url})`).join(' | ');
      }
      md += '\n\n';
    });
  }

  md += '---\n\n## Section 2: For Review\n\n';
  const review = tasks.filter(t => t.status === 'review');
  if (review.length === 0) {
    md += '*(No tasks currently in review)*\n';
  } else {
    review.forEach(t => {
      md += `<div style="font-size: 3.5rem; margin: 2rem 0 0.5rem 0;">${t.emoji}</div>\n\n`;
      md += `**${t.name}**: ${t.description}`;
      if (t.links && t.links.length > 0) {
        md += ' ' + t.links.map(l => `[${l.text}](${l.url})`).join(' | ');
      }
      md += '\n\n';
    });
  }

  md += '---\n\n## Section 3: Approved\n\n';
  const approved = tasks.filter(t => t.status === 'approved');
  if (approved.length === 0) {
    md += '*(No tasks currently approved)*\n';
  } else {
    approved.forEach(t => {
      md += `<div style="font-size: 3.5rem; margin: 2rem 0 0.5rem 0;">${t.emoji}</div>\n\n`;
      md += `**${t.name}**: ${t.description}`;
      if (t.links && t.links.length > 0) {
        md += ' ' + t.links.map(l => `[${l.text}](${l.url})`).join(' | ');
      }
      md += '\n\n';
    });
  }

  fs.writeFileSync(MD_PATH, md);
}

// Generate the beautiful dashboard HTML
function getDashboardHtml() {
  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E3 Progress Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --color-primary: #215734;
      --color-primary-dark: #163c24;
      --color-primary-light: #2c7245;
      --color-bg-sage: #f2f7f4;
      --color-dark: #111e15;
      --color-gray-light: #f7faf8;
      --color-border: #e0e8e3;
      --font-main: 'Inter', sans-serif;
      --font-display: 'Outfit', sans-serif;
    }

    body {
      font-family: var(--font-main);
      background-color: var(--color-bg-sage);
      color: var(--color-dark);
      margin: 0;
      padding: 3rem 1.5rem;
      line-height: 1.6;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    header {
      margin-bottom: 3rem;
      text-align: center;
    }

    h1 {
      font-family: var(--font-display);
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--color-primary);
      margin: 0 0 0.5rem 0;
      text-transform: uppercase;
      letter-spacing: -0.5px;
    }

    .subtitle {
      font-size: 1.1rem;
      color: #607065;
      margin: 0;
    }

    .sections-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 2.5rem;
    }

    @media (min-width: 900px) {
      .sections-grid {
        grid-template-columns: 1fr 1fr 1fr;
      }
    }

    .section-col {
      background: #ffffff;
      border: 1px solid var(--color-border);
      padding: 2rem 1.5rem;
      box-shadow: 0 8px 24px rgba(33, 87, 52, 0.04);
    }

    .section-col h2 {
      font-family: var(--font-display);
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--color-primary);
      margin-top: 0;
      margin-bottom: 1.5rem;
      border-bottom: 2px solid var(--color-bg-sage);
      padding-bottom: 0.75rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .task-card {
      border: 1px solid var(--color-border);
      background: var(--color-gray-light);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      transition: all 0.2s ease-in-out;
      position: relative;
    }

    .task-card:hover {
      box-shadow: 0 4px 16px rgba(33, 87, 52, 0.08);
      border-color: var(--color-primary-light);
    }

    .task-emoji {
      font-size: 2.5rem;
      margin-bottom: 0.75rem;
    }

    .task-name {
      font-family: var(--font-display);
      font-size: 1.15rem;
      font-weight: 700;
      margin: 0 0 0.5rem 0;
      color: var(--color-primary-dark);
    }

    .task-desc {
      font-size: 0.95rem;
      color: #4a584f;
      margin: 0 0 1rem 0;
    }

    .task-links {
      margin-bottom: 1rem;
      font-size: 0.85rem;
    }

    .task-links a {
      color: var(--color-primary);
      text-decoration: none;
      font-weight: 600;
      margin-right: 0.75rem;
      display: inline-block;
    }

    .task-links a:hover {
      text-decoration: underline;
    }

    .comments-section {
      border-top: 1px solid var(--color-border);
      padding-top: 1rem;
      margin-top: 1rem;
    }

    .comments-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: #708075;
      margin-bottom: 0.5rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .comment-item {
      font-size: 0.85rem;
      background: #ffffff;
      border: 1px solid var(--color-border);
      padding: 0.5rem 0.75rem;
      margin-bottom: 0.5rem;
    }

    .comment-user {
      font-weight: 700;
      color: var(--color-primary-dark);
      margin-right: 0.25rem;
    }

    .add-comment-form {
      display: flex;
      gap: 0.5rem;
      margin-top: 0.5rem;
    }

    .add-comment-input {
      flex: 1;
      font-family: var(--font-main);
      font-size: 0.85rem;
      padding: 0.4rem 0.6rem;
      border: 1px solid var(--color-border);
      outline: none;
    }

    .add-comment-input:focus {
      border-color: var(--color-primary);
    }

    .btn {
      font-family: var(--font-main);
      font-size: 0.85rem;
      font-weight: 600;
      padding: 0.4rem 0.8rem;
      border: none;
      background: var(--color-primary);
      color: white;
      cursor: pointer;
      transition: background 0.15s;
    }

    .btn:hover {
      background: var(--color-primary-dark);
    }

    .btn-secondary {
      background: #708075;
    }

    .btn-secondary:hover {
      background: #506055;
    }

    .task-actions {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    .add-task-btn-container {
      text-align: center;
      margin-top: 3rem;
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content {
      background: white;
      padding: 2.5rem;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 12px 32px rgba(0,0,0,0.15);
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-group label {
      display: block;
      font-weight: 700;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .form-control {
      width: 100%;
      padding: 0.6rem;
      border: 1px solid var(--color-border);
      font-family: var(--font-main);
      box-sizing: border-box;
      outline: none;
    }

    .form-control:focus {
      border-color: var(--color-primary);
    }

    .toast {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: var(--color-primary-dark);
      color: white;
      padding: 1rem 1.5rem;
      font-weight: 600;
      box-shadow: 0 4px 16px rgba(0,0,0,0.1);
      display: none;
      z-index: 2000;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>E3 Implementation Dashboard</h1>
      <p class="subtitle">Interactive checklist and feedback manager</p>
    </header>

    <div class="sections-grid">
      <!-- Section 1: Working -->
      <div class="section-col">
        <h2>🚧 Working On</h2>
        <div id="col-working"></div>
      </div>

      <!-- Section 2: Review -->
      <div class="section-col">
        <h2>📋 For Review</h2>
        <div id="col-review"></div>
      </div>

      <!-- Section 3: Approved -->
      <div class="section-col">
        <h2>✅ Approved</h2>
        <div id="col-approved"></div>
      </div>
    </div>

    <div class="add-task-btn-container">
      <button class="btn" style="padding: 0.75rem 1.5rem; font-size: 1rem;" onclick="showAddModal()">➕ Add New Task</button>
    </div>
  </div>

  <!-- Add Task Modal -->
  <div class="modal" id="add-modal">
    <div class="modal-content">
      <h2 style="margin-top: 0; font-family: var(--font-display); color: var(--color-primary);">Add New Task</h2>
      <div class="form-group">
        <label for="task-name-input">Task Name</label>
        <input type="text" id="task-name-input" class="form-control" placeholder="e.g. Navigation Breadcrumbs">
      </div>
      <div class="form-group">
        <label for="task-emoji-input">Emoji Icon</label>
        <input type="text" id="task-emoji-input" class="form-control" placeholder="e.g. 🧭" value="🧭">
      </div>
      <div class="form-group">
        <label for="task-desc-input">Description</label>
        <textarea id="task-desc-input" class="form-control" rows="3" placeholder="Describe the changes made..."></textarea>
      </div>
      <div class="form-group">
        <label for="task-link-input">Review Link (Optional)</label>
        <input type="text" id="task-link-input" class="form-control" placeholder="e.g. http://localhost:4008/clients">
      </div>
      <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 2rem;">
        <button class="btn btn-secondary" onclick="hideAddModal()">Cancel</button>
        <button class="btn" onclick="saveNewTask()">Save Task</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast">Saved Successfully!</div>

  <script>
    let tasksList = [];

    async function loadTasks() {
      const res = await fetch('/api/tasks');
      tasksList = await res.json();
      renderTasks();
    }

    function renderTasks() {
      const cols = {
        working: document.getElementById('col-working'),
        review: document.getElementById('col-review'),
        approved: document.getElementById('col-approved')
      };

      // Clear columns
      Object.values(cols).forEach(c => c.innerHTML = '');

      tasksList.forEach(task => {
        const card = document.createElement('div');
        card.className = 'task-card';
        
        let linksHtml = '';
        if (task.links && task.links.length > 0) {
          linksHtml = \`<div class="task-links">\` + 
            task.links.map(l => \`<a href="\${l.url}" target="_blank">\${l.text}</a>\`).join('') + 
            \`</div>\`;
        }

        let commentsHtml = '';
        if (task.comments) {
          const items = task.comments.map(c => 
            \`<div class="comment-item"><span class="comment-user">\${c.user}:</span>\${c.text}</div>\`
          ).join('');
          commentsHtml = \`
            <div class="comments-section">
              <div class="comments-title">Feedback / History</div>
              \${items}
              <div class="add-comment-form">
                <input type="text" placeholder="Add feedback..." class="add-comment-input" id="comment-input-\${task.id}">
                <button class="btn" onclick="addComment('\${task.id}')">Send</button>
              </div>
            </div>
          \`;
        }

        let actionBtns = '';
        if (task.status === 'working') {
          actionBtns = \`
            <button class="btn" onclick="moveTask('\${task.id}', 'review')">Send for Review 📋</button>
          \`;
        } else if (task.status === 'review') {
          actionBtns = \`
            <button class="btn" onclick="moveTask('\${task.id}', 'approved')">Approve ✅</button>
            <button class="btn btn-secondary" onclick="moveTask('\${task.id}', 'working')">Request Changes 🚧</button>
          \`;
        } else if (task.status === 'approved') {
          actionBtns = \`
            <button class="btn btn-secondary" onclick="moveTask('\${task.id}', 'review')">Re-review 📋</button>
          \`;
        }

        card.innerHTML = \`
          <div class="task-emoji">\${task.emoji}</div>
          <h3 class="task-name">\${task.name}</h3>
          <p class="task-desc">\${task.description}</p>
          \${linksHtml}
          <div class="task-actions">
            \${actionBtns}
            <button class="btn btn-secondary" style="background:#cc3333; margin-left:auto;" onclick="deleteTask('\${task.id}')">🗑️</button>
          </div>
          \${commentsHtml}
        \`;

        cols[task.status].appendChild(card);
      });
    }

    async function moveTask(taskId, newStatus) {
      const task = tasksList.find(t => t.id === taskId);
      if (task) {
        task.status = newStatus;
        if (newStatus === 'approved') {
          task.comments.push({ user: 'User', text: 'Approved' });
        }
        await saveTasksToServer();
      }
    }

    async function addComment(taskId) {
      const input = document.getElementById(\`comment-input-\${taskId}\`);
      const text = input.value.trim();
      if (text) {
        const task = tasksList.find(t => t.id === taskId);
        if (task) {
          task.comments.push({ user: 'User', text });
          input.value = '';
          await saveTasksToServer();
        }
      }
    }

    async function deleteTask(taskId) {
      if (confirm('Are you sure you want to delete this task?')) {
        tasksList = tasksList.filter(t => t.id !== taskId);
        await saveTasksToServer();
      }
    }

    function showAddModal() {
      document.getElementById('add-modal').style.display = 'flex';
    }

    function hideAddModal() {
      document.getElementById('add-modal').style.display = 'none';
    }

    async function saveNewTask() {
      const name = document.getElementById('task-name-input').value.trim();
      const emoji = document.getElementById('task-emoji-input').value.trim();
      const description = document.getElementById('task-desc-input').value.trim();
      const linkUrl = document.getElementById('task-link-input').value.trim();

      if (!name || !description) {
        alert('Please fill out Name and Description.');
        return;
      }

      const newTask = {
        id: name.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
        name,
        emoji,
        description,
        links: linkUrl ? [{ text: 'Review Link', url: linkUrl }] : [],
        status: 'working',
        comments: []
      };

      tasksList.push(newTask);
      hideAddModal();
      
      // Clear inputs
      document.getElementById('task-name-input').value = '';
      document.getElementById('task-desc-input').value = '';
      document.getElementById('task-link-input').value = '';

      await saveTasksToServer();
    }

    async function saveTasksToServer() {
      const res = await fetch('/api/tasks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(tasksList)
      });
      if (res.ok) {
        showToast();
        await loadTasks();
      }
    }

    function showToast() {
      const toast = document.getElementById('toast');
      toast.style.display = 'block';
      setTimeout(() => {
        toast.style.display = 'none';
      }, 2000);
    }

    loadTasks();
  </script>
</body>
</html>`;
}

// Simple HTTP router
const server = http.createServer((req, res) => {
  if (req.url === '/' || req.url === '/index.html') {
    res.writeHead(200, { 'Content-Type': 'text/html' });
    res.end(getDashboardHtml());
  } else if (req.url === '/api/tasks' && req.method === 'GET') {
    try {
      const data = fs.readFileSync(JSON_PATH, 'utf8');
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(data);
    } catch (e) {
      res.writeHead(500);
      res.end(JSON.stringify({ error: e.message }));
    }
  } else if (req.url === '/api/tasks' && req.method === 'POST') {
    let body = '';
    req.on('data', chunk => {
      body += chunk.toString();
    });
    req.on('end', () => {
      try {
        const tasks = JSON.parse(body);
        fs.writeFileSync(JSON_PATH, JSON.stringify(tasks, null, 2));
        syncToMarkdown(tasks);
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ success: true }));
      } catch (e) {
        res.writeHead(500);
        res.end(JSON.stringify({ error: e.message }));
      }
    });
  } else {
    res.writeHead(404);
    res.end('Not Found');
  }
});

server.listen(PORT, () => {
  console.log(`Progress Dashboard server running at http://localhost:${PORT}`);
});
