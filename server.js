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

// Make session available in all views (by passing to routes)
app.use((req, res, next) => {
  res.locals.session = req.session;
  next();
});

// Routes
const authRoutes = require('./routes/auth');
const adminRoutes = require('./routes/admin');
const karigarRoutes = require('./routes/karigar');
const apiRoutes = require('./routes/api');

app.use('/', authRoutes);
app.use('/admin', adminRoutes);
app.use('/karigar', karigarRoutes);
app.use('/api', apiRoutes);

// Test DB connection & start server
db.query('SELECT 1')
  .then(() => {
    console.log('✅ MySQL connected successfully');
    app.listen(PORT, () => {
      console.log(`🚀 KariTrack server running at http://localhost:${PORT}`);
    });
  })
  .catch(err => {
    console.error('❌ MySQL connection failed:', err.message);
    process.exit(1);
  });
