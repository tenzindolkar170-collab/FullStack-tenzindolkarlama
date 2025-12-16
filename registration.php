<?php
session_start();

$errors = [];
$success = "";
$name = $email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // JSON File
    $file = "users.json";

    if (empty($errors)) {

        // Create file if not exists
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]));
        }

        $users = json_decode(file_get_contents($file), true);

        // Check duplicate email
        foreach ($users as $user) {
            if ($user["email"] === $email) {
                $errors[] = "Email already registered.";
                break;
            }
        }

        // Save user
        if (empty($errors)) {
            $users[] = [
                "name" => $name,
                "email" => $email,
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "registered_at" => date("Y-m-d H:i:s")
            ];

            file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
            $success = "Registration successful!";
            $name = $email = "";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .box { width: 400px; margin: auto; background: white; padding: 20px; }
        .error { color: red; font-size: 14px; }
        .success { color: green; font-size: 16px; }
        input { width: 100%; padding: 8px; margin: 5px 0; }
        button { padding: 10px; width: 100%; background: green; color: white; border: none; }
    </style>
</head>

<body>

<div class="box">
    <h2>User Registration</h2>

    <!-- Messages -->
    <?php
    foreach ($errors as $error) {
        echo "<p class='error'>$error</p>";
    }

    if ($success != "") {
        echo "<p class='success'>$success</p>";
    }
    ?>

    <!-- Form -->
    <form method="post">
        <label>Full Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">

        <label>Password</label>
        <input type="password" name="password">

        <label>Confirm Password</label>
        <input type="password" name="confirm">

        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>