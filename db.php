<?php
  // Include database configuration
  require_once 'config/db-config.php';

  // dsn (data source name)
  $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

  try {
    $pdo = new PDO($dsn, $user, $pass);
    
    // Error (for debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Koneksi berhasil dibuat
  } catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
  }
  
  /*
   * ========================================
   * DOKUMENTASI PENGGUNAAN FUNGSI CRUD
   * ========================================
   *
   * 1. INCLUDE FILE
   *    require_once 'src/include/db.php';
   *
   * 2. CONTOH PENGGUNAAN:
   *
   *    // FUNGSI BERBASIS TABEL (Existing)
   *
   *    // READ - Membaca data
   *    $users = db_read('users'); // Semua data dari tabel users
   *    $user = db_read('users', ['id' => 1]); // User dengan id = 1
   *    $names = db_read('users', [], 'name, email'); // Hanya kolom name dan email
   *
   *    // CREATE - Menambah data
   *    $newUser = [
   *        'name' => 'John Doe',
   *        'email' => 'john@example.com',
   *        'password' => password_hash('secret', PASSWORD_DEFAULT)
   *    ];
   *    $userId = db_create('users', $newUser); // Mengembalikan ID baru
   *
   *    // UPDATE - Mengupdate data
   *    $updateData = ['name' => 'Jane Doe', 'email' => 'jane@example.com'];
   *    $conditions = ['id' => 1];
   *    $affectedRows = db_update('users', $updateData, $conditions); // Mengembalikan jumlah row yang terupdate
   *
   *    // DELETE - Menghapus data
   *    $conditions = ['id' => 1];
   *    $affectedRows = db_delete('users', $conditions); // Mengembalikan jumlah row yang terhapus
   *
   *    // FUNGSI RAW SQL (New - Lebih Sederhana)
   *
   *    // db_query - Untuk SELECT queries
   *    $users = db_query("SELECT * FROM users"); // Semua data
   *    $user = db_query("SELECT * FROM users WHERE id = ?", [1]); // Dengan parameter
   *    $active = db_query("SELECT name, email FROM users WHERE status = ? AND id > ?", ['active', 5]);
   *
   *    // db_execute - Untuk INSERT/UPDATE/DELETE queries
   *    $count = db_execute("UPDATE users SET status = ? WHERE id > ?", ['inactive', 10]);
   *    $count = db_execute("DELETE FROM users WHERE status = ?", ['inactive']);
   *    $id = db_execute("INSERT INTO users (name, email) VALUES (?, ?)", ['John', 'john@example.com']);
   *
   * 3. CATATAN:
   *    - Semua fungsi menggunakan prepared statements untuk keamanan
   *    - Fungsi tabel mengembalikan array untuk read, ID untuk create, dan jumlah row untuk update/delete
   *    - Fungsi raw SQL (db_query) mengembalikan array untuk SELECT
   *    - Fungsi raw SQL (db_execute) mengembalikan jumlah row yang terpengaruh
   *    - Gunakan fungsi tabel untuk operasi sederhana, raw SQL untuk query kompleks
   *    - Jika terjadi error, fungsi akan mengembalikan string pesan error
   * ========================================
   */
  
  // READ - Membaca data dari tabel
  function db_read($table, $conditions = [], $columns = '*') {
    global $pdo;
    
    try {
      $sql = "SELECT $columns FROM $table";
      $params = [];
      
      if (!empty($conditions)) {
        $where_clauses = [];
        foreach ($conditions as $column => $value) {
          $where_clauses[] = "$column = :$column";
          $params[":$column"] = $value;
        }
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
      }
      
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      return "Error: " . $e->getMessage();
    }
  }
  
  // CREATE - Menambah data baru ke tabel
  function db_create($table, $data) {
    global $pdo;
    
    try {
      $columns = implode(', ', array_keys($data));
      $placeholders = ':' . implode(', :', array_keys($data));
      
      $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
      
      $stmt = $pdo->prepare($sql);
      $stmt->execute($data);
      
      return $pdo->lastInsertId();
    } catch (PDOException $e) {
      return "Error: " . $e->getMessage();
    }
  }
  
  // UPDATE - Mengupdate data di tabel
  function db_update($table, $data, $conditions) {
    global $pdo;
    
    try {
      $set_clauses = [];
      $params = [];
      
      foreach ($data as $column => $value) {
        $set_clauses[] = "$column = :set_$column";
        $params[":set_$column"] = $value;
      }
      
      $where_clauses = [];
      foreach ($conditions as $column => $value) {
        $where_clauses[] = "$column = :where_$column";
        $params[":where_$column"] = $value;
      }
      
      $sql = "UPDATE $table SET " . implode(', ', $set_clauses) . " WHERE " . implode(' AND ', $where_clauses);
      
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      
      return $stmt->rowCount();
    } catch (PDOException $e) {
      return "Error: " . $e->getMessage();
    }
  }
  
  // DELETE - Menghapus data dari tabel
  function db_delete($table, $conditions) {
    global $pdo;
    
    try {
      $where_clauses = [];
      $params = [];
      
      foreach ($conditions as $column => $value) {
        $where_clauses[] = "$column = :$column";
        $params[":$column"] = $value;
      }
      
      $sql = "DELETE FROM $table WHERE " . implode(' AND ', $where_clauses);
      
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      
      return $stmt->rowCount();
    } catch (PDOException $e) {
      return "Error: " . $e->getMessage();
    }
  }
  
  // RAW SQL FUNCTIONS - Lebih sederhana untuk query langsung
  
  // db_query - Untuk SELECT queries dengan positional parameters
  function db_query($sql, $params = []) {
    global $pdo;
    
    try {
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      return "Error: " . $e->getMessage();
    }
  }
  
  // db_execute - Untuk INSERT/UPDATE/DELETE queries dengan positional parameters
  function db_execute($sql, $params = []) {
    global $pdo;
    
    try {
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      
      return $stmt->rowCount();
    } catch (PDOException $e) {
      return "Error: " . $e->getMessage();
    }
  }

?>
