const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { requireAdmin, requireKarigar } = require('../middleware/auth');

// ============ AUTH CHECK ============
router.get('/me', (req, res) => {
  if (req.session.admin_id) {
    return res.json({ role: 'admin', name: req.session.admin_name || 'Admin' });
  }
  if (req.session.karigar_id) {
    return res.json({ role: 'karigar', name: req.session.name, id: req.session.karigar_id });
  }
  res.status(401).json({ error: 'Not authenticated' });
});

// ============ CATEGORIES ============
router.get('/categories', async (req, res) => {
  try {
    const [rows] = await db.query('SELECT * FROM categories ORDER BY name ASC');
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

router.get('/categories/:id/price', async (req, res) => {
  try {
    const [rows] = await db.query('SELECT price FROM categories WHERE id = ?', [req.params.id]);
    res.json({ price: rows[0]?.price || 0 });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ============ KARIGARS ============
router.get('/karigars', requireAdmin, async (req, res) => {
  try {
    const search = req.query.search || '';
    let query = 'SELECT id, name, mobile, email FROM karigars';
    const params = [];
    if (search) {
      query += ' WHERE name LIKE ? OR mobile LIKE ? OR email LIKE ?';
      params.push(`%${search}%`, `%${search}%`, `%${search}%`);
    }
    query += ' ORDER BY id DESC';
    const [rows] = await db.query(query, params);
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

router.get('/karigars/:id', requireAdmin, async (req, res) => {
  try {
    const [rows] = await db.query('SELECT id, name, mobile, email FROM karigars WHERE id = ?', [req.params.id]);
    if (rows.length === 0) return res.status(404).json({ error: 'Not found' });
    res.json(rows[0]);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

router.get('/karigars/:id/profile', requireAdmin, async (req, res) => {
  const { filter } = req.query;
  const id = req.params.id;
  let dateFilter = '';
  const params = [id];

  if (filter === 'weekly') {
    dateFilter = ' AND we.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
  } else if (filter === 'monthly') {
    dateFilter = ' AND MONTH(we.date) = MONTH(CURDATE()) AND YEAR(we.date) = YEAR(CURDATE())';
  }

  try {
    const [karigar] = await db.query('SELECT * FROM karigars WHERE id = ?', [id]);
    const [entries] = await db.query(
      `SELECT we.*, c.name AS category_name 
       FROM work_entries we 
       JOIN categories c ON we.category = c.id 
       WHERE we.karigar_id = ?${dateFilter}
       ORDER BY we.date DESC`,
      params
    );
    const [totals] = await db.query(
      'SELECT SUM(quantity) AS total_qty, SUM(total) AS total_earned FROM work_entries WHERE karigar_id = ?',
      [id]
    );
    res.json({ karigar: karigar[0], entries, totals: totals[0] });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ============ WORK ENTRIES ============
router.get('/work-entries', requireAdmin, async (req, res) => {
  const { karigar_id, period, from_date, to_date } = req.query;
  let where = '1=1';
  const params = [];

  if (karigar_id && karigar_id !== '0') {
    where += ' AND we.karigar_id = ?';
    params.push(karigar_id);
  }
  if (period === 'weekly') {
    where += ' AND we.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
  } else if (period === 'monthly') {
    where += ' AND we.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
  } else if (period === 'custom' && from_date && to_date) {
    where += ' AND we.date BETWEEN ? AND ?';
    params.push(from_date, to_date);
  }

  try {
    const [entries] = await db.query(
      `SELECT we.*, c.name AS category_name, k.name AS karigar_name
       FROM work_entries we
       JOIN karigars k ON we.karigar_id = k.id
       JOIN categories c ON we.category = c.id
       WHERE ${where}
       ORDER BY we.date DESC`,
      params
    );
    res.json(entries);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ============ DASHBOARD STATS ============
router.get('/dashboard-stats', requireAdmin, async (req, res) => {
  try {
    const [totalPayments] = await db.query('SELECT SUM(total) AS total FROM work_entries');
    const [totalItems] = await db.query('SELECT SUM(quantity) AS items FROM work_entries');
    const [activeKarigars] = await db.query('SELECT COUNT(DISTINCT karigar_id) AS count FROM work_entries');
    const [itemBreakdown] = await db.query(`
      SELECT c.name AS category_name, SUM(w.quantity) as count
      FROM work_entries w
      JOIN categories c ON w.category = c.id
      GROUP BY w.category
    `);

    // Filtered stats
    const { karigar_id, period, from_date, to_date } = req.query;
    let where = '1=1';
    const params = [];

    if (karigar_id && karigar_id !== '0') {
      where += ' AND karigar_id = ?';
      params.push(karigar_id);
    }
    if (period === 'weekly') {
      where += ' AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
    } else if (period === 'monthly') {
      where += ' AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
    } else if (period === 'custom' && from_date && to_date) {
      where += ' AND date BETWEEN ? AND ?';
      params.push(from_date, to_date);
    }

    const [filteredTotal] = await db.query(
      `SELECT SUM(total) AS total_amount FROM work_entries WHERE ${where}`, params
    );

    // Monthly chart data (last 6 months)
    const [chartData] = await db.query(`
      SELECT DATE_FORMAT(date, '%Y-%m') AS month, 
             SUM(quantity) AS items, 
             SUM(total) AS payment
      FROM work_entries
      GROUP BY DATE_FORMAT(date, '%Y-%m')
      ORDER BY month DESC
      LIMIT 6
    `);

    res.json({
      totalPayments: totalPayments[0].total || 0,
      totalItems: totalItems[0].items || 0,
      activeKarigars: activeKarigars[0].count || 0,
      itemBreakdown,
      filteredTotal: filteredTotal[0].total_amount || 0,
      chartData: chartData.reverse()
    });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ============ KARIGAR DASHBOARD STATS ============
router.get('/karigar-stats', requireKarigar, async (req, res) => {
  const karigar_id = req.session.karigar_id;
  const filter = req.query.filter || 'weekly';

  let dateFilter = '';
  if (filter === 'weekly') {
    dateFilter = ' AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
  } else if (filter === 'monthly') {
    dateFilter = ' AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())';
  }

  try {
    const [totals] = await db.query(
      `SELECT SUM(total) AS total_amount, SUM(quantity) AS total_items 
       FROM work_entries 
       WHERE karigar_id = ?${dateFilter}`,
      [karigar_id]
    );
    const [entries] = await db.query(
      `SELECT we.*, c.name AS category_name 
       FROM work_entries we 
       JOIN categories c ON we.category = c.id 
       WHERE we.karigar_id = ?${dateFilter}
       ORDER BY date DESC LIMIT 10`,
      [karigar_id]
    );
    res.json({
      totalAmount: totals[0].total_amount || 0,
      totalItems: totals[0].total_items || 0,
      entries
    });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ============ KARIGAR ALL RECORDS ============
router.get('/karigar-records', requireKarigar, async (req, res) => {
  const karigar_id = req.session.karigar_id;
  try {
    const [rows] = await db.query(
      `SELECT we.*, c.name AS category_name 
       FROM work_entries we 
       JOIN categories c ON we.category = c.id 
       WHERE we.karigar_id = ? 
       ORDER BY we.date DESC`,
      [karigar_id]
    );
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
