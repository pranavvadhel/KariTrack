const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const db = require('../config/db');
const { requireAdmin } = require('../middleware/auth');
const path = require('path');

// All admin routes require auth
router.use(requireAdmin);

// Serve static HTML with session data injected via JSON
function sendAdminPage(res, page) {
  res.sendFile(path.join(__dirname, '../views/admin', page));
}

// GET Dashboard
router.get('/dashboard', async (req, res) => {
  sendAdminPage(res, 'dashboard.html');
});

// GET View Karigars
router.get('/karigars', async (req, res) => {
  sendAdminPage(res, 'view_karigars.html');
});

// GET Add Karigar
router.get('/add-karigar', (req, res) => {
  sendAdminPage(res, 'add_karigar.html');
});

// POST Add Karigar
router.post('/add-karigar', async (req, res) => {
  const { karigar_name, karigar_phone, karigar_email } = req.body;
  try {
    const [existing] = await db.query(
      'SELECT * FROM karigars WHERE mobile = ? OR email = ?',
      [karigar_phone, karigar_email]
    );
    if (existing.length > 0) {
      return res.redirect('/admin/add-karigar?error=Phone+or+email+already+exists');
    }
    const password = await bcrypt.hash('default123', 10);
    await db.query(
      'INSERT INTO karigars (name, mobile, email, password) VALUES (?, ?, ?, ?)',
      [karigar_name, karigar_phone, karigar_email, password]
    );
    res.redirect('/admin/karigars?success=Karigar+added');
  } catch (err) {
    console.error(err);
    res.redirect('/admin/add-karigar?error=Server+error');
  }
});

// GET Edit Karigar
router.get('/edit-karigar/:id', async (req, res) => {
  sendAdminPage(res, 'edit_karigar.html');
});

// POST Edit Karigar
router.post('/edit-karigar/:id', async (req, res) => {
  const { id } = req.params;
  const { name } = req.body;
  try {
    await db.query('UPDATE karigars SET name = ? WHERE id = ?', [name, id]);
    res.redirect('/admin/karigars?success=Karigar+updated');
  } catch (err) {
    console.error(err);
    res.redirect('/admin/karigars?error=Update+failed');
  }
});

// POST Delete Karigar
router.post('/delete-karigar/:id', async (req, res) => {
  const { id } = req.params;
  try {
    await db.query('DELETE FROM karigars WHERE id = ?', [id]);
    res.json({ success: true });
  } catch (err) {
    res.json({ success: false, error: err.message });
  }
});

// GET Karigar Profile
router.get('/karigar-profile/:id', (req, res) => {
  sendAdminPage(res, 'karigar_profile.html');
});

// GET Work Entry
router.get('/work-entry', (req, res) => {
  sendAdminPage(res, 'work_entry.html');
});

// POST Work Entry
router.post('/work-entry', async (req, res) => {
  const { karigar_id, category, quantity, date } = req.body;
  try {
    const [catRows] = await db.query('SELECT price FROM categories WHERE id = ?', [category]);
    if (catRows.length === 0) return res.redirect('/admin/work-entry?error=Category+not+found');

    const price_per_item = parseFloat(catRows[0].price);
    const total = price_per_item * parseInt(quantity);

    await db.query(
      'INSERT INTO work_entries (karigar_id, category, quantity, price_per_item, total, date) VALUES (?, ?, ?, ?, ?, ?)',
      [karigar_id, category, quantity, price_per_item, total, date]
    );
    res.redirect('/admin/work-entry?success=1');
  } catch (err) {
    console.error(err);
    res.redirect('/admin/work-entry?error=Server+error');
  }
});

// GET Reports
router.get('/reports', (req, res) => {
  sendAdminPage(res, 'reports.html');
});

// GET Categories
router.get('/categories', (req, res) => {
  sendAdminPage(res, 'categories.html');
});

// POST Add Category
router.post('/categories/add', async (req, res) => {
  const { category_name, price } = req.body;
  try {
    const [existing] = await db.query('SELECT id FROM categories WHERE name = ?', [category_name]);
    if (existing.length > 0) {
      return res.json({ success: false, error: 'Category already exists' });
    }
    const [result] = await db.query('INSERT INTO categories (name, price) VALUES (?, ?)', [category_name, price]);
    res.json({ success: true, id: result.insertId });
  } catch (err) {
    res.json({ success: false, error: err.message });
  }
});

// POST Update Category
router.post('/categories/update', async (req, res) => {
  const { category_id, category_name, price } = req.body;
  try {
    await db.query('UPDATE categories SET name = ?, price = ? WHERE id = ?', [category_name, price, category_id]);
    res.json({ success: true });
  } catch (err) {
    res.json({ success: false, error: err.message });
  }
});

// POST Delete Category
router.post('/categories/delete/:id', async (req, res) => {
  const { id } = req.params;
  try {
    await db.query('DELETE FROM categories WHERE id = ?', [id]);
    res.json({ success: true });
  } catch (err) {
    res.json({ success: false, error: err.message });
  }
});

// POST Delete Work Entry
router.post('/delete-work/:id', async (req, res) => {
  const { id } = req.params;
  try {
    await db.query('DELETE FROM work_entries WHERE id = ?', [id]);
    res.json({ success: true });
  } catch (err) {
    res.json({ success: false, error: err.message });
  }
});

module.exports = router;
