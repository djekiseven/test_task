CREATE TABLE logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(15),
  user_agent VARCHAR(255),
  view_date DATETIME,
  image_id INT ,
  view_count INT,
  UNIQUE(ip_address, user_agent, image_id)
);