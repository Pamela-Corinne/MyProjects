# Website Project

A comprehensive ordering system with separate admin and user dashboards, product management, and automated order confirmation emails.

## Features

### Admin Dashboard
- **Dashboard** — Overview and analytics
- **Product Management** — Add, remove, and edit products
- **Product Analysis** — View product performance and insights
- **Order List** — Track and manage all customer orders

### User Dashboard
- **Dashboard** — Personal overview
- **Product Catalog** — Browse and explore available products
- **Shopping Cart** — Add items and manage selections
- **Checkout** — Complete purchases with order confirmation

### 📧 Email (SMTP) Feature

This website sends automatic order confirmation emails using Gmail SMTP when a user completes a purchase.

### Image Credits
All images used on this site belong to their respective owners and are used here for demonstration purposes only. No copyright infringement is intended.

## ⚙️ Setup Requirements

To enable the email feature, a valid Gmail account with App Password must be configured in `checkout.php`.

Replace the placeholder SMTP credentials:

```php
$mail->Username = "your-email@gmail.com";
$mail->Password = "your-app-password";
