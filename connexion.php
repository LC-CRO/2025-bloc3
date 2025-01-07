<?php

require_once('database/db.php');

function generateToken() {
    return bin2hex(random_bytes(32));
}

function loginWithToken($username, $password) {
  $conn = connectDB();
    $query = "SELECT id, username, password_hash FROM administrateurs WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $adminId = $row['id'];
        $hashedPassword = $row['password_hash'];

        if (password_verify($password, $hashedPassword)) {
            $token = generateToken();

            $expirationDate = date('Y-m-d H:i:s', strtotime('+1 day'));
            $insertQuery = "INSERT INTO tokens (user_id, token, expiration_date) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iss", $adminId, $token, $expirationDate);
            $insertStmt->execute();
            return $token;
        }
    }
    return false;
}

function isTokenInDatabase($token) {
  $conn = connectDB();

    $query = "SELECT COUNT(*) AS token_count FROM tokens WHERE token = ? AND expiration_date > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $tokenCount = $row['token_count'];

        return $tokenCount > 0;
    }
    return false;
}
function isTokenValid($token) {
    if(isset($token) && !empty($token)){
        return isTokenInDatabase($token);
    }
    else{
        return false;
    }
}