<?php
/**
 * includes/auth-header.php
 *
 * Shared authentication layout for:
 * - Login
 * - Change Password
 */

$pageTitle = $pageTitle ?? 'Sign in';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= e($pageTitle) ?> · <?= e(APP_NAME) ?>
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= e(url('assets/css/style.css')) ?>"
    >

</head>


<body class="df-auth-body">


<div class="df-auth">


    <!-- =====================================================
         LEFT BRANDING SECTION
         ===================================================== -->

    <aside class="df-auth-brand">

        <div class="df-auth-brand-inner">


            <!-- LOGO / SYSTEM NAME -->

            <div class="df-auth-logo">

                <span class="df-auth-logo-mark">

                    <i class="bi bi-file-earmark-text"></i>

                </span>


                <span class="df-auth-logo-text">

                    <strong>
                        UHY WorkDoc Extract
                    </strong>

                    <small>
                        WORK DOCUMENT EXTRACTION SYSTEM
                    </small>

                </span>

            </div>



            <!-- HERO -->

            <div class="df-auth-hero">

                <p class="df-auth-eyebrow">
                    WORK DOCUMENT EXTRACTION
                </p>


                <h1>

                    Extract information.

                    <span>
                        Reduce manual work.
                    </span>

                </h1>


                <p class="df-auth-lead">

                    Capture work documents, extract text with OCR,
                    review the information, and reduce repetitive
                    copy-paste work during audit and accounting tasks.

                </p>



                <!-- FEATURES -->

                <div class="df-auth-features">


                    <div class="df-auth-feature">

                        <span class="df-feature-icon">
                            <i class="bi bi-camera"></i>
                        </span>

                        <span>

                            <strong>
                                Capture
                            </strong>

                            <small>
                                Upload documents from phone or desktop.
                            </small>

                        </span>

                    </div>



                    <div class="df-auth-feature">

                        <span class="df-feature-icon">
                            <i class="bi bi-magic"></i>
                        </span>

                        <span>

                            <strong>
                                Extract
                            </strong>

                            <small>
                                Extract text and information with OCR.
                            </small>

                        </span>

                    </div>



                    <div class="df-auth-feature">

                        <span class="df-feature-icon">
                            <i class="bi bi-pencil-square"></i>
                        </span>

                        <span>

                            <strong>
                                Review
                            </strong>

                            <small>
                                Check extracted information before use.
                            </small>

                        </span>

                    </div>



                    <div class="df-auth-feature">

                        <span class="df-feature-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </span>

                        <span>

                            <strong>
                                Work Faster
                            </strong>

                            <small>
                                Reduce repetitive copy-paste activity.
                            </small>

                        </span>

                    </div>


                </div>

            </div>



            <!-- FOOTER -->

            <p class="df-auth-foot">

                <i class="bi bi-shield-check"></i>

                UHY WorkDoc Extract · Internal use only

            </p>


        </div>

    </aside>



    <!-- =====================================================
         RIGHT LOGIN SECTION
         ===================================================== -->

    <main class="df-auth-panel">

        <div class="df-auth-card">


            <!-- PORTAL LABEL -->

            <div class="df-auth-portal">

                <span class="df-auth-portal-icon">

                    <i class="bi bi-person-lock"></i>

                </span>


                <span>

                    <strong>
                        UHY WORKDOC EXTRACT
                    </strong>

                    <small>
                        Secure access portal
                    </small>

                </span>

            </div>



            <!-- LOGIN CARD -->

            <div class="df-login-box">


                <?php foreach (flash_get() as $f): ?>

                    <div class="df-alert df-alert-<?= e($f['type']) ?>">

                        <?= e($f['message']) ?>

                    </div>

                <?php endforeach; ?>