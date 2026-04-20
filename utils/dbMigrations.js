const db = require('../config/db');

/**
 * Migration utility to ensure database schema matches code expectations.
 * This is especially useful for live sites like Vercel where manual SQL 
 * updates might be missed.
 */
async function runMigrations() {
    console.log('🔍 Checking database schema migrations...');
    try {
        // 1. Check/Add 'status' column to work_entries
        const [statusCols] = await db.query("SHOW COLUMNS FROM work_entries LIKE 'status'");
        if (statusCols.length === 0) {
            console.log('➕ Adding missing "status" column to work_entries...');
            await db.query("ALTER TABLE work_entries ADD COLUMN status VARCHAR(20) DEFAULT 'unpaid'");
        }

        // 2. Check/Add 'paid_date' column to work_entries
        const [dateCols] = await db.query("SHOW COLUMNS FROM work_entries LIKE 'paid_date'");
        if (dateCols.length === 0) {
            console.log('➕ Adding missing "paid_date" column to work_entries...');
            await db.query("ALTER TABLE work_entries ADD COLUMN paid_date DATETIME DEFAULT NULL");
        }

        // 3. Check/Add 'size' column to work_entries (it seems to be missing in some versions of sql)
        const [sizeCols] = await db.query("SHOW COLUMNS FROM work_entries LIKE 'size'");
        if (sizeCols.length === 0) {
            console.log('➕ Adding missing "size" column to work_entries...');
            await db.query("ALTER TABLE work_entries ADD COLUMN size VARCHAR(255) DEFAULT NULL");
        }

        console.log('✅ Database migrations checked successfully.');
    } catch (err) {
        console.error('❌ Migration failed:', err.message);
    }
}

module.exports = { runMigrations };
