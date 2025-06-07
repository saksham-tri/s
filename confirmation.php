<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Order Confirmation</title>
  <style>
  body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #f0f8ff, #e6f7ff);
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  animation: fadeIn 1s ease-in-out forwards;
}

.confirmation-box {
  background: #ffffff;
  padding: 45px 35px;
  border-radius: 25px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
  text-align: center;
  width: 90%;
  max-width: 450px;
  animation: slideInUp 0.9s ease forwards;
}

h2 {
  color: #28a745;
  margin-bottom: 18px;
  font-size: 30px;
  font-weight: 700;
  letter-spacing: 1px;
}

p {
  color: #555;
  font-size: 19px;
  margin-bottom: 30px;
  line-height: 1.5;
}

a {
  display: inline-block;
  text-decoration: none;
  color: white;
  background-color: #007BFF;
  padding: 14px 30px;
  border-radius: 30px;
  font-size: 17px;
  font-weight: 600;
  transition: background 0.3s ease, transform 0.3s ease;
  box-shadow: 0 6px 18px rgba(0, 123, 255, 0.45);
  animation: pulse 2.5s infinite ease-in-out;
}

a:hover {
  background-color: #0056b3;
  transform: scale(1.08);
  box-shadow: 0 10px 28px rgba(0, 86, 179, 0.65);
  animation: none;
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 6px 18px rgba(0, 123, 255, 0.45);
  }
  50% {
    box-shadow: 0 10px 25px rgba(0, 123, 255, 0.7);
  }
}

  </style>
</head>
<body>
  <div class="confirmation-box">
    <h2>Thank you for your order!</h2>
    <p>A confirmation has been sent to your email.</p>
    <a href="index.php">Back to Home</a>
  </div>
</body>
</html>


