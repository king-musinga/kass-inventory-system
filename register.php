<?php
session_start(); // Start the session if not already started
require_once './db.php'; // Database connection

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

$error_message = "";
$success_message = "";

if (isset($_POST['save'])) {
    // Check if database connection is available
    if (!$conn) {
        $error_message = "Database connection failed. Please try again later.";
        // Log the actual error for debugging, but don't display it to the user
        error_log("Database connection failed during registration: " . mysqli_connect_error());
    } else {
        // Sanitize and validate inputs
        $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
        $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password']; // Get raw password for hashing

        // Basic server-side validation
        if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
            $error_message = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        } elseif (strlen($password) < 8) { // Example: enforce minimum password length
            $error_message = "Password must be at least 8 characters long.";
        } else {
            // Check if email already exists
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $error_message = "This email is already registered.";
            } else {
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert the user data using prepared statements
              $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $firstname, $lastname, $email, $hashed_password);

if ($stmt->execute()) {
    $success_message = "Registration successful! You can now log in.";
} else {
    $error_message = "Error during registration. Please try again.";
    error_log("Registration insert failed: " . $stmt->error);
}

$stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Signup - Inventory System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="User Registration for Inventory System">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
          integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        :root {
            --primary-bg: linear-gradient(135deg, rgb(13, 15, 27), rgb(142, 198, 216));
            --card-bg: rgba(255, 255, 255, 0.1);
        }

        body {
            background: var(--primary-bg);
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex; /* Use flexbox for centering */
            justify-content: center;
            align-items: center;
        }

        .signup-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white; /* Ensure text is visible on the transparent card */
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.2); /* Transparent input background */
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white; /* Input text color */
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7); /* Placeholder color */
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(142, 198, 216, 0.5);
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .btn-primary {
            background-color: #8ec6d8; /* Match primary background color */
            border-color: #8ec6d8;
        }

        .btn-primary:hover {
            background-color: #7ab2c7;
            border-color: #7ab2c7;
        }

        a.text-white-hover:hover {
            color: rgba(255, 255, 255, 0.8) !important;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="signup-card p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-user-plus fa-3x text-white mb-3"></i>
                    <h2 class="text-white">Create Account</h2>
                </div>

                <form action="register.php" method="post" autocomplete="off">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-user text-primary"></i>
                                </span>
                            </div>
                            <input type="text" name="firstname" class="form-control" placeholder="First Name" required
                                   autocomplete="off" value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-user text-primary"></i>
                                </span>
                            </div>
                            <input type="text" name="lastname" class="form-control" placeholder="Last Name" required
                                   autocomplete="off" value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-envelope text-primary"></i>
                                </span>
                            </div>
                            <input type="email" name="email" class="form-control" placeholder="Email" required
                                   autocomplete="off" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                            </div>
                            <input type="password" name="password" class="form-control" placeholder="Password (min 8 characters)" required
                                   autocomplete="new-password">
                        </div>
                    </div>

                    <button class="btn btn-primary btn-block py-2 mt-3" type="submit" name="save">
                        <i class="fas fa-user-plus mr-2"></i> Register
                    </button>

                    <div class="text-center mt-3">
                        <a class="text-white text-white-hover" href="login.php">Already have an account? Login here.</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>