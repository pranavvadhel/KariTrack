const express = require('express');
const router = express.Router();
const db = require('../config/db');
const { requireKarigar } = require('../middleware/auth');
const path = require('path');

router.use(requireKarigar);

router.get('/dashboard', (req, res) => {
  res.sendFile(path.join(__dirname, '../views/karigar/dashboard.html'));
});

router.get('/my-records', (req, res) => {
  res.sendFile(path.join(__dirname, '../views/karigar/my_records.html'));
});

module.exports = router;
