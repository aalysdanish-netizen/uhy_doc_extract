<?php

require_once __DIR__ . '/../includes/bootstrap.php';


// Already logged in?
// Go to dashboard.
if (is_logged_in()) {

    redirect('dashboard.php');

}


$error = '';
$username = '';


if (is_post()) {

    if (!csrf_verify()) {

        $error = 'Your session expired. Please try again.';

    } else {

        $username = trim(
            $_POST['username'] ?? ''
        );

        $result = attempt_login(
            $username,
            $_POST['password'] ?? ''
        );


        if ($result['ok']) {

            // dashboard.php will redirect first-time
            // users to change-password.php.

            redirect('dashboard.php');

        }


        $error = $result['error'];

    }

}


$pageTitle = 'Sign in';

require __DIR__ . '/../includes/auth-header.php';

?>


<!-- =========================================================
     LOGIN HEADING
     ========================================================= -->

<div class="df-login-heading">

    <p class="df-login-kicker">
        WORK DOCUMENT EXTRACTION
    </p>


    <h2 class="df-auth-title">
        Welcome back
    </h2>


    <p class="df-auth-sub">
        Sign in to access your WorkDoc workspace.
    </p>

</div>



<!-- =========================================================
     ERROR MESSAGE
     ========================================================= -->

<?php if ($error): ?>

    <div class="df-alert df-alert-danger">

        <i class="bi bi-exclamation-circle"></i>

        <span>
            <?= e($error) ?>
        </span>

    </div>

<?php endif; ?>



<!-- =========================================================
     LOGIN FORM
     ========================================================= -->

<form
    method="post"
    novalidate
    data-loading-form
>


    <?= csrf_field() ?>



    <!-- USERNAME / EMAIL -->

    <div class="df-field">

        <label
            for="username"
            class="df-label"
        >
            Email
        </label>


        <div class="df-input-group">


            <span class="df-input-icon">

                <i class="bi bi-person"></i>

            </span>


            <input
                type="text"
                id="username"
                name="username"
                class="df-input"
                placeholder="Enter your username"
                value="<?= e($username) ?>"
                autocomplete="username"
                autocapitalize="none"
                autocorrect="off"
                spellcheck="false"
                required
                autofocus
            >


            <span class="df-input-suffix">

                @<?= e(EMAIL_DOMAIN) ?>

            </span>


        </div>

    </div>



    <!-- PASSWORD -->

    <div class="df-field">

        <label
            for="password"
            class="df-label"
        >
            Password
        </label>


        <div class="df-input-group">


            <span class="df-input-icon">

                <i class="bi bi-lock"></i>

            </span>


            <input
                type="password"
                id="password"
                name="password"
                class="df-input"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >


            <button
                type="button"
                class="df-input-btn"
                data-toggle-password="#password"
                aria-label="Show password"
            >

                <i class="bi bi-eye"></i>

            </button>


        </div>

    </div>



    <!-- LOGIN BUTTON -->

    <button
        type="submit"
        class="df-btn df-btn-primary df-btn-block df-login-btn"
    >

        <span class="df-btn-label">
            Sign In
        </span>


        <i class="bi bi-arrow-right"></i>

    </button>



    <!-- SECURITY -->

    <div class="df-login-security">

        <i class="bi bi-shield-lock"></i>

        <span>
            Your connection is protected.
        </span>

    </div>


</form>



<!-- =========================================================
     FORGOT PASSWORD
     ========================================================= -->

<details class="df-forgot">

    <summary>
        Forgot password?
    </summary>


    <p>
        Please contact your administrator to reset your password.
    </p>

</details>



<?php require __DIR__ . '/../includes/auth-footer.php'; ?>