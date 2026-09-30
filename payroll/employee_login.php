<?php

session_start();

$conn = new mysqli("localhost", "root", "", "db_employee");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM payroll_users WHERE username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // Plain text comparison
        if ($password == $user["password"]) {

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];

            header("Location: employee.php");
            exit();

        } else {

            echo "<script>
                    alert('Incorrect username or password.');
                    window.location.href = 'employee_login.html';
                  </script>";
            exit();
        }

    } else {

        echo "<script>
                alert('Incorrect username or password.');
                window.location.href = 'employee_login.html';
              </script>";
        exit();
    }
}

$conn->close();

?>