# 🧵 KariTrack - Karigar Work Tracking System

KariTrack is a modern, full-stack web application designed for the shirt manufacturing industry to track karigar (worker) productivity, manage work entries, and generate performance reports. It features a premium, mobile-responsive dark-themed interface built for efficiency and speed.

## ✨ Key Features

- **🛡️ Multi-Role Security**: Dedicated dashboards for Admin and Karigar roles with session-based authentication.
- **📈 Advanced Analytics**: Real-time stats, data filtering, and performance charts (via Chart.js).
- **📋 Smart Work Entry**: Dynamic price calculation based on shirt categories and quantities.
- **🔐 Secure Security Suite**: 6-digit OTP-based password recovery via SMTP (Gmail integration).
- **👁️ User Experience**: Eye toggles for password fields, interactive sidebar with mobile blurs, and premium animations.
- **📱 Fully Responsive**: Optimized for Desktop, Tablet, and Mobile devices.

## 🛠️ Technology Stack

- **Backend**: Node.js & Express.js
- **Database**: MySQL (optimized with connection pooling)
- **Frontend**: Vanilla HTML5, CSS3, & JavaScript (ES6+)
- **Authentication**: Bcrypt.js (Password Hashing) & Express-Session
- **Mailing**: Nodemailer (SMTP)

## 🚀 Getting Started

### 1. Prerequisites
- Node.js (v16+)
- MySQL Server (via XAMPP or standalone)

### 2. Database Setup
1. Open your MySQL client (e.g., phpMyAdmin).
2. Create a database named `shirt_business`.
3. Import the `shirt_business.sql` file provided in the repository.

### 3. Installation
```bash
# Install dependencies
npm install
```

### 4. Configuration
Create a `.env` file in the root directory and add:
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=shirt_business
SESSION_SECRET=your_secret_key
SMTP_HOST=smtp.gmail.com
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM='KariTrack <your-email@gmail.com>'
```

### 5. Run the Application
```bash
# Start server
node server.js
```
The app will be live at `http://localhost:3000`.

## 📦 Project Structure
- `config/`: Database configuration
- `middleware/`: Authentication and route protection
- `public/`: Assets (CSS, JS, Images)
- `routes/`: Modular API and View routing
- `services/`: Email (SMTP) logic
- `views/`: HTML Templates

## 📄 License
This project is for tracking and educational purposes. Feel free to modify and expand!
