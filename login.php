<?php
session_start();
require_once './db.php'; // DB connection

// Redirect if already logged in (check user_id instead of 'user')
if (isset($_SESSION['user_id'])) {
    header("Location: ./Dashboard/index.php");
    exit();
}

$error_message = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (empty($password)) {
        $error_message = "Please enter your password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Save useful user info in session for easy checks later
                $_SESSION['user_id'] = $user['id'];           // Assuming your DB has 'id' column
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'] ?? ''; // Optional: any other user info

                header("Location: ./Dashboard/index.php");
                exit();
            } else {
                $error_message = "Incorrect email or password.";
            }
        } else {
            $error_message = "Incorrect email or password.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Inventory System</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous">

    <style>
        body {
            background: linear-gradient(135deg, rgb(13, 15, 27), rgb(142, 198, 216));
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            padding: 2rem;
            color: white;
            width: 100%;
            max-width: 400px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
        }

        .form-control::placeholder {
            color: #ccc;
        }

        .btn-primary {
            background-color: #8ec6d8;
            border-color: #8ec6d8;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #74b3c7;
            border-color: #74b3c7;
        }
    </style>
</head>
<body>

<div class="login-card" role="main" aria-label="Login Form">
    <div class="text-center mb-4">
        <i class="fas fa-sign-in-alt fa-3x" aria-hidden="true"></i>
        <h3 class="mt-2">kass inventory system login</h3>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" novalidate>
        <div class="form-group">
            <label for="emailInput">Email</label>
            <input id="emailInput" type="email" name="email" class="form-control" placeholder="Enter Email" required autofocus
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="passwordInput">Password</label>
            <input id="passwordInput" type="password" name="password" class="form-control" placeholder="********" required autocomplete="off">
        </div>

        <button type="submit" name="login" class="btn btn-primary btn-block" aria-label="Login">
            <i class="fas fa-sign-in-alt mr-2" aria-hidden="true"></i> Login
        </button>

        <div class="mt-3 text-center">
            <a href="register.php" class="text-white">Don't have an account? Sign up</a>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>
