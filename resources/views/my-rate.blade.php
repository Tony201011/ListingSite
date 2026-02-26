@extends('layouts.frontend')

@section('content')


<!-- Main Content - My Rates Page -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">

        <!-- Go Back Link -->
        <a href="#" style="display: inline-block; margin-bottom: 20px; color: #e04ecb; text-decoration: none; font-weight: 500;">
            &larr; Go back
        </a>

        <!-- Page Title -->
        <h1 style="font-size: 2.2rem; font-weight: 700; color: #222; margin-bottom: 20px;">
            My rates
        </h1>

        <!-- Description Paragraph -->
        <p style="font-size: 1rem; color: #555; margin-bottom: 30px; line-height: 1.6;">
            You can group your rates by the type of services you offer, for example:<br>
            massages, gfe, pse, kink/bdsm, netflix and chill, online services, lunch/dinner dates, extended & overnight dates, fmty, etc.
        </p>

        <!-- Your rates (not in a group) Section -->
        <div style="padding: 25px; margin-bottom: 30px">
            <h2 style="font-size: 1.5rem; font-weight: 600; color: #333; margin-bottom: 10px;">
                Your rates (not in a group)
            </h2>
            <p style="font-size: 0.95rem; color: #666; margin-bottom: 20px; line-height: 1.5;">
                If you don't have many rates or there is no need to put them in separate groups then you can list them here.
            </p>

            <!-- Rates Table (with example rows) -->
            <div style="overflow-x: auto; margin-bottom: 25px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f0f0f0;">
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd;"></th>
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Incall</th>
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Outcall</th>
                        </tr>
                    </thead>
                    <tbody id="ratesList">
                    </tbody>
                </table>
            </div>

            <!-- "Add new rate" text (exactly as in image) -->
            <p style="font-size: 0.95rem; color: #666; margin-bottom: 15px;">
                Add new rate if you don't have an incall or outcall rate then you can leave that field blank.
            </p>

            <!-- Add Rate Form (always visible) -->
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f0f0f0;">
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Description</th>
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Incall</th>
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Outcall</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1: What do i type in here? with inputs -->
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <input type="text" id="newDesc" placeholder="What do i type in here?" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <input type="text" id="newIncall" placeholder="$" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <input type="text" id="newOutcall" placeholder="$" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            </td>
                        </tr>
                        <!-- Row 2: Extra info optional (only description input) -->
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <input type="text" id="extraInfo" placeholder="Extra info optional" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd;"></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"></td>
                        </tr>
                        <!-- Row 3: Another "What do i type in here?" (optional) -->
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <input type="text" id="extraDesc" placeholder="What do i type in here?" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd;"></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"></td>
                        </tr>
                        <!-- Row 4: Buttons +add rate and cancel -->
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;"></td>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <button onclick="addNewRate()" style="padding: 8px 20px; background: #4CAF50; border: none; border-radius: 50px; font-size: 0.95rem; font-weight: 500; color: white; cursor: pointer; width: 100%;">
                                    + add rate
                                </button>
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <button onclick="clearAddForm()" style="padding: 8px 20px; background: #f0f0f0; border: none; border-radius: 50px; font-size: 0.95rem; font-weight: 500; color: #666; cursor: pointer; width: 100%;">
                                    cancel
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create a new group Section -->
        <div style="padding: 25px;">
            <h2 style="font-size: 1.5rem; font-weight: 600; color: #333; margin-bottom: 10px;">
                Create a new group
            </h2>
            <p style="font-size: 0.95rem; color: #666; margin-bottom: 20px; line-height: 1.5;">
                If you like to create groups, use the button below to create a group for a specific service you offer.
            </p>
            <button style="padding: 12px 30px; background: #e04ecb; border: none; border-radius: 50px; font-size: 1rem; font-weight: 600; color: white; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 10px rgba(224,78,203,0.2);">
                create a new rates group
            </button>
        </div>
    </div>
</div>

<script>
    function addNewRate() {
        // Get values from the first row
        var desc = document.getElementById('newDesc').value.trim();
        var incall = document.getElementById('newIncall').value.trim();
        var outcall = document.getElementById('newOutcall').value.trim();

        // Use placeholder if empty
        if (!desc) desc = '—';
        if (!incall) incall = '—';
        if (!outcall) outcall = '—';

        // Add a new row to the rates table
        var tableBody = document.getElementById('ratesList');
        var newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td style="padding: 10px; border: 1px solid #ddd;">${desc}</td>
            <td style="padding: 10px; border: 1px solid #ddd;">${incall}</td>
            <td style="padding: 10px; border: 1px solid #ddd;">${outcall}</td>
        `;
        tableBody.appendChild(newRow);

        // Clear only the first row inputs (the main ones)
        document.getElementById('newDesc').value = '';
        document.getElementById('newIncall').value = '';
        document.getElementById('newOutcall').value = '';
    }

    function clearAddForm() {
        // Clear all input fields in the add form
        document.getElementById('newDesc').value = '';
        document.getElementById('newIncall').value = '';
        document.getElementById('newOutcall').value = '';
        document.getElementById('extraInfo').value = '';
        document.getElementById('extraDesc').value = '';
    }
</script>

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
    box-shadow: 0 6px 15px rgba(224,78,203,0.3) !important;
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

    h1 {
        font-size: 1.8rem !important;
    }

    h2 {
        font-size: 1.3rem !important;
    }

    button {
        width: 100% !important;
        padding: 12px 20px !important;
        font-size: 0.95rem !important;
    }

    div[style*="display: flex"][style*="gap: 25px"] {
        gap: 15px !important;
        justify-content: center !important;
    }

    div[style*="margin-left: auto"] {
        margin-left: 0 !important;
    }

    table, tbody, tr, td {
        font-size: 0.9rem;
    }

    input {
        font-size: 0.9rem;
    }
}

/* Small phones */
@media (max-width: 480px) {
    h1 {
        font-size: 1.5rem !important;
    }
}
</style>
@endsection
