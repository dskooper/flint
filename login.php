<?php
require_once('utils.php');

$client = getTwitterClient();
$error = getError();
$success_message = '';

// Check if already logged in
if ($client->isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username 	= isset($_POST['username']) ? $_POST['username'] : '';
    $password 	= isset($_POST['password']) ? $_POST['password'] : '';
    $apiUrl  	= isset($_POST['apiUrl']) ? $_POST['apiUrl'] : '';
    $remember 	= isset($_POST['remember']) ? true : false;

    if (empty($apiUrl)) {
    setError("You must specify an API root to use Flint.");
        header("Location: login.php");
        exit;
    } else if (empty($username) || empty($password)) {
        setError("You must specify a username and password to log in.");
        header("Location: login.php");
        exit;
    } else {
        $auth_result = $client->authenticate($username, $password, $apiUrl, $remember);
        
        if ($auth_result === true) {
            header("Location: index.php");
            exit;
        } else {
            // Check if this is a rate limit error
            if (is_array($auth_result) && isset($auth_result['errors']) && is_array($auth_result['errors'])) {
                foreach ($auth_result['errors'] as $errorItem) {
                    if (isset($errorItem['code']) && $errorItem['code'] == 88) {
                        setError("You've reached the Flint request limit. Please wait a moment and try again later.");
                        break;
                    }
                }
            }
            
            // Default error message if not a rate limit issue
            if (!$error) {
                setError("Something went wrong!");
            }
            
            header("Location: login.php");
            exit;
        }
    }
}

$pageTitle = "Flint Login";
include('layout_header.php');
?>
<div>
    <form action="login.php" method="post" style="text-align: center;">
	<br>
        <b>API root:</b>
        <input type="text" name="apiUrl" id="apiUrl">
	<br>
        <b>Username:</b>
        <input type="text" name="username" id="username">
        <br>
        <b>Password:</b>
        <input type="password" name="password" id="password">
        <br>
        <input type="checkbox" name="remember">
        <label>Remember me</label>
        <br>
        <br>
        <button type="submit">Login</button>
    </form>
</div>
<div class="title">About</div>
<div>
	Flint is a lightweight web client for the Twitter 1.x API. <br>
	It should be compatible with any Twitter 1.x reimplementation (e.g. DigUpTheBird) or similar (e.g. BlueTweety).
</div>
<div class="title">Usage</div>
<div>
	For longevity reasons, Flint does not provide an API root (you have to specify it). <br>
	Make sure to include the full URL <b>including the HTTP(S) protocol!</b>
</div>
<div class="title">Notice</div>
<div>For security reasons (this client supports the insecure HTTP protocol), cookies only last 1 day before expiring.</div>

<?php include('layout_footer.php'); ?> 
