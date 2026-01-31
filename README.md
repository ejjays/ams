# AI-Powered Accreditation Management System (AMS)

![Version](https://img.shields.io/badge/version-2.5-blue.svg) ![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg) ![AI](https://img.shields.io/badge/AI-Gemini%20%2B%20Llama-orange) ![Status](https://img.shields.io/badge/status-active-success.svg)

This is a high-performance web application built to simplify tracking academic accreditation. The system includes a powerful **Hybrid AI Engine** (Gemini 2.5 Flash and Llama 3.3) for smart compliance analytics and document management, all in a secure, responsive interface.

---

## Key Features

### Hybrid AI Intelligence

- **Smart Analytics:** Uses **Llama 3.3 (via Groq)** to analyze compliance data and create summaries of institutional readiness.
- **Visual Insight:** Utilizes **Gemini 2.5 Flash** to process document metadata and visual content for automated tagging and relevance scoring.
- **Quota Optimization:** Smart routing automatically switches between models to improve API usage and performance.

### Enterprise-Grade Security

- **Advanced Authentication:** Multi-level lockout protection against brute-force attacks.
- **Auth Guard:** Strict session management with a 15-minute inactivity timeout (`auth_guard.php`).
- **Audit Trail:** Logs all critical actions (Create, Update, Archive) tracked by user IP and ID.
- **Soft Deletes:** "Smart Restore" feature allows recovery of accidentally archived programs and documents.

### Dashboard & Visualization

- **Real-Time Metrics:** Live KPI cards for Programs, Visits, Users, and Documents.
- **Interactive Charts:** Dynamic Doughnut and Bar charts (Chart.js) visualize evidence submissions versus reviews.
- **Notice Board:** Automated system notices for upcoming visits, overdue tasks, and new document uploads.

---

## Technical Architecture

### Stack

- **Backend:** PHP 8.x (Pure/Native, PDO Database Abstraction)
- **Frontend:** Vanilla JavaScript (ES6+), TailwindCSS (CDN), FontAwesome
- **Database:** MySQL / MariaDB (Relational Schema)
- **AI Services:** Google Gemini API, Groq Cloud API

### Directory Structure

```text
/ams
├── app/                  # Frontend Assets
│   ├── css/              # Modular Stylesheets (Dashboard, Auth, etc.)
│   └── js/               # ES6 Modules and UI Logic
├── DATABASE/             # SQL Schema Definitions
│   └── master_accred_v2.sql  # Primary Database Schema
├── PHP/                  # Core Application Logic (API and Views)
│   ├── services/         # Specialized Services (AIService.php)
│   ├── partials/         # Reusable UI Components
│   ├── *_api.php         # RESTful JSON Endpoints
│   └── db.php            # Centralized Database Connection
└── uploads/              # Secure Storage for Evidence Documents
```

---

## Installation & Setup

### 1. Prerequisites

- Web Server (Apache or Nginx)
- PHP 8.0 or higher (with `pdo_mysql` and `curl` extensions)
- MySQL Database Server

### 2. Database Setup

1. Create a new MySQL database (for example, `ams_db`).
2. Import the schema file:
   ```bash
   mysql -u root -p ams_db < DATABASE/master_accred_v2.sql
   ```

### 3. Environment Configuration

Create a `.env` file in the **project root** directory:

```ini
DB_HOST=localhost
DB_NAME=ams_db
DB_USER=root
DB_PASS=

# AI Configuration (Optional for basic features, Required for AI)
GEMINI_API_KEY=your_gemini_key
GROQ_API_KEY=your_groq_key
```

### 4. Permissions

Make sure the web server has write access to the uploads directory:

```bash
chmod -R 755 uploads/
```

---

## Usage & Credentials (Demo Mode)

For development and testing, the system comes pre-configured with a **Test OTP**.

### Login Flow

1. **URL:** Go to `/PHP/login.php` (or root `index.php`, which redirects).
2. **Credentials:** Use any valid user from your database (or register a new one).
3. **OTP Verification:**
   - The system simulates sending an email or SMS.
   - **Enter Code:** `041102` (Hardcoded Developer Code)
   - **Backdoor:** `000000` (Emergency Bypass)

> **WARNING:** Before going live, you **MUST** update `PHP/otp_verify.php` to integrate a real SMS/Email gateway (for example, PHPMailer or Twilio) and remove the hardcoded credentials.

---

## API Documentation

The backend works mainly as a JSON API. Frontend files access these endpoints asynchronously.

| Endpoint                                    | Method | Description                                                |
| :------------------------------------------ | :----- | :--------------------------------------------------------- |
| `PHP/dashboard_api.php?action=summary`      | `GET`  | Returns aggregated stats, chart data, and system notices.  |
| `PHP/dashboard_api.php?action=ai_analytics` | `GET`  | Activates the AI analysis engine for compliance reporting. |
| `PHP/programs_api.php`                      | `GET`  | Lists all active programs.                                 |
| `PHP/programs_api.php`                      | `POST` | Creates a new program (or restores if archived).           |
| `PHP/programs_api.php`                      | `PUT`  | Updates program details.                                   |

---

## Contributing

1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/AmazingFeature`).
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4. Push to the branch (`git push origin feature/AmazingFeature`).
5. Open a Pull Request.

---

## License

This project is proprietary software. Unauthorized copying of this file, through any medium, is strictly prohibited.  
© 2026 Bestlink College of the Philippines
