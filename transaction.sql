CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    orderid INT NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,      -- e.g., 'Credit Card', 'PayPal', 'COD'
    payment_status VARCHAR(50) NOT NULL,      -- e.g., 'Paid', 'Pending', 'Failed'
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    transaction_amount DECIMAL(10, 2) NOT NULL,
    payment_reference VARCHAR(100),           -- gateway reference ID or confirmation number
    
    FOREIGN KEY (orderid) REFERENCES orders(orderid) ON DELETE CASCADE
);
