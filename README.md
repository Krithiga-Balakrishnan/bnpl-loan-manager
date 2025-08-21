# BNPL (Buy Now Pay Later) Platform - Laravel

A simplified **Buy Now Pay Later (BNPL)** platform built with Laravel that demonstrates **loan generation**, **installment scheduling**, **real-time payment processing**, and **automated payment jobs**.

## Features

### 1. Backend API
- **Loan Management API**
  - Generate loans with configurable installments and periods
  - Update loan statuses
  - Fetch loan payments and status counts
- **Customer Management API**
  - Create, update, and list customers
- **Installment Payment API**
  - Pay installments manually
  - Prevent duplicate payments
  - Auto-complete loans when all installments are paid

### 2. Frontend Interface
- **Loan Generation Page**
  - Form to input loan parameters
  - Responsive UI (Bootstrap 5)
  - Client-side and server-side validation
- **Loan Dashboard**
  - View all loans with status, amounts, installments, and next payment due date
  - Search and filter functionality 
  - Email notification to the customer
  - Data visualization for payment trends and loan count based on status

### 3. Real-time Updates
- Powered by **Laravel WebSockets** / Pusher
- Dashboard updates instantly when:
  - Loans are generated
  - Installments are paid
  - Loan status changes
- Events: `LoanGenerated`, `InstallmentPaid`, `LoanCompleted`

### 4. Background Job Processing
- Scheduled job runs every **10 minutes**
- Processes **due installments** automatically
- Updates statuses, dispatches events, and logs results
- Handles payment failures gracefully with retry support

---

## Tech Stack

- **Backend:** Laravel 12
- **PHP Version:** 8.2
- **Frontend:** Blade templates + Bootstrap 5 
- **JavaScript & Build Tools:** Vite 5, Axios, Laravel Vite Plugin
- **Real-time:** Laravel Echo + Pusher JS
- **Queue:** Laravel Database Queue
- **Mail:** Laravel Mailable 
- **Testing:** PHPUnit 11
---

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/laravel-bnpl.git
cd laravel-bnpl
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Configure the following in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bnpl
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database
```

For **Pusher/WebSockets**, add:
```env
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-key
PUSHER_APP_SECRET=your-secret
PUSHER_APP_CLUSTER=mt1
```

### 4. Database Migration
```bash
php artisan migrate
php artisan db:seed 
```

### 5. Run Queues & Scheduler
```bash
php artisan queue:work
php artisan schedule:work
```

### 6. Start WebSocket Server (if using Laravel WebSockets)
```bash
soketi start
```

### 7. Serve the Application
```bash
php artisan serve
npm run dev
```

---

## API Endpoints

### **Customer Management**
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST   | `/api/customers` | Create a new customer |
| PATCH  | `/api/customers/{id}` | Update customer details |
| GET    | `/api/customers` | Get all customers |

### **Loan Management**
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST   | `/api/loans/generate` | Generate loans |
| PATCH  | `/api/loans/{loan}/status` | Update loan status |
| GET    | `/api/loans/payments` | Get all loan payments |
| GET    | `/api/loans/status-counts` | Loan counts by status |

### **Installment Payments**
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST   | `/api/installments/{installment}/pay` | Pay a pending installment |

---

## Author
**Krithiga D. Balakrishnan** 
[GitHub](https://github.com/Krithiga-Balakrishnan) • [Portfolio](https://krithiga-balakrishnan.vercel.app) 

