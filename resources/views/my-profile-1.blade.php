@extends('layouts.frontend')

@section('content')
<!-- Main Content - Profile Dashboard -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">

        <!-- Dashboard Title -->
        <h1 style="font-size: 2.2rem; font-weight: 700; color: #222; margin-bottom: 30px;">
            Hotescorts dashboard
        </h1>

        <!-- Profile Setup Section - Exactly as in image -->
        <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">

            <!-- Header Text -->
            <p style="font-size: 1.2rem; color: #333; margin-bottom: 25px;">
                To set up your profile please do the next three steps:
            </p>

            <!-- Steps Table - Exactly as in image -->
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                    <!-- Completed Row -->
                    <tr>
                        <td style="padding: 10px 0; width: 70%;"></td>
                        <td style="padding: 10px 0; font-weight: 600; color: #333; text-align: right;">Completed</td>
                    </tr>

                    <!-- Step 1 - Write profile text -->
                    <tr>
                        <td style="padding: 12px 0; color: #333; font-size: 1.1rem;">1. Write profile text</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <div style="width: 24px; height: 24px; border: 2px solid #ccc; border-radius: 50%; display: inline-block;"></div>
                        </td>
                    </tr>

                    <!-- Step 2 - Upload photos -->
                    <tr>
                        <td style="padding: 12px 0; color: #333; font-size: 1.1rem;">2. Upload photos</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <div style="width: 24px; height: 24px; border: 2px solid #ccc; border-radius: 50%; display: inline-block;"></div>
                        </td>
                    </tr>

                    <!-- Step 3 - Verify your photos -->
                    <tr>
                        <td style="padding: 12px 0; color: #333; font-size: 1.1rem;">3. Verify your photos</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <div style="width: 24px; height: 24px; border: 2px solid #ccc; border-radius: 50%; display: inline-block;"></div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Profile Text Section - Exactly as in image -->
            <div>
                <!-- Action Buttons -->
                <div style="display: flex; gap: 15px; justify-content: flex-start; flex-wrap: wrap;">
                    <button style="padding: 14px 30px; background: #e04ecb; border: none; border-radius: 50px; font-size: 1.1rem; font-weight: 600; color: white; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 10px rgba(224,78,203,0.3);">
                        Start Writing Your Profile Text
                    </button>
                </div>
            </div>
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
    box-shadow: 0 6px 15px rgba(224,78,203,0.4) !important;
    transition: all 0.3s ease;
}

/* Responsive Design */
@media (max-width: 768px) {
    div[style*="padding: 40px 20px"] {
        padding: 20px 15px !important;
    }

    h1 {
        font-size: 1.8rem !important;
        margin-bottom: 20px !important;
    }

    div[style*="padding: 30px"] {
        padding: 20px !important;
    }

    p {
        font-size: 1rem !important;
        margin-bottom: 20px !important;
    }

    table td {
        font-size: 1rem !important;
        padding: 10px 0 !important;
    }

    table td:first-child {
        width: 70% !important;
    }

    table td:last-child {
        width: 30% !important;
    }

    div[style*="width: 24px"] {
        width: 22px !important;
        height: 22px !important;
    }

    button {
        width: 100% !important;
        padding: 14px 20px !important;
        font-size: 1rem !important;
        border-radius: 50px !important;
    }

    div[style*="display: flex"][style*="justify-content: flex-start"] {
        justify-content: center !important;
    }
}

/* Small phones */
@media (max-width: 480px) {
    h1 {
        font-size: 1.5rem !important;
    }

    table td {
        font-size: 0.95rem !important;
    }

    table td:first-child {
        width: 65% !important;
    }

    table td:last-child {
        width: 35% !important;
    }

    div[style*="width: 24px"] {
        width: 20px !important;
        height: 20px !important;
        border-width: 1.5px !important;
    }
}

/* Focus States */
button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(224,78,203,0.3);
}

/* Smooth transitions */
button {
    transition: all 0.3s ease;
}
</style>
@endsection
