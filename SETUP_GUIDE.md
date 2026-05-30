# SETUP AND DEPLOYMENT GUIDE

## Quick Start Guide

### Step 1: Import Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database called `electricity_complaint_system`
3. Import the SQL file from `database/complaint_system.sql`
4. If you already had an older database, run this repair command from the project folder:

```powershell
C:\xampp\php\php.exe database\update_schema.php
```

### Step 1A: Environment Configuration
Create a local `.env` file by copying `.env.example`, then update it for your XAMPP setup:

```env
DB_HOST = 127.0.0.1
DB_USER = root
DB_PASS = your_mysql_password
DB_NAME = electricity_complaint_system
```

Keep `.env` private. It is ignored by Git, so only `.env.example` should be uploaded to GitHub.

### Step 2: Test Login Credentials
After importing the database, use these credentials:

**Admin:**
- Email: admin@electricity.com
- Password: 123

**Agent (Billing):**
- Email: billing@electricity.com
- Password: 123

**Agent (Power Failure):**
- Email: power@electricity.com
- Password: 123

**Agent (Emergency):**
- Email: emergency@electricity.com
- Password: 123

**Customer:**
- Email: customer@electricity.com
- Password: 123

### Step 3: Configure Groq API (Optional)
AI works with a local fallback even without an API key. For real Groq AI responses, add your key to `.env`:

```env
GROQ_API_KEY=your_groq_api_key_here
```

Get your free Groq API key from: https://console.groq.com

### Step 4: Access the Application
- **Landing Page:** http://localhost/electricity-project/
- **Login:** http://localhost/electricity-project/login.php
- **Register:** http://localhost/electricity-project/register.php

## File Structure (Completed)

```
electricity-project/
├── index.php                          ✅ Landing page
├── login.php                          ✅ Login system
├── register.php                       ✅ Registration
├── logout.php                         ✅ Logout handler
│
├── config/
│   └── db.php                         ✅ Database connection & helper functions
│
├── database/
│   └── complaint_system.sql           ✅ Complete database schema
│
├── api/
│   ├── ai_response.php                ✅ Groq AI Integration
│   └── create_ticket.php              ✅ Ticket creation API
│
├── user/
│   ├── dashboard.php                  ✅ Customer dashboard
│   ├── create_complaint.php           ✅ Create complaint form
│   ├── chatbot.php                    ✅ AI chatbot with Groq
│   ├── my_tickets.php                 ✅ View complaints
│   └── ticket_details.php             ✅ Complaint details & chat
│
├── agent/
│   ├── dashboard.php                  ✅ Agent dashboard
│   └── assigned_tickets.php           ✅ Manage assigned tickets
│
├── admin/
│   ├── dashboard.php                  ⏳ IN PROGRESS
│   ├── all_tickets.php                ⏳ TO CREATE
│   ├── manage_agents.php              ⏳ TO CREATE
│   ├── analytics.php                  ⏳ TO CREATE
│   └── update_ticket.php              ⏳ TO CREATE
│
└── assets/
    ├── css/
    │   └── style.css                  ⏳ TO CREATE
    └── js/
        └── app.js                     ⏳ TO CREATE
```

## Key Features Implemented

✅ User Authentication (Register/Login/Logout)
✅ Role-Based Access Control (Customer/Agent/Admin)
✅ Groq AI Integration for Complaint Analysis
✅ AI Chatbot Support
✅ Complaint Management System
✅ Ticket Tracking & Status Updates
✅ Agent Assignment & Management
✅ Customer Dashboard
✅ Agent Dashboard
✅ Database Schema with Proper Relationships
✅ Input Validation & Sanitization
✅ Modern UI with Bootstrap 5
✅ Responsive Design

## Admin Dashboard Completion (In Progress)

Replace the entire admin/dashboard.php file with the comprehensive version provided in the generated files. The admin dashboard includes:
- User statistics
- Ticket statistics
- Priority alerts
- Status and priority distribution charts
- Recent tickets list
- Agent performance metrics

## Remaining Steps

### To Deploy:
1. Ensure XAMPP is running
2. Place project in `C:\xampp\htdocs\electricity-project\`
3. Import database from `database/complaint_system.sql`
4. Configure Groq API key in `api/ai_response.php`
5. Access: http://localhost/electricity-project/

### Testing Workflow:
1. Register as new customer
2. Login and create a complaint
3. Use chatbot to analyze complaints
4. Login as agent to manage tickets
3. Login as admin to view analytics

## Important Notes

- All passwords are hashed with MD5 (use bcrypt in production)
- All user inputs are validated and sanitized
- SQL injection protected with mysqli_real_escape_string
- Bootstrap 5 for responsive UI
- Groq API for advanced AI analysis
- Chart.js for analytics visualization

## Groq API Models Available

- `mixtral-8x7b-32768` (Default) - Fast, powerful
- `llama2-70b-4096` - Alternative
- `gemma-7b-it` - Lightweight

## Troubleshooting

**Database Connection Error:**
- Check if database `electricity_complaint_system` exists
- Verify MySQL is running
- Check credentials in `.env`

**Groq API Error:**
- Verify API key is correct
- Check internet connection
- Ensure API key has proper permissions

**File Not Found:**
- Verify files exist in correct directories
- Check file permissions
- Ensure PHP extensions are enabled

## Security Improvements (Production)

- [ ] Use bcrypt instead of MD5 for passwords
- [ ] Implement HTTPS
- [ ] Add CSRF protection tokens
- [ ] Implement rate limiting
- [ ] Add file upload validation
- [ ] Use environment variables for API keys
- [ ] Add logging and monitoring
- [ ] Implement session timeouts
