@extends('layouts.frontend')

@section('content')
<!-- ================= TOP BANNER ================= -->
<div style="width:100%; background:#b784a7;">
    <div style="display:flex; width:100%; height:350px; overflow:hidden;">
        <!-- LEFT IMAGE -->
        <div style="
            flex:1;
            background:url('https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?q=80&w=1200&auto=format&fit=crop') center center/cover no-repeat;
            position:relative;">
            <div style="position:absolute; inset:0; background:rgba(92,53,89,0.6);"></div>
        </div>
        <!-- CENTER LOGO TEXT -->
        <div style="flex:1; background:#c893b8; display:flex; align-items:center; justify-content:center; flex-direction:column; text-align:center;">
            <h2 style="margin:0; font-size:40px; font-weight:700; color:#000;">
                Hotescorts.com.au
            </h2>
            <span style="font-size:12px; letter-spacing:2px; color:#333;">
                REAL WOMEN NEAR YOU
            </span>
        </div>
        <!-- RIGHT IMAGE -->
        <div style="
            flex:1;
            background:url('https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=1200&auto=format&fit=crop') center center/cover no-repeat;
            position:relative;">
            <div style="position:absolute; inset:0; background:rgba(92,53,89,0.6);"></div>
        </div>
    </div>
</div>
<!-- ================= END BANNER ================= -->

<!-- Purchases History Page -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">
        <!-- Header with back link -->
        <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; margin-bottom: 30px;">
            <h1 style="font-size: 2.2rem; font-weight: 700; color: #222; margin: 0;">Purchases history</h1>
            <a href="#" style="color: #e04ecb; text-decoration: none; font-size: 1rem;">&larr; back to dashboard</a>
        </div>

        <!-- Purchases table (empty state) -->
        <div style="overflow-x: auto; margin-bottom: 40px; border-radius: 12px; border: 1px solid #eaeaea; background: #fff;">
            <table style="width: 100%; border-collapse: collapse; font-size: 1rem;">
                <thead>
                    <tr style="background: #f8f8f8;">
                        <th style="padding: 15px 12px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #ddd;">Date</th>
                        <th style="padding: 15px 12px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #ddd;">Credits</th>
                        <th style="padding: 15px 12px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #ddd;">Price</th>
                        <th style="padding: 15px 12px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #ddd;">Status</th>
                        <th style="padding: 15px 12px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #ddd;">Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" style="padding: 40px 20px; text-align: center; color: #888; font-size: 1.1rem;">
                            You haven't purchased credits yet. <a href="#" style="color: #e04ecb; text-decoration: none; font-weight: 600;">Click here to buy credits</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<style>
/* Global Styles (consistent with previous pages) */
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

/* Responsive Design for banner and page */
@media (max-width: 768px) {
    /* Make banner stack vertically */
    div[style*="height:350px"] {
        flex-direction: column !important;
        height: auto !important;
    }
    div[style*="height:350px"] > div {
        min-height: 250px !important;
    }
    h2 {
        font-size: 30px !important;
    }
    div[style*="padding: 40px 20px"] {
        padding: 20px 15px !important;
    }

    h1 {
        font-size: 1.8rem !important;
    }

    /* Stack top nav */
    div[style*="display: flex"][style*="gap: 20px"] {
        gap: 10px !important;
        flex-direction: column;
        align-items: flex-start !important;
    }

    span[style*="margin-left: auto"] {
        margin-left: 0 !important;
    }

    /* Adjust table padding */
    table th, table td {
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

/* Extra small devices (phones) */
@media (max-width: 480px) {
    h1 {
        font-size: 1.5rem !important;
    }
    h2 {
        font-size: 24px !important;
    }
    span[style*="font-size:12px"] {
        font-size: 10px !important;
    }
    /* Footer links line height */
    div[style*="padding: 30px 0 15px 0"] {
        line-height: 1.8;
    }
    /* Adjust empty message padding and font */
    td[colspan="5"] {
        padding: 30px 15px !important;
        font-size: 1rem !important;
    }
    /* Make the link more tappable */
    td[colspan="5"] a {
        display: inline-block;
        padding: 8px 0;
    }
}

/* Very small phones (<=360px) */
@media (max-width: 360px) {
    div[style*="padding: 40px 20px"] {
        padding: 15px 10px !important;
    }
    table th, table td {
        padding: 8px 5px !important;
        font-size: 0.8rem !important;
    }
    div[style*="font-size: 1.5rem"] {
        font-size: 1.3rem !important;
    }
}
</style>
@endsection
