// test.js
import sql from 'mssql';

const config = {
  server: '192.168.56.1',
  database: 'dbwbcp', // ganti sesuai database Anda
  user: 'sa',
  password: 'anekajc1a9', // ganti dengan password asli
  options: {
    encrypt: false,
    trustServerCertificate: true
  }
};

async function testConnection() {
  try {
    await sql.connect(config);
    console.log('✅ Database connection SUCCESS!');
    
    const result = await sql.query('SELECT 1 as test');
    console.log('✅ Query test:', result.recordset);
    
    await sql.close();
  } catch (error) {
    console.log('❌ Database error:', error.message);
  }
}

testConnection();