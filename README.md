# MyOnlineShop E-commerce Platform

A comprehensive e-commerce website built with PHP and MySQL, featuring a responsive design and modern user interface using Bootstrap 5.

## Features
<img width="1920" height="1080" alt="Screenshot (46)" src="https://github.com/user-attachments/assets/e1c93b17-9c02-4c87-83d8-f13eeadcca60" />
<img width="1920" height="1080" alt="Screenshot (47)" src="https://github.com/user-attachments/assets/28b6fa10-36cb-47bb-a30f-b304fcc05dd1" />
<img width="1920" height="1080" alt="Screenshot (45)" src="https://github.com/user-attachments/assets/95c2c5e4-0318-4f87-a90f-753fe27352d3" />


### User Features
- User Authentication System
  - Register with email verification
  - Secure login/logout
  - Password reset functionality
- User Profile Management
  - Update personal information
  - Profile picture upload
  - View order history
- Shopping Experience
  - Browse products by categories
  - Advanced product search
  - Shopping cart management
  - Secure checkout process
  - Multiple payment options

### Admin Features
- Secure Admin Panel
  - Dashboard with analytics
  - Sales reports and statistics
- Product Management
  - Add/Edit/Delete products
  - Bulk product upload
  - Category management
  - Image upload and management
- Order Management
  - View and process orders
  - Update order status
  - Generate invoices

### Technical Features
- Responsive Bootstrap 5 Design
- Mobile-First Approach
- Secure Authentication System
- Image Upload and Processing
- Shopping Cart Functionality
- Order Processing System
- Admin Dashboard
- Database Management

## Technologies Used

- Frontend:
  - HTML5, CSS3, JavaScript
  - Bootstrap 5.3.0
  - Animate.css for animations
  - Font Awesome icons
  - jQuery for AJAX requests

- Backend:
  - PHP 8.0+
  - MySQL/MariaDB
  - Apache Web Server
  - PDO for database operations

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Apache Server with mod_rewrite enabled
- PHP Extensions:
  - PDO
  - mysqli
  - gd (for image processing)
  - curl (for payment integration)
- Composer (for dependency management)
- SSL Certificate (for production)

## Installation

1. Clone this repository to your local machine:
```bash
git clone https://github.com/Taleb013/shop.git
cd shop
```

2. Set up the database:
   - Create a new MySQL database named `shop_db`
   - Configure database credentials in `config.php`

3. Import the database structure:
```sql
-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    location VARCHAR(255),
    profile_image VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount INT DEFAULT 0,
    stock INT DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES product(id)
);
```

4. Set up the project:
   ```bash
   # Create required directories
   mkdir uploads
   chmod 777 uploads  # On Linux/Mac

   # Configure your web server
   # For Apache, ensure mod_rewrite is enabled
   ```

5. Create an admin user:
   ```sql
   INSERT INTO users (name, email, password, role) 
   VALUES ('Admin', 'admin@example.com', '$2y$10$YOUR_HASHED_PASSWORD', 'admin');
   ```

## Configuration

1. Database Configuration (`config.php`):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'shop_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

2. File Upload Settings:
   - Maximum file size: 5MB
   - Allowed types: jpg, jpeg, png, gif
   - Upload directory: `/uploads`

## Usage

1. Start your Apache server and MySQL:
   ```bash
   # Using XAMPP Control Panel
   # Start Apache and MySQL services
   ```

2. Access the website:
   - Main site: `http://localhost/shop`
   - Admin panel: `http://localhost/shop/admin.php`

3. Default Admin Credentials:
   - Email: admin@example.com
   - Password: admin123

4. User Functions:
   - Register/Login
   - Browse products
   - Add to cart
   - Checkout
   - View orders
   - Update profile

5. Admin Functions:
   - Manage products
   - Process orders
   - View reports
   - Manage users

## Security Features

- Password Hashing
- SQL Injection Prevention
- XSS Protection
- CSRF Protection
- Secure File Upload
- Input Validation
- Session Security

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Support

For support, email 2002mdabutaleb@gmail.com or open an issue in the repository.

## License

This project is licensed for user who shared star of that project.

## Acknowledgments

- Bootstrap team for the amazing framework
- XAMPP for the development environment
- All contributors who have helped with the project
