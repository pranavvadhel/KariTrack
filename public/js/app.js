// Shared sidebar renderer for admin pages
function renderAdminSidebar(activePage) {
  const links = [
    { href: '/admin/dashboard', icon: '📊', label: 'Dashboard', key: 'dashboard' },
    { href: '/admin/karigars', icon: '👷', label: 'View Karigars', key: 'karigars' },
    { href: '/admin/add-karigar', icon: '➕', label: 'Add Karigar', key: 'add-karigar' },
    { href: '/admin/work-entry', icon: '📝', label: 'Add Work', key: 'work-entry' },
    { href: '/admin/reports', icon: '📈', label: 'Performance', key: 'reports' },
    { href: '/admin/categories', icon: '🏷️', label: 'Categories', key: 'categories' },
  ];

  return `
    <button class="nav-toggle" id="navToggle" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <div class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <h4>🧵 KariTrack</h4>
        <p>Admin Panel</p>
      </div>
      <nav class="sidebar-nav">
        ${links.map(l => `
          <a href="${l.href}" class="${activePage === l.key ? 'active' : ''}">
            <span class="nav-icon">${l.icon}</span>${l.label}
          </a>
        `).join('')}
        <a href="/logout" class="nav-logout">
          <span class="nav-icon">🚪</span>Logout
        </a>
      </nav>
    </div>
  `;
}

function renderKarigarSidebar(activePage) {
  const links = [
    { href: '/karigar/dashboard', icon: '📊', label: 'Dashboard', key: 'dashboard' },
    { href: '/karigar/my-records', icon: '📋', label: 'My Work', key: 'my-records' },
  ];
  return `
    <button class="nav-toggle" id="navToggle" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <div class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <h4>🧵 My Panel</h4>
        <p>Karigar Dashboard</p>
      </div>
      <nav class="sidebar-nav">
        ${links.map(l => `
          <a href="${l.href}" class="${activePage === l.key ? 'active' : ''}">
            <span class="nav-icon">${l.icon}</span>${l.label}
          </a>
        `).join('')}
        <a href="/logout" class="nav-logout">
          <span class="nav-icon">🚪</span>Logout
        </a>
      </nav>
    </div>
  `;
}

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const btn = document.getElementById('navToggle');
  
  sidebar.classList.toggle('open');
  overlay.classList.toggle('show');
  
  if (sidebar.classList.contains('open')) {
    btn.textContent = '✕';
  } else {
    btn.textContent = '☰';
  }
}

function formatCurrency(val) {
  return '₹' + parseFloat(val || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-IN');
}

function showAlert(containerId, msg, type = 'success') {
  const icon = type === 'success' ? '✅' : '⚠️';
  document.getElementById(containerId).innerHTML = `<div class="alert alert-${type}">${icon} ${msg}</div>`;
  setTimeout(() => {
    const el = document.getElementById(containerId);
    if (el) el.innerHTML = '';
  }, 4000);
}

async function apiGet(url) {
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10s timeout

    const res = await fetch(url, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (res.status === 401) { location.href = '/'; return null; }
    if (!res.ok) {
      const err = await res.json();
      throw new Error(err.error || 'Server error');
    }
    return res.json();
  } catch (err) {
    console.error('API Get Error:', err);
    return null;
  }
}

async function apiPost(url, data) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });
  return res.json();
}

function togglePassword(id, btn) {
  const input = document.getElementById(id);
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '👁️';
  } else {
    input.type = 'password';
    btn.textContent = '👁️‍🗨️';
  }
}
