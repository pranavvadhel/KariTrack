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

// ============ SIZES ============
router.get('/sizes', async (req, res) => {
  try {
    const [rows] = await db.query('SELECT * FROM sizes ORDER BY size_name ASC');
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
    const queries = [
      db.query('SELECT SUM(total) AS total FROM work_entries'),
      db.query('SELECT SUM(quantity) AS items FROM work_entries'),
      db.query('SELECT COUNT(DISTINCT karigar_id) AS count FROM work_entries'),
      db.query(`
        SELECT c.name AS category_name, SUM(w.quantity) as count
        FROM work_entries w
        JOIN categories c ON w.category = c.id
        GROUP BY w.category
      `)
    ];

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

    queries.push(db.query(`SELECT SUM(total) AS total_amount FROM work_entries WHERE ${where}`, params));
    queries.push(db.query(`
      SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(quantity) AS items, SUM(total) AS payment
      FROM work_entries GROUP BY month ORDER BY month DESC LIMIT 6
    `));

    const results = await Promise.all(queries);

    res.json({
      totalPayments: results[0][0][0].total || 0,
      totalItems: results[1][0][0].items || 0,
      activeKarigars: results[2][0][0].count || 0,
      itemBreakdown: results[3][0],
      filteredTotal: results[4][0][0].total_amount || 0,
      chartData: results[5][0].reverse()
    });
  } catch (err) {
    console.error('Stats Error:', err);
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

router.get('/payroll/summary', requireAdmin, async (req, res) => {
  const { search, period, start_date, end_date } = req.query;
  
  let karigarConditions = [];
  let karigarParams = [];
  if (search) {
      karigarConditions.push('(k.name LIKE ? OR k.mobile LIKE ?)');
      karigarParams.push(`%${search}%`, `%${search}%`);
  }
  let karigarWhere = karigarConditions.length > 0 ? 'WHERE ' + karigarConditions.join(' AND ') : '';

  let weConditions = [];
  let weParams = [];
  
  if (period === 'weekly') {
      weConditions.push('we.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
  } else if (period === 'monthly') {
      weConditions.push('we.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
  } else if (period === 'custom' && start_date && end_date) {
      weConditions.push('we.date BETWEEN ? AND ?');
      weParams.push(start_date, end_date);
  }
  let weWhere = weConditions.length > 0 ? 'AND ' + weConditions.join(' AND ') : '';

  let allParams = [...karigarParams, ...weParams];

  try {
    const [rows] = await db.query(`
      SELECT k.id, k.name, k.mobile,
             COALESCE(SUM(CASE WHEN we.status = 'unpaid' THEN we.total ELSE 0 END), 0) as unpaid_amount,
             COALESCE(SUM(CASE WHEN we.status = 'paid' THEN we.total ELSE 0 END), 0) as paid_amount,
             COUNT(we.id) as total_entries
      FROM karigars k
      LEFT JOIN work_entries we ON k.id = we.karigar_id ${weWhere}
      ${karigarWhere}
      GROUP BY k.id
    `, allParams);
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

router.post('/payroll/mark-paid', requireAdmin, async (req, res) => {
  const { karigar_id, amount } = req.body;
  try {
    // Mark all currently unpaid entries as paid for this karigar
    await db.query(
      "UPDATE work_entries SET status = 'paid', paid_date = NOW() WHERE karigar_id = ? AND status = 'unpaid'",
      [karigar_id]
    );
    res.json({ success: true, message: `Status updated for Karigar ID ${karigar_id}` });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

router.get('/payroll/karigar/:id/history', requireAdmin, async (req, res) => {
  try {
    const [rows] = await db.query(
      `SELECT we.*, c.name as category_name 
       FROM work_entries we 
       JOIN categories c ON we.category = c.id
       WHERE we.karigar_id = ? 
       ORDER BY we.date DESC`, 
      [req.params.id]
    );
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ============ PROFILE MANAGEMENT ============
const emailService = require('../services/emailService');
const bcrypt = require('bcryptjs');

// GET profile details
router.get('/karigar/profile-details', requireKarigar, async (req, res) => {
  try {
    const [rows] = await db.query('SELECT name, email, mobile FROM karigars WHERE id = ?', [req.session.karigar_id]);
    res.json(rows[0]);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// POST update name only (no OTP needed)
router.post('/karigar/update-name', requireKarigar, async (req, res) => {
  const { name } = req.body;
  const id = req.session.karigar_id;
  if (!name || !name.trim()) return res.status(400).json({ success: false, error: 'Name is required' });
  try {
    await db.query('UPDATE karigars SET name = ? WHERE id = ?', [name.trim(), id]);
    req.session.name = name.trim(); // keep session in sync
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

// POST send OTP to current email (for email change only)
router.post('/karigar/request-profile-otp', requireKarigar, async (req, res) => {
  const user_id = req.session.karigar_id;
  try {
    const [rows] = await db.query('SELECT email, name FROM karigars WHERE id = ?', [user_id]);
    if (rows.length === 0) return res.status(404).json({ error: 'User not found' });

    const user = rows[0];
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiry = Date.now() + 600000; // 10 min

    req.session.profile_otp = { code: otp, expiry, for_user: user_id };
    await emailService.sendResetOTP(user.email, user.name, otp);
    res.json({ success: true, message: 'OTP sent to your current email.' });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

// POST confirm email change with OTP
router.post('/karigar/update-email', requireKarigar, async (req, res) => {
  const { email, otp } = req.body;
  const id = req.session.karigar_id;

  const sessionOtp = req.session.profile_otp;
  if (!sessionOtp || sessionOtp.code !== otp || sessionOtp.expiry < Date.now() || sessionOtp.for_user !== id) {
    return res.status(400).json({ success: false, error: 'Invalid or expired OTP. Please request a new one.' });
  }
  if (!email || !email.includes('@')) {
    return res.status(400).json({ success: false, error: 'Please provide a valid email address.' });
  }

  try {
    // Check if email is already taken
    const [existing] = await db.query('SELECT id FROM karigars WHERE email = ? AND id != ?', [email, id]);
    if (existing.length > 0) return res.status(400).json({ success: false, error: 'This email is already in use by another account.' });

    await db.query('UPDATE karigars SET email = ? WHERE id = ?', [email, id]);
    delete req.session.profile_otp;
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

// POST update password only — requires current password, no logout
router.post('/karigar/update-password', requireKarigar, async (req, res) => {
  const { current_password, password } = req.body;
  const id = req.session.karigar_id;

  if (!current_password) {
    return res.status(400).json({ success: false, error: 'Please enter your current password.' });
  }
  if (!password || password.trim().length < 6) {
    return res.status(400).json({ success: false, error: 'New password must be at least 6 characters.' });
  }

  try {
    const [rows] = await db.query('SELECT password FROM karigars WHERE id = ?', [id]);
    if (rows.length === 0) return res.status(404).json({ success: false, error: 'User not found.' });

    const isMatch = await bcrypt.compare(current_password, rows[0].password);
    if (!isMatch) {
      return res.status(400).json({ success: false, error: 'Current password is incorrect.' });
    }

    const hashed = await bcrypt.hash(password.trim(), 10);
    await db.query('UPDATE karigars SET password = ? WHERE id = ?', [hashed, id]);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

module.exports = router;


