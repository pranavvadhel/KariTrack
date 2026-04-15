require('dotenv').config();
const express = require('express');
const session = require('express-session');
const cookieParser = require('cookie-parser');
const path = require('path');
const db = require('./config/db');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));

// Session
app.use(session({
  secret: process.env.SESSION_SECRET || 'karitrack_secret',
  resave: false,
  saveUninitialized: false,
  cookie: { maxAge: 24 * 60 * 60 * 1000 } // 1 day
}));

// View engine
app.set('view engine', 'html');
app.set('views', path.join(__dirname, 'views'));

// Make session available in all views
app.use((req, res, next) => {
  res.locals.session = req.session;
  next();
});

// Routes
app.use('/', require('./routes/auth'));
app.use('/admin', require('./routes/admin'));
app.use('/karigar', require('./routes/karigar'));
app.use('/api', require('./routes/api'));

// Export for Vercel
if (process.env.NODE_ENV !== 'production') {
  db.query('SELECT 1')
    .then(() => {
      console.log('✅ MySQL connected successfully');
      app.listen(PORT, () => {
        console.log(`🚀 KariTrack server running at http://localhost:${PORT}`);
      });
    })
    .catch(err => {
      console.error('❌ MySQL connection failed:', err.message);
    });
}

module.exports = app;
