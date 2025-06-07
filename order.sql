CREATE TABLE orders (
    orderid INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    productid INT NOT NULL,
    productname VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    size VARCHAR(50),
    image VARCHAR(255),
    catid INT,
    quantity INT NOT NULL DEFAULT 1,
    status ENUM('pending','confirmed','processing','shipped','out of Delivery','Delivered','Cancelled','Returned') NOT NULL DEFAULT 'pending',
    courier_name VARCHAR(100),
    tracking_number VARCHAR(100),
    tracking_url VARCHAR(500),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
