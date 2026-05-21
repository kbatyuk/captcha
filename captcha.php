<?php
session_start();
// Get the original URI so we can send the user back after verification
$redirect_to = $_GET['orig_uri'] ?? '/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user's answer matches the code stored in the session
    if (isset($_POST['answer']) && strtoupper($_POST['answer']) === $_SESSION['captcha_code']) {
        // SUCCESS: Set the VerifiedHuman cookie for 24 hours
        setcookie("VerifiedHuman", "1", time() + 86400, "/", "", true, true);
        header("Location: " . $redirect_to);
        exit;
    } else {
        $error = "Incorrect code. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Check | WHOI Data Library & Archives</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --whoi-blue: #0F395F;
            --whoi-hover: #1a4d7a;
            --bg-gray: #f4f7f9;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            background-color: var(--bg-gray); 
        }

        .card { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
            text-align: center; 
            max-width: 450px; 
            width: 90%; 
            box-sizing: border-box;
        }

        h2 { 
            color: var(--whoi-blue); 
            margin: 0 0 10px 0; 
            font-size: 1.8rem; 
            font-weight: 700;
        }

        p { 
            color: #555; 
            margin-bottom: 25px; 
            line-height: 1.5; 
            font-size: 0.95rem;
        }

        /* Container for the CAPTCHA image */
        .captcha-box { 
            background: #fff;
            padding: 15px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 25px;
            max-width: 320px;
        }
        
        .captcha-box img { 
            width: 100%; 
            height: auto; 
            display: block; 
            border-radius: 4px;
        }

        /* Prominent Input Field */
        input[type="text"] { 
            width: 100%; 
            padding: 18px; 
            margin-bottom: 15px; 
            border: 2px solid #d1d9e0; 
            border-radius: 8px; 
            font-size: 1.5rem; 
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            text-align: center; 
            text-transform: uppercase; 
            letter-spacing: 0.3em;
            box-sizing: border-box; 
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input[type="text"]:focus { 
            border-color: var(--whoi-blue); 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(15, 57, 95, 0.1); 
        }

        /* Primary Button */
        button { 
            background-color: var(--whoi-blue); 
            color: white; 
            border: none; 
            padding: 16px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 700; 
            width: 100%; 
            font-size: 1.1rem; 
            transition: background-color 0.2s; 
        }

        button:hover { 
            background-color: var(--whoi-hover); 
        }

        .error { 
            color: #d9534f; 
            margin-top: 20px; 
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="card">
        <h2>Security Check</h2>
        <p>Please enter the characters shown below to continue to the <strong>WHOI Data Archives</strong>.</p>
        
        <form method="POST" action="">
            <div class="captcha-box">
                <img src="captcha-image.php" alt="Security CAPTCHA Code">
            </div>

            <input type="text" 
                   name="answer" 
                   placeholder="ENTER CODE" 
                   required 
                   autofocus 
                   maxlength="5" 
                   autocomplete="off" 
                   spellcheck="false">

            <button type="submit">Verify & Continue</button>
        </form>

        <?php if(isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>

</body>
</html>
