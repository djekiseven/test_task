<?php
$env = parse_ini_file('.env');

class Database {
  private $connection;

  public function __construct($host, $user, $password, $database) {
    $this->connection = new mysqli($host, $user, $password, $database);

    if ($this->connection->connect_error) {
      throw new Exception("Ошибка подключения к базе данных: " . $this->connection->connect_error);
    }
  }

  public function getRandomImageId() {
    $imageId = rand(1,4);
    return json_encode($imageId);
  }

  public function increaseViewCount($imageId) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    $query = "INSERT INTO logs (ip_address, user_agent, image_id, view_date, view_count) ";
    $query .= "VALUES (?, ?, ?, CURRENT_TIMESTAMP, 1) ";
    $query .= "ON DUPLICATE KEY UPDATE view_date = CURRENT_TIMESTAMP, view_count = view_count + 1";

    $statement = $this->connection->prepare($query);
    if (!$statement) {
      throw new Exception("Ошибка подготовки запроса: " . $this->connection->error);
    }

    $statement->bind_param('ssi', $ip, $userAgent, $imageId);

    if (!$statement->execute()) {
      throw new Exception("Ошибка выполнения запроса: " . $statement->error);
    }

    $statement->close();
  }

  public function getViewCount($imageId) {
    $query = "SELECT SUM(view_count) as total_view_count FROM logs WHERE image_id = ?";

    $statement = $this->connection->prepare($query);
    if (!$statement) {
      throw new Exception("Ошибка подготовки запроса: " . $this->connection->error);
    }

    $statement->bind_param('i', $imageId);
    $statement->execute();

    $result = $statement->get_result()->fetch_assoc();
    $statement->close();

    return $result ? $result['total_view_count'] : 0;
  }
}

try {
  $db = new Database($env["MYSQL_HOST"], $env["MYSQL_USER"], $env["MYSQL_PASS"], $env["MYSQL_DB"]);

  if ($_GET['action'] == 'getRandomImageId') {
    echo $db->getRandomImageId();
  }

  if ($_GET['action'] == 'increaseViewCount') {
    $imageId = $_GET['imageId'];
    $db->increaseViewCount($imageId);
  }

  if ($_GET['action'] == 'getViewCount') {
    $imageId =$_GET['imageId'];
    echo $db->getViewCount($imageId);
  }
} catch (Exception $e) {
  echo $e->getMessage();
}
?>