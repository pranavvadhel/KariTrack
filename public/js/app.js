// Theme Management
function initTheme() {
  const theme = localStorage.getItem('karitrack_theme') || 'dark';
  document.body.setAttribute('data-theme', theme);
  // Also set on documentElement to be safe
  document.documentElement.setAttribute('data-theme', theme);
}

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  document.body.setAttribute('data-theme', next);
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('karitrack_theme', next);
}

// Global initialization
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
});

function renderThemeToggle() {
  const isLight = document.documentElement.getAttribute('data-theme') === 'light';
  return `
    <div class="theme-switch-wrapper">
      <span>☀️ Light Mode</span>
      <label class="switch">
        <input type="checkbox" id="themeToggle" onchange="toggleTheme()" ${isLight ? 'checked' : ''}>
        <span class="slider round"></span>
      </label>
    </div>
  `;
}

// Update Sidebars to include Profile and Toggle after Logout
function renderAdminSidebar(activePage) {
  const links = [
    { href: '/admin/dashboard', icon: '📊', label: 'Dashboard', key: 'dashboard' },
    { href: '/admin/karigars', icon: '👷', label: 'View Karigars', key: 'karigars' },
    { href: '/admin/add-karigar', icon: '➕', label: 'Add Karigar', key: 'add-karigar' },
    { href: '/admin/work-entry', icon: '📝', label: 'Add Work', key: 'work-entry' },
    { href: '/admin/reports', icon: '📈', label: 'Performance', key: 'reports' },
    { href: '/admin/payroll', icon: '💰', label: 'Payroll', key: 'payroll' },
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
          <div class="logout-gate">
            <div class="gate-door"></div>
            <span class="gate-arrow">🚶</span>
          </div>
          Logout
        </a>
        ${renderThemeToggle()}
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
      <div class="sidebar-brand clickable" onclick="location.href='/karigar/profile'">
        <div class="avatar-circle">
          <span id="sidebar-avatar">K</span>
        </div>
        <div>
          <h4>KariTrack</h4>
          <p id="sidebar-name">Karigar Dashboard</p>
        </div>
      </div>
      <nav class="sidebar-nav">
        ${links.map(l => `
          <a href="${l.href}" class="${activePage === l.key ? 'active' : ''}">
            <span class="nav-icon">${l.icon}</span>${l.label}
          </a>
        `).join('')}
        <a href="/logout" class="nav-logout">
          <div class="logout-gate">
            <div class="gate-door"></div>
            <span class="gate-arrow">🚶</span>
          </div>
          Logout
        </a>
        ${renderThemeToggle()}
      </nav>
    </div>
  `;
}

// Helper to fetch user name for sidebar
async function updateSidebarInfo() {
    const me = await apiGet('/api/me');
    if (me && me.name) {
        const nameEl = document.getElementById('sidebar-name');
        const avatarEl = document.getElementById('sidebar-avatar');
        if (nameEl) nameEl.textContent = me.name;
        if (avatarEl) avatarEl.textContent = me.name.charAt(0).toUpperCase();
    }
}
document.addEventListener('DOMContentLoaded', updateSidebarInfo);


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

const EYE_OPEN = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
const EYE_CLOSED = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;

function togglePassword(id, btn) {
  const input = document.getElementById(id);
  if (input.type === 'password') {
    input.type = 'text';
    btn.innerHTML = EYE_OPEN;
  } else {
    input.type = 'password';
    btn.innerHTML = EYE_CLOSED;
  }
}

// Wrap all eye toggles on page load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.innerHTML = EYE_CLOSED;
    });
});
