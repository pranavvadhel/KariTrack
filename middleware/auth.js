const jwt = require('jsonwebtoken');
const JWT_SECRET = process.env.JWT_SECRET || 'karitrack_jwt_special_2024';

// Middleware to protect admin routes
async function requireAdmin(req, res, next) {
  // Check session first
  if (req.session && req.session.admin_id) {
    return next();
  }

  // Check JWT if session fails
  const authHeader = req.headers.authorization;
  if (authHeader && authHeader.startsWith('Bearer ')) {
    const token = authHeader.split(' ')[1];
    try {
      const decoded = jwt.verify(token, JWT_SECRET);
      if (decoded.role === 'admin') {
        req.user = decoded;
        return next();
      }
    } catch (err) {
      console.error('JWT Admin Error:', err.message);
    }
  }

  res.redirect('/admin-login');
}

// Middleware to protect karigar routes
async function requireKarigar(req, res, next) {
  if (req.session && req.session.karigar_id) {
    return next();
  }

  const authHeader = req.headers.authorization;
  if (authHeader && authHeader.startsWith('Bearer ')) {
    const token = authHeader.split(' ')[1];
    try {
      const decoded = jwt.verify(token, JWT_SECRET);
      if (decoded.role === 'karigar') {
        req.user = decoded;
        req.session.karigar_id = decoded.id; // Sync session for compatibility
        return next();
      }
    } catch (err) {
      console.error('JWT Karigar Error:', err.message);
    }
  }

  res.redirect('/');
}

module.exports = { requireAdmin, requireKarigar };
