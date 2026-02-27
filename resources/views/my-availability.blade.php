@extends('layouts.frontend')

@section('content')


<!-- Main Content - Availability Page -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">

        <!-- Back to profile link -->
        <button onclick="window.history.back()" style="background: #cfa1b8; color: white; border: none; border-radius: 8px; padding: 6px 18px; font-size: 1rem; font-weight: 500; margin-bottom: 30px; cursor: pointer;">&lt; Go back</button>

        <!-- Availability Card -->
        <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 40px 30px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <!-- Icon or Emoji (optional) -->
            <div style="font-size: 3rem; margin-bottom: 20px;">📅</div>

            <!-- Main message -->
            <h2 style="font-size: 2rem; font-weight: 600; color: #333; margin-bottom: 15px;">
                You haven't set your availability yet
            </h2>

            <!-- Button -->
            <button style="padding: 16px 40px; background: #e04ecb; border: none; border-radius: 50px; font-size: 1.2rem; font-weight: 600; color: white; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(224,78,203,0.3);">
                Set your availability
            </button>
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

/* Button Hover Effects */
button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(224,78,203,0.4) !important;
    transition: all 0.3s ease;
}

/* Link Hover */
a:hover {
    color: #e04ecb !important;
    transition: color 0.2s;
}

/* Responsive Design */
@media (max-width: 768px) {
    div[style*="padding: 40px 20px"] {
        padding: 20px 15px !important;
    }

    h2 {
        font-size: 1.6rem !important;
    }

    button {
        width: 100% !important;
        padding: 14px 20px !important;
        font-size: 1.1rem !important;
    }

    div[style*="display: flex"][style*="gap: 25px"] {
        gap: 15px !important;
        justify-content: center !important;
    }

    div[style*="margin-left: auto"] {
        margin-left: 0 !important;
    }
}

/* Small phones */
@media (max-width: 480px) {
    h2 {
        font-size: 1.4rem !important;
    }

    div[style*="font-size: 3rem"] {
        font-size: 2.5rem !important;
    }
}
</style>
@endsection
