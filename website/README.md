Website Project
This website is a simple ordering system where users can browse products and place orders.

Features
Product listing
Add to cart system
Checkout system
Order confirmation email using SMTP
📧 Email (SMTP) Feature

This website sends automatic order confirmation emails using Gmail SMTP when a user completes a purchase.

**All images used on this site belong to their respective owners and are used here for demonstration purposes only. No copyright infringement is intended.

⚙️ Setup Requirement

To enable this feature, a valid Gmail account with App Password must be configured in checkout.php.

Replace the placeholder SMTP credentials in checkout.php:

$mail->Username = "your-email@gmail.com"; $mail->Password = "your-app-password";

⚠️ Note: Do not use or commit real credentials in this repository The original exposed SMTP credentials have been removed for security reasons Users must configure their own Gmail account for the feature to work
