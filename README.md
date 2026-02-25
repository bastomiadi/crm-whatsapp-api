<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# WhatsApp CRM - Customer Relationship Management System

WhatsApp CRM is a comprehensive Laravel-based Customer Relationship Management system with built-in WhatsApp integration via Chatery API. This application enables businesses to manage customer relationships, automate marketing campaigns, handle support tickets, and communicate directly through WhatsApp.

## Features

### 📊 Dashboard & Analytics

- **Main Dashboard** - Overview with key metrics including total contacts, active contacts, pending orders, open tickets, active campaigns, active automations, and active chatbots
- **Analytics Dashboard** - Comprehensive analytics with:
  - Messages sent/received statistics
  - Delivery rate and response rate calculations
  - Message volume charts (last 7 days)
  - Response time analytics
  - Delivery/read status tracking
  - Top performing campaigns
  - Top agents performance tracking
- **Activity Log** - Track all recent activities including contacts, interactions, and orders

### 👥 Contact Management

- **Contact CRUD** - Create, read, update, and delete contacts
- **Contact Fields** - Name, phone, email, company, address, custom fields, notes
- **Contact Status** - Active, inactive, blocked status management
- **Tags & Segments** - Organize contacts with tags and segments
- **Import Contacts** - Import contacts from CSV/Excel files
- **Export Contacts** - Export contacts to CSV format
- **Contact Assignment** - Assign contacts to specific agents
- **Bulk Operations** - Bulk delete, bulk assign contacts
- **Contact Notes** - Add and manage notes for each contact
- **Contact Search** - Search by name, phone, or email

### 📱 WhatsApp Integration (Chatery)

- **Session Management**
  - Connect/disconnect WhatsApp sessions
  - QR code authentication
  - Session status monitoring
  - User-owned session isolation
  
- **Messaging Features**
  - Send text messages
  - Send images with captions
  - Send documents/files
  - Send audio messages (voice notes)
  - Send locations
  - Send contact cards
  - Send polls
  - Reply to specific messages
  - Typing indicators
  
- **Bulk Messaging**
  - Send bulk text messages (up to 100 recipients)
  - Send bulk images
  - Send bulk documents
  - Configurable delay between messages
  - Bulk job status tracking
  
- **Chat Management**
  - Real-time chat interface
  - Chat history search
  - Conversation management
  - Unread message tracking
  
- **Number Verification** - Check if a phone number is registered on WhatsApp

### 📢 Campaigns

- **Campaign Types**
  - Broadcast messages
  - Sequential message sequences
  - Trigger-based campaigns
  
- **Campaign Management**
  - Create/edit/delete campaigns
  - Target by segments and tags
  - Schedule campaigns
  - Start/pause campaigns
  - Campaign statistics and tracking
  
### 🤖 Automations

- **Automation Builder**
  - Trigger-based automation
  - Custom conditions
  - Multiple actions
  - Active/inactive toggle
  
- **Automation Triggers** - Configurable based on events

### 💬 Chatbots

- **Chatbot Builder**
  - Flow-based conversation design
  - Keyword triggers
  - Default responses
  - Fallback responses
  
- **Chatbot Features**
  - Human handover capability
  - Working hours configuration
  - Session tracking
  - Activate/deactivate chatbots

### 🎫 Support Tickets

- **Ticket Management**
  - Create tickets from contacts
  - Ticket numbering system
  - Priority levels (low, medium, high, urgent)
  - Categories (general, support, complaint, sales, feedback)
  - Status tracking (open, in progress, waiting customer, resolved, closed)
  
- **Ticket Features**
  - Assign tickets to agents
  - Internal notes
  - Reply via WhatsApp
  - Response time tracking
  - First response tracking

### 📦 Orders Management

- **Order CRUD** - Full order lifecycle management
- **Order Status** - Pending, confirmed, processing, shipped, delivered, cancelled, refunded
- **Product Selection** - Select products for orders
- **Order Details** - Shipping address, shipping method, notes
- **Order Export** - Export orders to CSV

### 💼 Deals Pipeline

- **Pipeline Stages** - Lead, Qualified, Proposal, Negotiation, Closed Won, Closed Lost
- **Deal Properties** - Title, value, probability, source, expected close date
- **Deal Statistics** - Total deals, total value, win rate calculation

### 📝 Tasks & Follow-ups

- **Task Management**
  - Create tasks with title and description
  - Due date tracking
  - Priority levels (low, medium, high, urgent)
  - Status tracking (pending, in progress, completed, cancelled)
  
- **Task Features**
  - Assign tasks to contacts
  - Assign tasks to agents
  - Filter by status, priority, assignee
  - Task statistics (total, pending, overdue, due today)

### 📋 Surveys

- **Survey Types**
  - NPS (Net Promoter Score) surveys
  - Satisfaction surveys
  - Feedback surveys
  
- **Survey Features**
  - Active/draft/closed status
  - Start and end dates
  - Response tracking
  - Average NPS calculation

### 📄 Message Templates

- **Template Types** - Text, image, document, location, contact
- **Template Features**
  - Variable support
  - Button support
  - Approval workflow
  - Category organization

### ⚡ Quick Replies

- **Quick Reply Management** - Save and organize frequently used responses
- **Categories** - Organize quick replies by category

### 🔖 Tags & Categories

- **Tags** - Create and manage tags for contacts
- **Categories** - Organize products into categories

### 🔍 Duplicate Detection

- **Detection Features**
  - Find duplicate phone numbers
  - Find duplicate emails
  - Find similar names
  
- **Merge Contacts** - Merge duplicate contacts into one

### 📈 Export Features

- **Export Options**
  - Export contacts to CSV
  - Export products to CSV
  - Export orders to CSV
  - Filter by status, date range

### 👤 User Management & Authentication

- **Authentication**
  - User login
  - User registration
  - Logout functionality
  - Remember me option
  
- **Profile Management**
  - Update name and email
  - Change password
  - Profile picture upload
  
- **Email Verification** - Optional email verification system

### 🔐 Roles & Permissions

- **Roles Management**
  - Create custom roles
  - Role-based access control
  - Default role assignment
  
- **Permissions Management**
  - Granular permission system
  - Module-based organization
  - Custom permission creation

### 🎯 Additional Features

- **Audit Logs** - Track all user actions and changes
- **Data Isolation** - Users can only see their own data (non-admin)
- **Session Limits** - Configurable limits on WhatsApp sessions per user
- **Real-time Updates** - Server-Sent Events (SSE) for live data
- **WebSocket Support** - Real-time messaging via WebSocket

## Technology Stack

- **Framework**: Laravel 12
- **Database**: MySQL/SQLite
- **Authentication**: Laravel Sanctum
- **Frontend**: Blade Templates + JavaScript
- **WhatsApp API**: Chatery API Integration

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Copy environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Configure database in `.env` file
6. Run migrations:
   ```bash
   php artisan migrate
   ```
7. Seed database (optional):
   ```bash
   php artisan db:seed
   ```
8. Start development server:
   ```bash
   php artisan serve
   ```

## Configuration

### Chatery API Setup

Add the following to your `.env` file:

```
CHATERY_API_URL=your_api_url
CHATERY_API_KEY=your_api_key
CHATERY_WEBHOOK=your_webhook_url
```

### Session Limits (Optional)

```
SESSION_LIMIT_ENABLED=true
SESSION_LIMIT_MAX=1
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
