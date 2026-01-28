# 🎓 Accreditation Management System (AMS) with AI Insights

A professional, high-performance web application designed to streamline academic accreditation tracking. Now supercharged with **Gemini 2.5 Flash AI** for intelligent document classification and progress analytics.

## 🚀 Key Features

### 🤖 AI-Driven Intelligence
- **Automated Document Tagging:** Gemini AI automatically analyzes uploaded file metadata and links documents to the most relevant accreditation indicators, reducing manual data entry.
- **Progress Analytics:** Real-time compliance monitoring that uses AI to summarize program readiness, identifying gaps in evidence and providing actionable insights on the dashboard.

### 📋 Core Management
- **Role-Based Access Control (RBAC):** Secure access for Admins, Deans, Program Coordinators, Faculty, and External Accreditors.
- **Dynamic Accreditation Tracking:** Manage instruments, levels, areas, and parameters with a clean, hierarchical tree structure.
- **Evidence Repository:** Centralized document management system with secure storage, sharing capabilities, and a 5-year auto-archive policy.
- **Visit Scheduling:** Integrated calendar for tracking accreditation visits and deadlines.

### 🔒 Security & Performance
- **Multi-Level Lockout:** Advanced security logic to prevent brute-force attacks with progressive lockout durations.
- **Two-Factor Authentication (OTP):** Secure login flow requiring a One-Time Password verification.
- **Automated Session Handling:** Intelligent session management with a 15-minute inactivity timeout for data protection.
- **Optimized Performance:** Uses a lightweight PHP (PDO) backend with Vanilla JS for a responsive, "app-like" experience.

---

## 🛠 Tech Stack
- **Backend:** PHP 8.x (PDO MySQL)
- **Frontend:** Vanilla JavaScript, Tailwind CSS, Chart.js
- **AI Engine:** Google Gemini 2.5 Flash
- **Database:** MySQL / MariaDB

---

## 📦 Project Structure
```text
├── app/            # Frontend Assets (CSS, JS)
├── PHP/            # Core Logic & API Endpoints
│   ├── Gemini.php  # AI Service Integration
│   ├── db.php      # Database Connection
│   └── *_api.php   # REST-like API Providers
├── DATABASE/       # SQL Schema & Dumps
├── MIGRATIONS/     # Database Versioning
└── uploads/        # Secure Document Storage
```

---

## ⚙️ Installation & Setup

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/ejjays/ams.git
   ```

2. **Database Configuration:**
   - Import `DATABASE/accred.sql` into your local MySQL instance.
   - Configure your credentials in `PHP/db.php` or a `.env` file.

3. **AI Configuration:**
   - Add your Gemini API key to `PHP/.env`:
     ```text
     GEMINI_API_KEY=your_actual_api_key_here
     ```

4. **Web Server:**
   - Deploy to a PHP-enabled server (Apache/Nginx/KSWEB).
   - Ensure the `uploads/` directory is writable by the server.

5. **Access the App:**
   - Navigate to the root directory in your browser.
   - Default redirect: `PHP/sms.php` -> `PHP/login.php`.

---

## 📜 Development Guidelines
This project follows strict modularity and clean code standards. Refer to `GEMINI.md` for coding conventions and documentation requirements.