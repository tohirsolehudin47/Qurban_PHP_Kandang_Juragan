# Qurban Reseller Affiliate Platform

A comprehensive PHP-based platform for managing Qurban sales through a reseller affiliate network. The platform enables resellers to promote Qurban products, track sales, and earn commissions while providing customers with options for direct purchase or Qurban savings plans.

## Features

### User Management
- Multi-role authentication (Admin & Reseller)
- Detailed reseller profiles with bank information
- Reseller approval system
- Profile image and ID card management

### Product Management
- Category-based product organization
- Multiple product images and video support
- Stock tracking
- Location-based inventory
- Dynamic pricing system

### Order System
- Order tracking with multiple statuses
- Payment proof upload
- Delivery management
- Qurban savings plan integration
- WhatsApp integration for order communication

### Affiliate System
- Unique affiliate link generation
- Click tracking with analytics
- Commission calculation based on product weight
- Commission payment management
- Marketing materials distribution

### Marketing Tools
- Downloadable marketing materials (ebooks, videos, images)
- WhatsApp message templates
- Social media sharing integration
- Performance analytics

### Financial Management
- Commission rate configuration
- Payment tracking
- Sales reporting
- Savings plan management

## Technical Stack

- PHP 7.4+
- MySQL 5.7+
- Tailwind CSS
- Font Awesome Icons
- Google Fonts

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/qurban-reseller-platform.git
```

2. Create a MySQL database and import the schema:
```bash
mysql -u root -p
CREATE DATABASE qurban_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
mysql -u root -p qurban_app < database.sql
```

3. Configure database connection in `includes/db.php`:
```php
$host = 'localhost';
$db   = 'qurban_app';
$user = 'your_username';
$pass = 'your_password';
```

4. Set up the upload directories:
```bash
mkdir -p public/uploads/{profiles,id_cards,products,payment_proofs}
chmod -R 755 public/uploads
```

5. Configure your web server to point to the project directory.

## Directory Structure

```
├── admin/                 # Admin panel files
├── auth/                  # Authentication files
├── includes/             # Common includes
├── public/               # Publicly accessible files
│   ├── uploads/         # File uploads
│   └── assets/          # Static assets
├── reseller/             # Reseller panel files
├── database.sql          # Database schema and initial data
└── README.md            # This file
```

## Default Admin Account

- Username: admin
- Email: admin@qurbanapp.com
- Password: admin123

## Key Files

- `includes/functions.php`: Common helper functions
- `includes/db.php`: Database connection
- `includes/header.php`: Common header template
- `includes/footer.php`: Common footer template

## Features by Role

### Admin
- Manage resellers
- Manage products and categories
- Process orders
- Configure commission rates
- Manage marketing materials
- View system statistics

### Reseller
- View and share product catalog
- Generate affiliate links
- Track sales and commissions
- Access marketing materials
- View performance statistics

## Security Features

- Password hashing
- Session management
- Input sanitization
- File upload validation
- Role-based access control

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please email support@qurbanapp.com or open an issue in the repository.

## Acknowledgments

- Tailwind CSS for the UI framework
- Font Awesome for icons
- Google Fonts for typography

## Roadmap

- [ ] Mobile app integration
- [ ] Payment gateway integration
- [ ] API development
- [ ] Multi-language support
- [ ] Advanced analytics
- [ ] Bulk SMS integration

## Security Considerations

- Always keep PHP and MySQL updated to the latest stable versions
- Regularly backup the database
- Monitor system logs for suspicious activity
- Implement rate limiting for login attempts
- Use HTTPS in production
- Regularly update dependencies

## Production Deployment

1. Set up a production server with PHP 7.4+ and MySQL 5.7+
2. Configure SSL certificate
3. Set up proper file permissions
4. Configure error logging
5. Set up database backups
6. Configure email sending
7. Set up monitoring

Remember to:
- Change default admin password
- Configure proper PHP settings
- Set up proper backup strategy
- Monitor system resources
- Implement proper logging
