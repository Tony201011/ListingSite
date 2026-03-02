@extends('layouts.frontend')

@section('content')
<div class="credits-history-container">
    <div class="credits-content">
        <!-- Header with back link -->
        <div class="credits-header">
            <h1 class="credits-title">Credits history</h1>
            <a href="#" class="back-link">&larr; back to dashboard</a>
        </div>

        <!-- Month section -->
        <div class="month-section">
            <h2 class="month-title">March 2026</h2>
            <div class="balance-forward">
                Balance forwarded from previous month: <strong>21</strong>
            </div>
            <a href="#" class="prev-month-link">previous month</a>
        </div>

        <!-- Credits table -->
        <div class="table-responsive">
            <table class="credits-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Credits used</th>
                        <th>Credits received</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1 Mar</td>
                        <td>0</td>
                        <td>0</td>
                        <td>21</td>
                    </tr>
                    <tr>
                        <td>2 Mar</td>
                        <td>0</td>
                        <td>0</td>
                        <td>21</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Balance card -->
        <div class="balance-card">
            <div class="balance-label">YOUR CREDITS BALANCE</div>
            <div class="balance-number">21</div>
        </div>
    </div>
</div>

<style>
/* General reset – matching buy credits page */
body, html {
    margin: 0;
    padding: 0;
    font-family: 'Montserrat', Arial, Helvetica, sans-serif;
    background: #fff;
    color: #222;
}

/* Container */
.credits-history-container {
    min-height: 100vh;
    background: #fff;
    padding: 20px;
}

/* Top navigation */
.top-nav {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 20px;
    background: #fff;
    padding: 12px 0;
    margin-bottom: 20px;
    border-bottom: 1px solid #f0e0f0;
}
.nav-link {
    color: #e04ecb;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: color 0.2s;
}
.nav-link:hover {
    color: #b33e9e;
    text-decoration: underline;
}
.nav-follow {
    margin-left: auto;
    color: #888;
    font-style: italic;
    font-size: 0.95rem;
}

/* Credits content */
.credits-content {
    max-width: 800px;
    margin: 0 auto;
}

/* Header */
.credits-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    flex-wrap: wrap;
    margin-bottom: 30px;
}
.credits-title {
    font-size: 2.6rem;
    font-weight: 700;
    margin: 0;
    color: #222;
}
.back-link {
    color: #e04ecb;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    transition: color 0.2s;
}
.back-link:hover {
    color: #b33e9e;
    text-decoration: underline;
}

/* Month section */
.month-section {
    margin-bottom: 25px;
}
.month-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #222;
    margin: 0 0 8px 0;
}
.balance-forward {
    font-size: 1.1rem;
    color: #333;
    margin-bottom: 5px;
}
.balance-forward strong {
    color: #222;
    font-weight: 700;
}
.prev-month-link {
    color: #e04ecb;
    text-decoration: none;
    font-size: 0.95rem;
}
.prev-month-link:hover {
    text-decoration: underline;
    color: #b33e9e;
}

/* Table */
.table-responsive {
    overflow-x: auto;
    margin-bottom: 40px;
    border-radius: 12px;
    border: 1px solid #eaeaea;
    background: #fff;
}
.credits-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 1rem;
}
.credits-table th {
    background: #f8f8f8;
    color: #555;
    font-weight: 600;
    padding: 15px 12px;
    text-align: left;
    border-bottom: 2px solid #ddd;
}
.credits-table td {
    padding: 14px 12px;
    border-bottom: 1px solid #eaeaea;
    color: #222;
}
.credits-table tr:last-child td {
    border-bottom: none;
}
.credits-table td:first-child,
.credits-table th:first-child {
    padding-left: 20px;
}
.credits-table td:last-child,
.credits-table th:last-child {
    padding-right: 20px;
}

/* Balance card */
.balance-card {
    background: #fff;
    border: 2px solid #e0cbe0;
    border-radius: 20px;
    padding: 25px 30px;
    text-align: center;
    max-width: 300px;
    margin: 0 auto 40px auto;
    box-shadow: 0 5px 15px rgba(224,78,203,0.1);
}
.balance-label {
    font-size: 1.1rem;
    color: #888;
    letter-spacing: 1px;
    margin-bottom: 10px;
}
.balance-number {
    font-size: 3.5rem;
    font-weight: 800;
    color: #e04ecb;
    line-height: 1;
}

/* Footer */
.footer-links {
    text-align: center;
    color: #888;
    font-size: 0.9rem;
    padding: 30px 0 15px 0;
    border-top: 1px solid #ddd;
}
.footer-links a {
    color: #e04ecb;
    text-decoration: none;
    margin: 0 5px;
}
.footer-links a:hover {
    text-decoration: underline;
    color: #b33e9e;
}
.footer-links .sep {
    color: #ccc;
    margin: 0 2px;
}
.footer-restricted {
    text-align: center;
    font-size: 0.85rem;
    color: #aaa;
    padding-bottom: 20px;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .credits-title {
        font-size: 2rem;
    }
    .month-title {
        font-size: 1.5rem;
    }
    .top-nav {
        gap: 15px;
    }
}

@media (max-width: 600px) {
    .credits-title {
        font-size: 1.8rem;
    }
    .month-title {
        font-size: 1.3rem;
    }
    .top-nav {
        gap: 10px;
        flex-direction: column;
        align-items: flex-start;
    }
    .nav-follow {
        margin-left: 0;
    }
    .credits-header {
        flex-direction: column;
        gap: 10px;
    }
    .credits-table th,
    .credits-table td {
        padding: 10px 8px;
        font-size: 0.9rem;
    }
    .balance-card {
        max-width: 100%;
        padding: 20px;
    }
    .balance-number {
        font-size: 2.8rem;
    }
    .footer-links {
        line-height: 1.8;
    }
}

@media (max-width: 480px) {
    .credits-title {
        font-size: 1.5rem;
    }
    .month-title {
        font-size: 1.2rem;
    }
    .balance-number {
        font-size: 2.2rem;
    }
    .balance-label {
        font-size: 1rem;
    }
}

@media (max-width: 360px) {
    .credits-title {
        font-size: 1.3rem;
    }
    .month-title {
        font-size: 1rem;
    }
    .balance-forward {
        font-size: 0.95rem;
    }
}
</style>
@endsection
