<?php

// ----------------------------------------
// API: Authentication
// ----------------------------------------

// Authentication action handler
function handleAuthRequest(): void
{
  startAppSession();

  // Login and logout both mutate the session, so require POST and CSRF token.
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('POST required.');
  }

  $token = $_POST['csrf_token'] ?? '';
  if (!is_string($token) || !isValidCsrfToken($token)) {
      http_response_code(403);
      exit('Invalid request token.');
  }

  // Determine the requested authentication action.
  $action = $_GET['action'] ?? '';

  // Login action handler
  if ($action === 'login') {
    // DEMO ONLY: replace this check with a verified OIDC identity (or other secure authentication mechanism).
    $user = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $valid = is_string($user) && is_string($password)
      && hash_equals('admin', $user)
      && hash_equals('pw', $password);

    if (!$valid) {
      echo '<div class="alert alert-danger">Login or password is incorrect.</div>';
      return;
    }

    session_regenerate_id(true);
    $_SESSION['admin_authenticated'] = true;
    echo '
      <button class="btn btn-success"
        hx-get="api/backend.php?action=page&amp;page=admin"
        hx-target="#main-content" hx-push-url="#admin">
        Open dashboard
      </button>';
    return;
  }

  // Logout action handler
  if ($action === 'logout') {
    unset($_SESSION['admin_authenticated']);
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo '
      <button class="btn btn-primary"
        hx-get="api/backend.php?action=page&amp;page=admin"
        hx-target="#main-content" hx-push-url="#admin">
        Sign in again
      </button>';
    return;
  }

  http_response_code(404);
  echo '<div class="alert alert-danger">Unknown authentication action.</div>';
}

// Including this file provides helpers; requesting it runs the endpoint.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    handleAuthRequest();
}

// ----------------------------------------
// Helper functions for session and CSRF (Cross-Site Request Forgery) token management
// ----------------------------------------

// Initializes the application session and ensures a CSRF token is available.
function startAppSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}

// Checks if the current session belongs to an authenticated admin user.
function isAdminAuthenticated(): bool
{
    return !empty($_SESSION['admin_authenticated']);
}

// Validates the provided CSRF token against the one stored in the session.
function isValidCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
