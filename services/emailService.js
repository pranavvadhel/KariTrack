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

exports.sendWorkLogNotification = async (email, name, data) => {
  const mailOptions = {
    from: process.env.SMTP_FROM,
    to: email,
    subject: 'KariTrack - New Work Entry Added',
    html: `
      <div style="font-family: 'Inter', sans-serif; background: #0f172a; color: #f1f5f9; padding: 40px; border-radius: 16px;">
        <h2 style="color: #6366f1; margin-bottom: 20px;">📝 New Work Entry Added</h2>
        <p>Hello ${name},</p>
        <p>A new work record has been added to your profile.</p>
        <div style="background: #1e293b; color: #f1f5f9; padding: 20px; border-radius: 12px; margin: 20px 0;">
          <p><strong>Category:</strong> ${data.category_name}</p>
          <p><strong>Quantity:</strong> ${data.quantity}</p>
          <p><strong>Earnings:</strong> ₹${parseFloat(data.total).toFixed(2)}</p>
          <p><strong>Date:</strong> ${data.date}</p>
        </div>
        <p>Log in to your dashboard to view all records.</p>
        <hr style="border: none; border-top: 1px solid #334155; margin: 30px 0;">
        <p style="font-size: 0.75rem; color: #64748b;">© 2024 KariTrack System</p>
      </div>
    `
  };
  return transporter.sendMail(mailOptions);
};
