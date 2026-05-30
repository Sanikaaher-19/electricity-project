# PowerDesk - AI Electricity Complaint Management System

PowerDesk is a PHP and MySQL web application for managing electricity complaints with role-based dashboards for customers, agents, and administrators. It includes an AI-assisted complaint chatbot that helps users troubleshoot first, then creates a complaint ticket when the issue is not solved.

## Demo Video

Watch the project demo:

<video src="assets/demo/powerdesk-demo.mp4" controls width="100%"></video>

[Watch / Download Demo Video](assets/demo/powerdesk-demo.mp4)

## Features

- Customer registration and login
- Role-based access for Customer, Agent, and Admin
- AI chatbot for electricity support
- Complaint ticket creation and tracking
- AI category and priority detection
- Automatic agent routing by complaint type
- Agent dashboard with assigned tickets and KPI cards
- Agent status updates and internal notes
- Admin dashboard for all tickets, agents, and analytics
- MySQL database schema included
- Environment variable support for API keys and database credentials

## Tech Stack

- PHP
- MySQL
- XAMPP
- Bootstrap 5
- JavaScript
- Chart.js
- Groq API with local fallback support

## Project Structure

```text
electricity-project/
|-- admin/
|-- agent/
|-- api/
|-- assets/
|   |-- css/
|   `-- js/
|-- config/
|   |-- db.php
|   `-- env.php
|-- database/
|   |-- complaint_system.sql
|   `-- update_schema.php
|-- user/
|-- .env.example
|-- .gitignore
|-- index.php
|-- login.php
|-- register.php
|-- logout.php
|-- README.md
`-- SETUP_GUIDE.md
```

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/electricity-project.git
```

Move the project folder into your XAMPP `htdocs` directory if it is not already there:

```text
C:\xampp\htdocs\electricity-project
```

### 2. Configure Environment Variables

Create a local `.env` file from `.env.example` and update your local values:

```env
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=your_mysql_password
DB_NAME=electricity_complaint_system

GROQ_API_KEY=your_groq_api_key_here
GROQ_API_URL=https://api.groq.com/openai/v1/chat/completions
GROQ_MODEL=llama-3.1-8b-instant
```

The `.env` file is ignored by Git and should not be uploaded to GitHub.

### 3. Import Database

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

3. Import:

```text
database/complaint_system.sql
```

If you already have an older version of the database, run:

```powershell
C:\xampp\php\php.exe database\update_schema.php
```

### 4. Run the Project

Open:

```text
http://localhost/electricity-project/
```

## Test Login Credentials

### Admin

```text
Email: admin@electricity.com
Password: 123
```

### Agents

```text
Billing Agent:
Email: billing@electricity.com
Password: 123

Power Failure Agent:
Email: power@electricity.com
Password: 123

Emergency Agent:
Email: emergency@electricity.com
Password: 123
```

### Customer

```text
Email: customer@electricity.com
Password: 123
```

## Suggested Demo Flow

1. Open the landing page.
2. Login as customer.
3. Create a normal billing complaint.
4. Open the AI chatbot.
5. Discuss an urgent power/sparking issue.
6. Let the chatbot create a complaint ticket.
7. Login as the assigned agent and update ticket status.
8. Login as admin and show all tickets, agents, and analytics.

## Example Chatbot Demo Queries

```text
There is no electricity in my house since last night, but my neighbors have power. My meter box is making a buzzing sound.
```

```text
It is still not solved. I can see small sparks near the main switch. My address is 24 Green Park, Sector 7, and my phone number is 9876543210. Please create a complaint ticket urgently.
```

## Screenshots

Add screenshots in this folder:

```text
assets/screenshots/
```

Example README image links:

```md
![Landing Page](assets/screenshots/landing-page.png)
![Customer Dashboard](assets/screenshots/customer-dashboard.png)
![AI Chatbot](assets/screenshots/chatbot.png)
![Agent Dashboard](assets/screenshots/agent-dashboard.png)
![Admin Analytics](assets/screenshots/admin-analytics.png)
```

## Security Notes

- Do not commit `.env`.
- Do not commit real API keys.
- Do not commit real database passwords.
- Use `.env.example` to show required configuration keys.
- If a real API key was ever pushed to GitHub, rotate it immediately.

## Deployment Notes

GitHub Pages cannot run this project because it uses PHP and MySQL.

Use PHP hosting or a server that supports:

- PHP
- MySQL
- Apache/Nginx
- Environment variables

For college/demo submission, GitHub repository plus local XAMPP setup and demo video is usually enough.

## Author

PowerDesk Electricity Complaint Management System
