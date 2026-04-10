// Middleware to protect admin routes
function requireAdmin(req, res, next) {
  if (req.session && req.session.admin_id) {
    return next();
  }
  res.redirect('/admin-login');
}

// Middleware to protect karigar routes
function requireKarigar(req, res, next) {
  if (req.session && req.session.karigar_id && req.session.role === 'karigar') {
    return next();
  }
  res.redirect('/');
}

module.exports = { requireAdmin, requireKarigar };
