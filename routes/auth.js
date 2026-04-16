const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const db = require('../config/db');

// GET - Karigar Login Page
router.get('/', (req, res) => {
  // Auto-login from cookie
  if (!req.session.karigar_id && req.cookies.karigar_id) {
    req.session.karigar_id = req.cookies.karigar_id;
    req.session.name = req.cookies.karigar_name;
    req.session.role = 'karigar';
    return res.redirect('/karigar/dashboard');
  }
  if (req.session.karigar_id) return res.redirect('/karigar/dashboard');
  res.sendFile(require('path').join(__dirname, '../views/login.html'));
});

// POST - Karigar Login
router.post('/login', async (req, res) => {
  const { email, password, remember } = req.body;
  const JWT_SECRET = process.env.JWT_SECRET || 'karitrack_jwt_special_2024';
  try {
    const [rows] = await db.query('SELECT id, name, password FROM karigars WHERE email = ?', [email]);
    if (rows.length === 0) return res.redirect('/?error=User+not+found');
    
    const user = rows[0];
    const match = await bcrypt.compare(password, user.password);
    if (!match) return res.redirect('/?error=Invalid+password');
    
    req.session.karigar_id = user.id;
    req.session.name = user.name;
    req.session.role = 'karigar';

    const token = jwt.sign({ id: user.id, name: user.name, role: 'karigar' }, JWT_SECRET, { expiresIn: '7d' });
    res.cookie('auth_token', token, { maxAge: 7 * 24 * 60 * 60 * 1000 });

    if (remember) {
      res.cookie('karigar_id', user.id, { maxAge: 30 * 24 * 60 * 60 * 1000 });
      res.cookie('karigar_name', user.name, { maxAge: 30 * 24 * 60 * 60 * 1000 });
    }
    res.redirect('/karigar/dashboard');
  } catch (err) {
    console.error('Karigar Login Error:', err);
    res.redirect('/?error=Server+error');
  }
});

// GET - Admin Login Page
router.get('/admin-login', (req, res) => {
  if (!req.session.admin_id && req.cookies.admin_id === 'admin') {
    req.session.admin_id = 'admin';
    req.session.admin_name = 'Admin';
    return res.redirect('/admin/dashboard');
  }
  if (req.session.admin_id) return res.redirect('/admin/dashboard');
  res.sendFile(require('path').join(__dirname, '../views/admin_login.html'));
});

// POST - Admin Login
router.post('/admin-login', (req, res) => {
  const { email, password, remember } = req.body;
  const adminId = process.env.ADMIN_ID || 'admin';
  const adminPass = process.env.ADMIN_PASS || '12345';
  const JWT_SECRET = process.env.JWT_SECRET || 'karitrack_jwt_special_2024';

  if (email === adminId && password === adminPass) {
    req.session.admin_id = 'admin';
    req.session.admin_name = 'Admin';
    req.session.role = 'admin';

    const token = jwt.sign({ id: 'admin', role: 'admin' }, JWT_SECRET, { expiresIn: '7d' });
    res.cookie('auth_token', token, { maxAge: 7 * 24 * 60 * 60 * 1000 });

    if (remember) {
      res.cookie('admin_id', 'admin', { maxAge: 30 * 24 * 60 * 60 * 1000 });
      res.cookie('admin_name', 'Admin', { maxAge: 30 * 24 * 60 * 60 * 1000 });
    }
    return res.redirect('/admin/dashboard');
  }
  res.redirect('/admin-login?error=Invalid+Admin+ID+or+Password');
});

// GET - Signup Page
router.get('/signup', (req, res) => {
  res.sendFile(require('path').join(__dirname, '../views/signup.html'));
});

// POST - Signup
router.post('/signup', async (req, res) => {
  console.log('Signup Attempt:', req.body);
  const { name, email, mobile, password, confirm_password } = req.body;
  if (!password) {
    return res.redirect('/signup?error=Password+is+required');
  }
  if (password !== confirm_password) {
    return res.redirect('/signup?error=Passwords+do+not+match');
  }
  try {
    const [existing] = await db.query('SELECT id FROM karigars WHERE email = ?', [email]);
    if (existing.length > 0) {
      return res.redirect('/signup?error=Email+already+registered');
    }
    const hashed = await bcrypt.hash(password, 10);
    await db.query(
      'INSERT INTO karigars (name, email, mobile, password, role) VALUES (?, ?, ?, ?, ?)',
      [name, email, mobile, hashed, 'karigar']
    );
    res.redirect('/?success=Signup+successful');
  } catch (err) {
    console.error('Signup Error:', err);
    res.redirect('/signup?error=Server+error');
  }
});

const crypto = require('crypto');
const emailService = require('../services/emailService');

// GET - Forgot Password Page
router.get('/forgot-password', (req, res) => {
  res.sendFile(require('path').join(__dirname, '../views/forgot_password.html'));
});

// POST - Forgot Password
router.post('/forgot-password', async (req, res) => {
  const { email } = req.body;
  try {
    const [rows] = await db.query('SELECT id, name FROM karigars WHERE email = ?', [email]);
    if (rows.length === 0) {
      return res.redirect('/forgot-password?error=User+not+found');
    }

    const user = rows[0];
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiry = new Date(Date.now() + 600000); // 10 minutes

    await db.query(
      'UPDATE karigars SET reset_token = ?, reset_token_expiry = ? WHERE id = ?',
      [otp, expiry, user.id]
    );

    await emailService.sendResetOTP(email, user.name, otp);
    res.redirect('/reset-password?email=' + encodeURIComponent(email) + '&success=OTP+sent+to+your+email');
  } catch (err) {
    console.error('Forgot Pwd Error:', err);
    res.redirect('/forgot-password?error=Server+error');
  }
});

// GET - Reset Password Page
router.get('/reset-password', async (req, res) => {
  res.sendFile(require('path').join(__dirname, '../views/reset_password.html'));
});

// POST - Reset Password (Verify OTP and update)
router.post('/reset-password', async (req, res) => {
  const { email, otp, password, confirm_password } = req.body;

  if (password !== confirm_password) {
    return res.redirect(`/reset-password?email=${email}&error=Passwords+do+not+match`);
  }

  try {
    const [rows] = await db.query(
      'SELECT id FROM karigars WHERE email = ? AND reset_token = ? AND reset_token_expiry > NOW()',
      [email, otp]
    );
    
    if (rows.length === 0) {
      return res.redirect(`/reset-password?email=${email}&error=Invalid+or+expired+OTP`);
    }

    const hashed = await bcrypt.hash(password, 10);
    await db.query(
      'UPDATE karigars SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?',
      [hashed, rows[0].id]
    );

    res.redirect('/?success=Password+reset+successful.+Please+login.');
  } catch (err) {
    console.error('Reset Pwd Error:', err);
    res.redirect(`/reset-password?email=${email}&error=Server+error`);
  }
});

// GET - Logout
router.get('/logout', (req, res) => {
  req.session.destroy();
  res.clearCookie('karigar_id');
  res.clearCookie('karigar_name');
  res.clearCookie('admin_id');
  res.clearCookie('admin_name');
  res.redirect('/');
});

module.exports = router;
