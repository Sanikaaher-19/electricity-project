<?php
// ============================================
// AI Chatbot Page with Groq Integration
// Electricity Complaint Management System
// ============================================

session_start();
include '../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot - Electricity Complaint System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/user-electricity.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        .sidebar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            padding: 12px 15px;
            color: #667eea;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 8px;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background: #f0f4ff;
            padding-left: 20px;
        }
        .chatbot-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .chat-header h2 {
            margin: 0;
            font-weight: 700;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f9f9f9;
        }
        .message {
            margin-bottom: 15px;
            display: flex;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .message.user {
            justify-content: flex-end;
        }
        .message-content {
            max-width: 70%;
            padding: 12px 15px;
            border-radius: 10px;
            white-space: pre-line;
            word-wrap: break-word;
        }
        .message.user .message-content {
            background: #667eea;
            color: white;
            border-radius: 10px 0 10px 10px;
        }
        .message.ai .message-content {
            background: #e9ecef;
            color: #333;
            border-radius: 0 10px 10px 10px;
        }
        .chat-input-area {
            padding: 20px;
            border-top: 1px solid #ddd;
        }
        .input-group {
            display: flex;
            gap: 10px;
        }
        .input-group input {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 12px;
            font-size: 14px;
        }
        .input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-group button {
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            font-weight: 600;
            cursor: pointer;
        }
        .input-group button:hover {
            background: #5568d3;
        }
        .loading {
            text-align: center;
            padding: 10px;
            color: #999;
        }
        .loading span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            margin: 0 3px;
            animation: bounce 1.4s infinite;
        }
        .loading span:nth-child(2) {
            animation-delay: 0.2s;
        }
        .loading span:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes bounce {
            0%, 80%, 100% {
                opacity: 0.3;
                transform: translateY(0);
            }
            40% {
                opacity: 1;
                transform: translateY(-10px);
            }
        }
        .main-content {
            padding: 20px;
        }
        h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .info-box {
            background: #f0f4ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .info-box h5 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .chat-action-panel {
            background: #f0f4ff;
            border: 1px solid #d7e1ec;
            border-radius: 10px;
            margin: 10px 0 16px 0;
            padding: 15px;
            text-align: center;
        }
        .chat-action-panel button,
        .chat-action-panel a {
            margin: 4px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="gov-top-strip">
        <div class="container-fluid">
            <span>Government Electricity Consumer Service Portal</span>
            <span>Helpline: 1912 | Emergency Supply Support</span>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Consumer Grievance Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="complaint_management.php">Complaint Management</a>
                    </li>
                    <li class="nav-item">
                        <span class="navbar-text text-white me-3">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid main-content">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar">
                    <h5 style="margin-bottom: 20px; color: #333;">Menu</h5>
                    <a href="dashboard.php">Home</a>
                    <a href="complaint_management.php">Complaint Management</a>
                    <a href="create_complaint.php">Register Complaint</a>
                    <a href="my_tickets.php">My Complaints</a>
                    <a href="chatbot.php" class="active">AI Chatbot</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <h1>AI Assistant Chatbot</h1>

                <div class="info-box">
                    <h5>AI Complaint Assistant</h5>
                    <p>Describe your electricity issue. I will first try to help with safe troubleshooting steps. If the issue is not solved, you can create a complaint directly from this chat.</p>
                </div>

                <!-- Chatbot Container -->
                <div class="chatbot-container">
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <h2>AI Assistant</h2>
                    </div>

                    <!-- Chat Messages -->
                    <div class="chat-messages" id="chatMessages">
                        <div class="message ai">
                            <div class="message-content">
                                👋 Hello! I'm your AI Assistant. I'm here to help you describe your electricity issue. 
                                <br><br>
                                Tell me what problem you are facing. I will try to help first, and if you are not satisfied, I can create a complaint ticket for you directly.
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="chat-input-area">
                        <form id="chatForm" style="display: flex; gap: 10px;">
                            <input 
                                type="text" 
                                id="chatInput" 
                                placeholder="Describe your electricity issue..." 
                                autocomplete="off"
                                style="flex: 1; border: 1px solid #ddd; border-radius: 5px; padding: 12px; font-size: 14px;"
                            >
                            <button 
                                type="submit" 
                                style="background: #667eea; color: white; border: none; border-radius: 5px; padding: 12px 20px; font-weight: 600; cursor: pointer;"
                            >
                                Send
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatMessages = document.getElementById('chatMessages');

        // Handle form submission
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userMessage = chatInput.value.trim();
            if (!userMessage) return;

            // Add user message to chat
            addMessage(userMessage, 'user');
            chatInput.value = '';

            // Show loading indicator
            showLoading();

            // Send to Groq API
            sendToGroqAPI(userMessage);
        });

        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = text;
            
            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showLoading() {
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'message ai';
            loadingDiv.innerHTML = '<div class="loading"><span></span><span></span><span></span></div>';
            loadingDiv.id = 'loadingMessage';
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeLoading() {
            const loadingMsg = document.getElementById('loadingMessage');
            if (loadingMsg) {
                loadingMsg.remove();
            }
        }

        function sendToGroqAPI(message) {
            // Call Groq API endpoint
            fetch('../api/ai_response.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    complaint: message,
                    ticket_id: null // Not creating ticket yet
                })
            })
            .then(response => response.json())
            .then(data => {
                removeLoading();
                
                if (data.success) {
                    // Extract AI response
                    const aiData = data.data;
                    
                    let aiMessage = `Analysis Complete!\n\n`;
                    aiMessage += `📁 Category: ${aiData.category}\n`;
                    aiMessage += `⚠️  Priority: ${aiData.priority}\n\n`;
                    aiMessage += `Response:\n${aiData.initial_response}`;
                    
                    addMessage(aiMessage, 'ai');
                    
                    // Show action button
                    showCreateComplaintOption();
                } else {
                    addMessage(`Sorry, I couldn't analyze your complaint. Error: ${data.error}`, 'ai');
                }
            })
            .catch(error => {
                removeLoading();
                console.error('Error:', error);
                addMessage('Sorry, there was an error connecting to the AI service. Please try again.', 'ai');
            });
        }

        function showCreateComplaintOption() {
            const optionDiv = document.createElement('div');
            optionDiv.style.cssText = `
                background: #f0f4ff;
                padding: 15px;
                border-radius: 10px;
                margin-top: 10px;
                text-align: center;
            `;
            optionDiv.innerHTML = `
                <p style="margin-bottom: 10px;">Would you like to create a complaint ticket based on this analysis?</p>
                <a href="create_complaint.php" class="btn btn-primary" style="background: #667eea; color: white; padding: 8px 20px; text-decoration: none; border-radius: 5px; font-weight: 600;">
                    Create Ticket
                </a>
            `;
            chatMessages.appendChild(optionDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Allow Enter key to send message
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    </script>
    <script>
        let latestIssueForTicket = '';
        let latestAnalysisForTicket = null;

        // Override the earlier analyzer so the bot tries to help before complaint creation.
        function sendToGroqAPI(message) {
            latestIssueForTicket = message;

            fetch('../api/ai_response.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    complaint: message,
                    ticket_id: null
                })
            })
            .then(response => response.json())
            .then(data => {
                removeLoading();

                if (data.success) {
                    latestAnalysisForTicket = data.data;

                    let aiMessage = `I checked your issue.\n\n`;
                    aiMessage += `Category: ${data.data.category}\n`;
                    aiMessage += `Priority: ${data.data.priority}\n\n`;
                    aiMessage += `Try this first:\n${getTroubleshootingSteps(data.data.category, message)}\n\n`;
                    aiMessage += `If this does not solve your issue, I can create a complaint ticket for you.`;

                    addMessage(aiMessage, 'ai');
                    showSatisfactionOptions();
                } else {
                    addMessage(`Sorry, I could not analyze your issue. Error: ${data.error}`, 'ai');
                }
            })
            .catch(error => {
                removeLoading();
                console.error('AI analysis error:', error);
                addMessage('Sorry, there was an error connecting to the support service. Please try again.', 'ai');
            });
        }

        function getTroubleshootingSteps(category, message) {
            const text = message.toLowerCase();

            if (category === 'Electrical Hazard') {
                return [
                    '1. Do not touch exposed wires, sparking meters, or damaged electrical points.',
                    '2. Switch off the main supply only if it is safe.',
                    '3. Keep people away from the affected area.',
                    '4. Call emergency electricity support/helpline immediately.'
                ].join('\n');
            }

            if (category === 'Power Failure') {
                return [
                    '1. Check whether nearby homes also have no power.',
                    '2. Check your main switch/MCB and reset it once if it has tripped.',
                    '3. Turn off heavy appliances before restoring supply.',
                    '4. If the outage continues, create a complaint.'
                ].join('\n');
            }

            if (category === 'Billing') {
                return [
                    '1. Check bill month, meter reading, and due date.',
                    '2. Compare current units with your previous bill.',
                    '3. Keep consumer number and payment reference ready.',
                    '4. If the amount looks incorrect, create a billing complaint.'
                ].join('\n');
            }

            if (category === 'Meter Issue' || text.includes('meter')) {
                return [
                    '1. Note the current meter reading and meter number.',
                    '2. Check if display is blank, fast, slow, or showing an error.',
                    '3. Do not open or tamper with the meter box.',
                    '4. If the issue remains, create a meter inspection complaint.'
                ].join('\n');
            }

            if (category === 'Connection Issue') {
                return [
                    '1. Keep consumer number and registered mobile number ready.',
                    '2. Check if this is new connection, disconnection, or reconnection.',
                    '3. Verify pending documents or payments.',
                    '4. If support is still needed, create a connection complaint.'
                ].join('\n');
            }

            return [
                '1. Note when the issue started and how often it happens.',
                '2. Keep consumer number, address, and contact number ready.',
                '3. Avoid unsafe electrical handling.',
                '4. If the issue is not solved, create a complaint ticket.'
            ].join('\n');
        }

        function showSatisfactionOptions() {
            const chatMessagesBox = document.getElementById('chatMessages');
            const optionDiv = document.createElement('div');
            optionDiv.className = 'chat-action-panel';
            optionDiv.innerHTML = `
                <p style="margin-bottom: 10px;">Was this helpful?</p>
                <button type="button" class="btn btn-success btn-sm" data-chat-action="solved">Yes, issue solved</button>
                <button type="button" class="btn btn-primary btn-sm" data-chat-action="create-ticket">No, create complaint</button>
            `;

            chatMessagesBox.appendChild(optionDiv);

            optionDiv.querySelector('[data-chat-action="solved"]').addEventListener('click', function() {
                optionDiv.remove();
                addMessage('Great. I am glad the guidance helped. You can come back anytime if the issue happens again.', 'ai');
            });

            optionDiv.querySelector('[data-chat-action="create-ticket"]').addEventListener('click', function() {
                optionDiv.remove();
                createComplaintFromChat();
            });

            chatMessagesBox.scrollTop = chatMessagesBox.scrollHeight;
        }

        function createComplaintFromChat() {
            if (!latestIssueForTicket) {
                addMessage('Please describe your issue first, then I can create a complaint.', 'ai');
                return;
            }

            showLoading();

            const title = latestAnalysisForTicket
                ? `${latestAnalysisForTicket.category} Complaint`
                : 'Electricity Complaint';

            fetch('../api/create_ticket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    title: title,
                    description: latestIssueForTicket
                })
            })
            .then(response => response.json())
            .then(data => {
                removeLoading();

                if (data.success) {
                    addMessage(`Complaint created successfully. Your ticket number is #${data.ticket_id}.`, 'ai');
                    showTicketCreatedOptions(data.ticket_id);
                } else {
                    addMessage(`Could not create complaint: ${data.error}`, 'ai');
                }
            })
            .catch(error => {
                removeLoading();
                console.error('Create ticket error:', error);
                addMessage('Sorry, I could not create the complaint right now. Please try again.', 'ai');
            });
        }

        function showTicketCreatedOptions(ticketId) {
            const chatMessagesBox = document.getElementById('chatMessages');
            const optionDiv = document.createElement('div');
            optionDiv.className = 'chat-action-panel';
            optionDiv.innerHTML = `
                <p style="margin-bottom: 10px;">Your complaint is now registered.</p>
                <a href="ticket_details.php?id=${ticketId}" class="btn btn-primary btn-sm">View Ticket</a>
                <a href="my_tickets.php" class="btn btn-secondary btn-sm">My Complaints</a>
            `;
            chatMessagesBox.appendChild(optionDiv);
            chatMessagesBox.scrollTop = chatMessagesBox.scrollHeight;
        }
    </script>
    <script>
        // Smart support flow: intercepts the older analyzer and uses full chat context.
        const smartState = {
            history: [],
            latestAnalysis: null,
            latestIssue: '',
            readyForTicket: false
        };

        const firstBotMessage = document.querySelector('#chatMessages .message.ai .message-content');
        if (firstBotMessage) {
            firstBotMessage.textContent = 'Hello! I am your AI support assistant.\n\nTell me what problem you are facing. I will ask a few questions if needed, try safe troubleshooting first, and create a complaint ticket only if the issue is not solved.';
        }

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const userMessage = chatInput.value.trim();
            if (!userMessage) return;

            addMessage(userMessage, 'user');
            chatInput.value = '';
            handleSmartSupport(userMessage);
        }, true);

        function handleSmartSupport(message) {
            smartState.history.push(message);
            smartState.latestIssue = smartState.history.join('\n');

            const intent = detectSmartIntent(message);
            if (intent === 'solved') {
                smartState.readyForTicket = false;
                addMessage('Great, I am glad it is solved. If the issue happens again, note the time, location, and meter details so we can register a stronger complaint.', 'ai');
                return;
            }

            if (intent === 'create_ticket' && smartState.latestAnalysis) {
                addMessage('Understood. Since the issue is still not solved, I will create a complaint ticket using this chat conversation.', 'ai');
                createSmartComplaintFromChat();
                return;
            }

            showLoading();

            fetch('../api/ai_response.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    complaint: smartState.latestIssue,
                    ticket_id: null
                })
            })
            .then(response => response.json())
            .then(data => {
                removeLoading();

                if (!data.success) {
                    addMessage(`Sorry, I could not understand the issue properly. Error: ${data.error}`, 'ai');
                    return;
                }

                smartState.latestAnalysis = data.data;
                const missing = getSmartMissingQuestions(data.data.category, smartState.latestIssue);

                if (missing.length > 0 && smartState.history.length < 3) {
                    addMessage(buildSmartFollowUp(data.data.category, missing), 'ai');
                    showSmartSatisfactionOptions();
                    return;
                }

                smartState.readyForTicket = true;
                addMessage(buildSmartSupportReply(data.data.category, data.data.priority, smartState.latestIssue), 'ai');
                showSmartSatisfactionOptions();
            })
            .catch(error => {
                removeLoading();
                console.error('Smart support error:', error);
                addMessage('Sorry, there was an error connecting to the support service. Please try again.', 'ai');
            });
        }

        function detectSmartIntent(message) {
            const text = message.toLowerCase();

            if (/(not solved|still not|still|same issue|not working|not fixed|create ticket|create complaint|raise complaint|complaint|ticket|no it did not|no,? create)/.test(text)) {
                return 'create_ticket';
            }

            if (/(^|\b)(solved|fixed|resolved)(\b|$)|working now|thank you|thanks|ok now|okay now/.test(text)) {
                return 'solved';
            }

            return 'details';
        }

        function getSmartMissingQuestions(category, issueText) {
            const text = issueText.toLowerCase();
            const questions = [];

            if (!/(since|today|yesterday|hour|minute|morning|night|day|date|time)/.test(text)) {
                questions.push('When did this issue start?');
            }

            if (category === 'Power Failure' && !/(neighbor|nearby|area|only my|whole building|house only|flat only)/.test(text)) {
                questions.push('Is the power cut only at your home, or are nearby homes also affected?');
            }

            if (category === 'Billing' && !/(amount|rupee|rs|bill|unit|reading|meter|payment)/.test(text)) {
                questions.push('What is wrong in the bill: high amount, wrong meter reading, payment issue, or duplicate bill?');
            }

            if ((category === 'Electrical Hazard' || /spark|shock|fire|burn|wire|buzz/.test(text)) && !/(main switch|off|away|danger|touch|safe)/.test(text)) {
                questions.push('Is there sparking, shock, burning smell, exposed wire, or meter buzzing?');
            }

            if (!/(address|sector|street|flat|house|phone|mobile|consumer|meter number)/.test(text)) {
                questions.push('Please share your address or consumer number if you want me to create a ticket later.');
            }

            return questions.slice(0, 3);
        }

        function buildSmartFollowUp(category, questions) {
            return [
                `I understand this looks like a ${category} issue.`,
                'Before I suggest the right action, please answer these:',
                '',
                ...questions.map((question, index) => `${index + 1}. ${question}`)
            ].join('\n');
        }

        function buildSmartSupportReply(category, priority, issueText) {
            return [
                'Thanks, I understand your issue better now.',
                '',
                `Category: ${category}`,
                `Priority: ${priority}`,
                '',
                'Please try these safe steps first:',
                getTroubleshootingSteps(category, issueText),
                '',
                'After trying this, choose one option below. You can also type "solved" or "not solved".'
            ].join('\n');
        }

        function showSmartSatisfactionOptions() {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'chat-action-panel';
            optionDiv.innerHTML = `
                <p style="margin-bottom: 10px;">Did this resolve your issue?</p>
                <button type="button" class="btn btn-primary btn-sm" data-smart-action="ticket">Create complaint ticket</button>
                <button type="button" class="btn btn-success btn-sm" data-smart-action="solved">Issue solved</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-smart-action="more">Ask another question</button>
            `;

            chatMessages.appendChild(optionDiv);

            optionDiv.querySelector('[data-smart-action="solved"]').addEventListener('click', function() {
                optionDiv.remove();
                smartState.readyForTicket = false;
                addMessage('Good to hear. I will not create a ticket. Please return here if the issue starts again.', 'ai');
            });

            optionDiv.querySelector('[data-smart-action="ticket"]').addEventListener('click', function() {
                optionDiv.remove();
                createSmartComplaintFromChat();
            });

            optionDiv.querySelector('[data-smart-action="more"]').addEventListener('click', function() {
                optionDiv.remove();
                addMessage('Sure. Tell me what happened after trying the steps, or share any extra details you noticed.', 'ai');
            });

            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function createSmartComplaintFromChat() {
            if (!smartState.latestIssue) {
                addMessage('Please describe your issue first, then I can create a complaint.', 'ai');
                return;
            }

            showLoading();

            const category = smartState.latestAnalysis ? smartState.latestAnalysis.category : 'Electricity';
            const title = `${category} Complaint`;

            fetch('../api/create_ticket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    title: title,
                    description: smartState.latestIssue
                })
            })
            .then(response => response.json())
            .then(data => {
                removeLoading();

                if (data.success) {
                    smartState.readyForTicket = false;
                    const analysis = data.analysis || smartState.latestAnalysis || {};
                    addMessage([
                        `Complaint created successfully. Your ticket number is #${data.ticket_id}.`,
                        '',
                        analysis.category ? `Category: ${analysis.category}` : '',
                        analysis.priority ? `Priority: ${analysis.priority}` : '',
                        'You can track this complaint from My Complaints.'
                    ].filter(Boolean).join('\n'), 'ai');
                    showTicketCreatedOptions(data.ticket_id);
                } else {
                    addMessage(`Could not create complaint: ${data.error}`, 'ai');
                }
            })
            .catch(error => {
                removeLoading();
                console.error('Create ticket error:', error);
                addMessage('Sorry, I could not create the complaint right now. Please try again.', 'ai');
            });
        }
    </script>
</body>
</html>
