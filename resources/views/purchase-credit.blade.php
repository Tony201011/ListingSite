@extends('layouts.frontend')

@section('content')
<div class="purchase-credit-container">
    <!-- Back link -->
    <a href="#" class="dashboard-back-link">&larr; return to your dashboard</a>

    <div class="purchase-credit-content">
        <h1 class="purchase-credit-title">Buy credits</h1>

        <div class="purchase-credit-desc">
            Your current credits balance = <strong>21</strong>. When your profile is set to visible you will be charged 1 credit per day.
        </div>

        <p class="purchase-credit-desc">
            Choose how many credits you want to buy and what name you want on your invoice, then click <em>'continue to checkout'</em>.
        </p>

        <div class="purchase-credit-note">
            <strong>All prices are in Australian dollars and including GST</strong>
        </div>

        <form class="purchase-credit-form">
            <!-- Credit options -->
            <div class="credit-options">
                <div class="credit-option">
                    <div class="credit-option-row">
                        <span class="credit-radio">
                            <input type="radio" name="credit" id="credit7" value="7">
                        </span>
                        <label for="credit7" class="credit-label">
                            <strong>7 credits / $10 AUD</strong>
                        </label>
                        <button type="button" class="credit-select-btn" onclick="document.getElementById('credit7').checked = true;">select</button>
                    </div>
                </div>

                <div class="credit-option">
                    <div class="credit-option-row">
                        <span class="credit-radio">
                            <input type="radio" name="credit" id="credit30" value="30">
                        </span>
                        <label for="credit30" class="credit-label">
                            <strong>30 credits / $35 AUD</strong>
                        </label>
                        <button type="button" class="credit-select-btn" onclick="document.getElementById('credit30').checked = true;">select</button>
                    </div>
                </div>

                <div class="credit-option">
                    <div class="credit-option-row">
                        <span class="credit-radio">
                            <input type="radio" name="credit" id="credit60" value="60">
                        </span>
                        <label for="credit60" class="credit-label">
                            <strong>60 credits / $65 AUD</strong>
                        </label>
                        <button type="button" class="credit-select-btn" onclick="document.getElementById('credit60').checked = true;">select</button>
                    </div>
                </div>

                <div class="credit-option">
                    <div class="credit-option-row">
                        <span class="credit-radio">
                            <input type="radio" name="credit" id="credit120" value="120">
                        </span>
                        <label for="credit120" class="credit-label">
                            <strong>120 credits / $120 AUD</strong>
                        </label>
                        <button type="button" class="credit-select-btn" onclick="document.getElementById('credit120').checked = true;">select</button>
                    </div>
                </div>

                <div class="credit-option">
                    <div class="credit-option-row">
                        <span class="credit-radio">
                            <input type="radio" name="credit" id="credit180" value="180">
                        </span>
                        <label for="credit180" class="credit-label">
                            <strong>180 credits / $160 AUD</strong>
                        </label>
                        <button type="button" class="credit-select-btn" onclick="document.getElementById('credit180').checked = true;">select</button>
                    </div>
                </div>
            </div>

            <hr class="credit-divider">

            <div class="invoice-field">
                <label for="invoice_name" class="invoice-label">
                    Invoice for <span class="invoice-hint">(this name appears on the invoice)</span>
                </label>
                <input type="text" id="invoice_name" class="invoice-input" value="Sourabh wadhwa">
            </div>

            <button type="submit" class="continue-btn">continue to checkout</button>
        </form>
    </div>
</div>

<style>
/* General reset */
body, html {
    margin: 0;
    padding: 0;
    font-family: 'Montserrat', Arial, Helvetica, sans-serif;
    background: #fff;
    color: #222;
}

/* Container */
.purchase-credit-container {
    min-height: 100vh;
    background: #fff;
    padding: 20px;
}

/* Back link */
.dashboard-back-link {
    display: inline-block;
    color: #e04ecb;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 20px;
    transition: color 0.2s;
}
.dashboard-back-link:hover {
    color: #b33e9e;
    text-decoration: underline;
}

/* Content */
.purchase-credit-content {
    max-width: 700px;
    margin: 0 auto;
}

/* Title */
.purchase-credit-title {
    font-size: 2.6rem;
    font-weight: 700;
    margin: 0 0 20px 0;
    color: #222;
}

/* Description */
.purchase-credit-desc {
    font-size: 1.1rem;
    color: #333;
    margin-bottom: 20px;
    line-height: 1.5;
}

/* Note */
.purchase-credit-note {
    font-size: 1rem;
    color: #222;
    margin-bottom: 30px;
}

/* Credit options */
.credit-options {
    margin-bottom: 30px;
}

.credit-option {
    border-bottom: 1px solid #eaeaea;
    padding: 12px 0;
}

.credit-option:last-child {
    border-bottom: none;
}

.credit-option-row {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.credit-radio {
    display: flex;
    align-items: center;
}

.credit-radio input[type="radio"] {
    width: 20px;
    height: 20px;
    accent-color: #e04ecb;
    margin: 0;
}

.credit-label {
    flex: 1;
    font-size: 1.2rem;
    color: #e04ecb;
    font-weight: 600;
    cursor: pointer;
    min-width: 200px; /* keeps text from wrapping too early */
}

/* Select button – exactly as in image: rectangular with slight rounding */
.credit-select-btn {
    background: transparent;
    border: 2px solid #e04ecb;
    color: #e04ecb;
    font-size: 1rem;
    font-weight: 600;
    padding: 8px 24px;
    border-radius: 6px; /* less rounded, more like image */
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.credit-select-btn:hover {
    background: #e04ecb;
    color: #fff;
}

/* Divider */
.credit-divider {
    border: 0;
    border-top: 2px solid #eaeaea;
    margin: 30px 0;
}

/* Invoice field */
.invoice-field {
    margin-bottom: 30px;
}

.invoice-label {
    display: block;
    font-size: 1.1rem;
    font-weight: 500;
    color: #222;
    margin-bottom: 8px;
}

.invoice-hint {
    font-size: 0.95rem;
    color: #888;
    font-weight: 400;
}

.invoice-input {
    width: 100%;
    max-width: 500px;
    padding: 14px 16px;
    font-size: 1.15rem;
    border: 2px solid #e0cbe0;
    border-radius: 8px;
    background: #fff;
    color: #222;
    transition: border 0.2s;
    box-sizing: border-box;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.invoice-input:focus {
    outline: none;
    border-color: #e04ecb;
    background: #faf7fa;
}

/* Continue button – exactly as in image */
.continue-btn {
    width: 220px;
    max-width: 100%;
    background: #e04ecb;
    color: #fff;
    border: none;
    border-radius: 50px; /* pill shape as in image */
    padding: 16px 0;
    font-size: 1.15rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(224,78,203,0.3);
    display: block;
    margin: 0 auto;
}

.continue-btn:hover {
    background: #b33e9e;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(224,78,203,0.4);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 600px) {
    .purchase-credit-title {
        font-size: 2rem;
    }

    .credit-label {
        font-size: 1.1rem;
        min-width: 0; /* allow text to shrink */
    }

    .credit-select-btn {
        padding: 6px 18px;
        font-size: 0.95rem;
    }

    .invoice-input,
    .continue-btn {
        max-width: 100%;
    }

    .continue-btn {
        font-size: 1.2rem;
        padding: 16px 20px;
    }

    .continue-btn {
        width: 100%;
        font-size: 1rem;
        padding: 14px 0;
    }
}

/* On very small screens, let the button move to next line naturally if needed */
@media (max-width: 480px) {
    .credit-option-row {
        gap: 10px;
    }

    .credit-label {
        font-size: 1rem;
        flex-basis: 100%; /* forces text to take full width, button below */
        margin-bottom: 5px;
    }

    .credit-select-btn {
        width: 100%;
        text-align: center;
        padding: 10px 16px;
    }

    .continue-btn {
        font-size: 1.1rem;
        padding: 14px 20px;
    }
}

@media (max-width: 360px) {
    .purchase-credit-title {
        font-size: 1.8rem;
    }

    .purchase-credit-desc {
        font-size: 1rem;
    }
}
</style>

@endsection
