# CAPTCHA

A PHP library for generating and validating CAPTCHA (Completely Automated Public Turing test to tell Computers and Humans Apart) tokens to protect your applications from automated abuse.

## Features

- Easy-to-use CAPTCHA generation
- Secure token validation
- Session-based storage support
- Customizable parameters
- Lightweight and fast

## Installation

Install via Composer:

```bash
composer require kbatyuk/captcha
```

## Quick Start

### Generate a CAPTCHA

```php
<?php
require_once 'vendor/autoload.php';

use Captcha\CaptchaGenerator;

// Create a new CAPTCHA instance
$captcha = new CaptchaGenerator();

// Generate a new token
$token = $captcha->generate();

// Store token in session (for later validation)
$_SESSION['captcha_token'] = $token;
```

### Validate a CAPTCHA

```php
<?php
require_once 'vendor/autoload.php';

use Captcha\CaptchaValidator;

$validator = new CaptchaValidator();

// Get the user input
$userInput = $_POST['captcha_input'] ?? '';

// Validate the CAPTCHA
if ($validator->validate($userInput, $_SESSION['captcha_token'])) {
    echo "CAPTCHA validation successful!";
    unset($_SESSION['captcha_token']); // Clear after successful validation
} else {
    echo "CAPTCHA validation failed!";
}
```

## Usage Examples

### Basic Form Protection

```php
<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CAPTCHA
    if (!validateCaptcha($_POST['captcha'] ?? '', $_SESSION['captcha_token'] ?? '')) {
        $error = "Invalid CAPTCHA";
    } else {
        // Process form...
        $success = "Form submitted successfully!";
        unset($_SESSION['captcha_token']);
    }
}

// Generate CAPTCHA for form display
$captcha_token = generateCaptcha();
$_SESSION['captcha_token'] = $captcha_token;
?>
```

## Configuration

Customize CAPTCHA behavior:

```php
<?php
use Captcha\CaptchaGenerator;

$captcha = new CaptchaGenerator([
    'length' => 6,           // Token length
    'timeout' => 300,        // Token expiration time in seconds
    'case_insensitive' => true,  // Allow case-insensitive validation
]);
```

## Requirements

- PHP 7.4 or higher
- No external dependencies

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues.

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For issues, questions, or suggestions, please open an issue on [GitHub](https://github.com/kbatyuk/captcha/issues).

---

**Note**: This CAPTCHA library is intended for basic protection. For production environments handling sensitive operations, consider using additional security measures or established services like Google reCAPTCHA.
