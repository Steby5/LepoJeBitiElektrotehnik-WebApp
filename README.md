# Lepo je biti elektrotehnik - Web Application

Modern web application for managing the "Lepo je biti elektrotehnik" quiz and interactive experiences. Features a premium glassmorphic UI, real-time AJAX updates, and specialized mobile/admin views.

## 🚀 Key Features

### 🎮 Quiz Management

- **Live Contestant Registration**: Real-time contestant enrollment.
- **Admin Dashboard**: Select contestants and manage game states.
- **Audience Voting (Glas ljudstva)**: Interactive voting for multiple-choice questions.
- **Real-time Stats**: Instant display of voting percentages with AJAX auto-refresh.

### 🎡 Experience Tracking (Doživetja)

- **JSON Import**: Easy setup of experiences via JSON files.
- **Capacity Management**: Automatic spot counting and availability tracking.
- **Admin Control**: Select and clear participants with ease.
- **Dedicated Displays**: Specialized views for projection or mobile info.

### 🛡️ Security & Performance

- **Anti-Duplication**: Session-based protection for voting and registration.
- **Role-based Access**: Password-protected admin panels.
- **High Concurrency**: Tested for up to 600 concurrent users.

## 🛠️ Tech Stack

- **Backend**: PHP 8.1+
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: Bootstrap 5.3, Bootstrap Icons, Inter Font
- **Design**: Custom Glassmorphism UI (premium.css)

## 📥 Installation

1. **Clone the repository** to your web server root (e.g., `htdocs` for XAMPP).
2. **Database Setup**:
   - Create a database named `elektrotehnik`.
   - Import `mysql/elektrotehnik.sql`.
3. **Configuration**:
   - Copy `server_data.sample.php` to `server_data.php`.
   - Update `server_data.php` with your database credentials and set the `ADMIN_PASSWORD`.
4. **Access**:
   - User view: `index.php`
   - Admin view: `nadzor.php` (default pass: `elektro`)

## 📂 Project Structure

- `index.php`: Main landing page for participants.
- `nadzor.php`: Quiz admin dashboard.
- `nadzor_dozivetja.php`: Experience admin dashboard.
- `css/premium.css`: Core design system.
- `api_*.php`: Backend endpoints for live updates.

## 📄 License

MIT License - Developed by UL FE 2026
