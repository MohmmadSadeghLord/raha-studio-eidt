<?php
/* Template Name: فرم سفارش */
if (!defined('ABSPATH')) exit;
get_header();
?>
<div class="raha-order-page">
    <div class="raha-container">

        <header class="raha-order-header">
            <div class="raha-order-logo" aria-hidden="true">✦</div>
            <div>
                <h1>ثبت درخواست طراحی سایت</h1>
                <p>در چند مرحله کوتاه، نیاز خود را توضیح دهید</p>
            </div>
        </header>

        <div class="raha-order-progress">
            <div class="raha-order-progress__info">
                <span id="stepLabel">مرحله ۱ از ۱۰</span>
                <span id="stepPercent">۱۰٪</span>
            </div>
            <div class="raha-order-progress__track">
                <div class="raha-order-progress__fill" id="progressFill"></div>
            </div>
        </div>

        <main class="raha-order-step" id="stepContainer"></main>

        <div class="raha-order-error" id="orderError" role="alert"></div>

        <nav class="raha-order-nav">
            <button type="button" class="raha-btn raha-btn--ghost" id="backBtn">→ قبلی</button>
            <button type="button" class="raha-btn raha-btn--primary" id="nextBtn">بعدی ←</button>
        </nav>

    </div>
</div>
<?php get_footer();