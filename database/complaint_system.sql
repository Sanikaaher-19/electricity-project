-- ============================================
-- Electricity Complaint Management System
-- Database Schema
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS electricity_complaint_system;
USE electricity_complaint_system;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('customer','agent','admin') DEFAULT 'customer',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- ============================================
-- Tickets Table
-- ============================================
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    category VARCHAR(100),
    priority ENUM('Low','Medium','High','Critical') DEFAULT 'Medium',
    status ENUM('Pending','Assigned','In Progress','On Hold','Resolved','Closed') DEFAULT 'Pending',
    assigned_agent INT,
    ai_response LONGTEXT,
    resolution_notes LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,

    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(assigned_agent) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_assigned_agent (assigned_agent)
);

-- ============================================
-- Chat Messages Table
-- ============================================
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    sender ENUM('customer','agent','admin','ai') DEFAULT 'customer',
    message LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- Agent Notes Table
-- ============================================
CREATE TABLE IF NOT EXISTS agent_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    agent_id INT NOT NULL,
    note LONGTEXT NOT NULL,
    is_private BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY(agent_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_agent_id (agent_id)
);

-- ============================================
-- AI Response Cache Table
-- ============================================
CREATE TABLE IF NOT EXISTS ai_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    complaint_text LONGTEXT,
    ai_category VARCHAR(100),
    ai_priority VARCHAR(50),
    ai_suggested_response LONGTEXT,
    ai_model VARCHAR(50) DEFAULT 'groq',
    confidence_score FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id)
);

-- ============================================
-- Sample Data (For Testing)
-- ============================================

-- Admin User
INSERT IGNORE INTO users (name, email, password, phone, role) 
VALUES ('Admin User', 'admin@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543210', 'admin');

-- Agent Users
INSERT IGNORE INTO users (name, email, password, phone, role) 
VALUES ('Billing Agent', 'billing@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543211', 'agent');

INSERT IGNORE INTO users (name, email, password, phone, role) 
VALUES ('Power Failure Agent', 'power@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543212', 'agent');

INSERT IGNORE INTO users (name, email, password, phone, role) 
VALUES ('Emergency Agent', 'emergency@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543213', 'agent');

-- Customer User
INSERT IGNORE INTO users (name, email, password, phone, role) 
VALUES ('Test Customer', 'customer@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543214', 'customer');

-- ============================================
-- Note: Password '123' is hashed as '202cb962ac59075b964b07152d234b70' (MD5)
-- For production, use password_hash() and password_verify() with bcrypt
-- ============================================
