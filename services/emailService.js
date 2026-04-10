const nodemailer = require('nodemailer');

const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST,
  port: process.env.SMTP_PORT,
  secure: process.env.SMTP_PORT == 465,
  auth: {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS
  }
});

exports.sendResetOTP = async (email, name, otp) => {
  const mailOptions = {
    from: process.env.SMTP_FROM,
    to: email,
    subject: 'KariTrack - Your Password Reset OTP',
    html: `
      <div style="font-family: 'Inter', sans-serif; background: #0f172a; color: #f1f5f9; padding: 40px; border-radius: 16px; text-align: center;">
        <h2 style="color: #6366f1; margin-bottom: 20px;">🧵 KariTrack Security</h2>
        <p>Hello ${name},</p>
        <p>Use the following 6-digit OTP to reset your password. This code is valid for 10 minutes.</p>
        <div style="margin: 30px 0;">
          <span style="background: #1e293b; color: #6366f1; padding: 16px 32px; border-radius: 12px; font-size: 2.4rem; font-weight: 800; letter-spacing: 8px; border: 2px solid #334155;">${otp}</span>
        </div>
        <p style="color: #94a3b8; font-size: 0.85rem;">If you didn't request this, please secure your account immediately.</p>
        <hr style="border: none; border-top: 1px solid #334155; margin: 30px 0;">
        <p style="font-size: 0.75rem; color: #64748b;">© 2024 KariTrack System</p>
      </div>
    `
  };

  return transporter.sendMail(mailOptions);
};
