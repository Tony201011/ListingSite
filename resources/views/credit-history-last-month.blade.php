@extends('layouts.frontend')

@section('content')
<!-- Credits History Page -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">

        <!-- Back link and title -->
        <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; margin-bottom: 20px;">
            <h1 style="font-size: 2.2rem; font-weight: 700; color: #222; margin: 0;">Credits history</h1>
            <a href="#" style="color: #e04ecb; text-decoration: none; font-size: 1rem;">&larr; back to dashboard</a>
        </div>

        <!-- Month section -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 1.8rem; font-weight: 600; color: #222; margin: 0 0 8px 0;">February 2026</h2>
            <div style="font-size: 1.1rem; color: #333; margin-bottom: 5px;">
                Balance forwarded from previous month: <strong>0</strong>
            </div>
        </div>

        <!-- Credits table -->
        <div style="overflow-x: auto; margin-bottom: 40px; border-radius: 12px; border: 1px solid #eaeaea; background: #fff;">
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
                    <tr><td>1 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>2 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>3 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>4 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>5 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>6 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>7 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>8 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>9 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>10 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>11 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>12 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>13 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>14 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>15 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>16 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>17 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>18 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>19 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>20 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>21 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>22 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>23 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>24 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>25 Feb</td><td>0</td><td>21</td><td>21</td></tr>
                    <tr><td>26 Feb</td><td>0</td><td>0</td><td>21</td></tr>
                    <tr><td>27 Feb</td><td>0</td><td>0</td><td>21</td></tr>
                    <tr><td>28 Feb</td><td>0</td><td>0</td><td>21</td></tr>
                </tbody>
            </table>
        </div>


    </div>
</div>

<style>
/* Global Styles */
body, html {
    overflow-x: hidden !important;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

/* Link hover */
a:hover {
    text-decoration: underline !important;
}

/* Credits table styles */
.credits-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 1rem;
}
.credits-table th {
    background: #f8f8f8;
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    color: #555;
    border-bottom: 2px solid #ddd;
}
.credits-table td {
    padding: 14px 12px;
    border-bottom: 1px solid #eaeaea;
    color: #222;
}
/* Alternating row colors for readability */
.credits-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Responsive Design */
@media (max-width: 768px) {
    div[style*="padding: 40px 20px"] {
        padding: 20px 15px !important;
    }

    h1 {
        font-size: 1.8rem !important;
    }

    h2 {
        font-size: 1.5rem !important;
    }

    /* Adjust table padding and font size */
    .credits-table th,
    .credits-table td {
        padding: 10px 8px !important;
        font-size: 0.9rem !important;
    }

    /* Make back link and title stack */
    div[style*="display: flex"][style*="justify-content: space-between"] {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 8px;
    }
}

@media (max-width: 480px) {
    h1 {
        font-size: 1.5rem !important;
    }
    h2 {
        font-size: 1.3rem !important;
    }
    /* Footer links line height */
    div[style*="padding: 30px 0 15px 0"] {
        line-height: 1.8;
    }
}
</style>
@endsection
