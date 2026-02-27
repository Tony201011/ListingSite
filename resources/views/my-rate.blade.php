@extends('layouts.frontend')

@section('content')
<!-- Navigation Menu - Exactly as in image -->

<!-- Main Content - My Rates Page -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">

        <!-- Go Back Button -->
        <button onclick="window.history.back()" style="background: #cfa1b8; color: white; border: none; border-radius: 8px; padding: 6px 18px; font-size: 1rem; font-weight: 500; margin-bottom: 30px; cursor: pointer;">&lt; Go back</button>

        <!-- Page Title -->
        <h1 style="font-size: 2.5rem; font-weight: 500; color: #333; margin-bottom: 10px;">My rates</h1>

        <!-- Description Paragraph -->
        <p style="font-size: 1.15rem; color: #222; margin-bottom: 18px; line-height: 1.6;">
            You can group your rates by the type of services you offer, for example: <br>
            <span style="color: #222; font-size: 1.08rem;">massages, <b>gfe</b>, <b>pse</b>, kink/bdsm, netflix and chill, online services, lunch / dinner dates, extended & overnight dates, fmty, etc.</span>
        </p>
        <hr style="border: none; border-top: 2px dotted #a16ba1; margin: 30px 0 25px 0;">

        <!-- Your rates (not in a group) Section -->
        <div style="padding: 0 0 25px 0; margin-bottom: 30px">
            <div style="display: flex; align-items: baseline; gap: 10px; margin-bottom: 0;">
                <h2 style="font-size: 1.35rem; font-weight: 500; color: #222; margin-bottom: 0; margin-right: 8px;">Your rates</h2>
                <span style="font-size: 1.05rem; color: #666; font-weight: 400;">(not in a group)</span>
            </div>
            <p style="font-size: 1rem; color: #7a7a7a; margin-bottom: 18px; line-height: 1.5;">
                If you don't have many rates or there is no need to put them in seprate groups then you can list them here.
            </p>

            <!-- Rates Table (initially empty) -->
            <div style="overflow-x: auto; margin-bottom: 25px;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                    <thead id="ratesTableHead" style="display: none;">
                        <tr style="background: #f0f0f0; border-bottom: 2px solid #ddd;">
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd; color: #222; font-size: 1.08rem; font-weight: 700;">Description</th>
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd; color: #222; font-size: 1.08rem; font-weight: 700;">Incall</th>
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd; color: #222; font-size: 1.08rem; font-weight: 700;">Outcall</th>
                        </tr>
                    </thead>
                    <tbody id="ratesList">
                        <!-- Rows will be added here dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Add Rates Button (visible by default) -->
            <button id="showAddRateBtn" onclick="toggleAddRateForm()" style="padding: 8px 28px; background: #e04ecb; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; color: white; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 10px rgba(224,78,203,0.2); display: inline-block; margin-bottom: 10px;">add rates</button>

            <!-- Hidden Add Rate Form (initially hidden) -->
            <div id="addRateForm" style="display: none; margin-top: 30px;">
                <div style="background: #fff; border: 1px solid #e4c6d6; border-radius: 10px; box-shadow: 0 2px 12px 0 rgba(224,78,203,0.07); padding: 24px 28px 20px 28px; max-width: 950px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #eee; padding-bottom: 16px; margin-bottom: 22px;">
                        <span style="font-size: 1.25rem; font-weight: 600; color: #222;">Add new rate</span>
                        <span style="font-size: 1rem; color: #444; font-weight: 400; margin-left: 8px;">If you don't have an incall or outcall rate then you can leave that field blank</span>
                    </div>
                    <form onsubmit="event.preventDefault(); addNewRate();">
                        <div style="display: flex; gap: 18px; margin-bottom: 18px;">
                            <div style="flex: 2;">
                                <label style="font-weight: 700; color: #222; margin-bottom: 4px; display: block;">Description</label>
                                <input type="text" id="newDesc" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; color: #222; font-weight: 600;">
                                <div style="color: #3490dc; font-size: 0.97rem; margin-top: 2px;">What do i type in here?</div>
                            </div>
                            <div style="flex: 1;">
                                <label style="font-weight: 700; color: #222; margin-bottom: 4px; display: block;">Incall</label>
                                <div style="display: flex; align-items: center;">
                                    <span style="background: #f3f3f3; border: 1px solid #ccc; border-radius: 4px 0 0 4px; padding: 8px 10px; color: #888; font-size: 1rem; border-right: none;">$</span>
                                    <input type="text" id="newIncall" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-left: none; border-radius: 0 5px 5px 0; font-size: 1rem; color: #222; font-weight: 600;">
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <label style="font-weight: 700; color: #222; margin-bottom: 4px; display: block;">Outcall</label>
                                <div style="display: flex; align-items: center;">
                                    <span style="background: #f3f3f3; border: 1px solid #ccc; border-radius: 4px 0 0 4px; padding: 8px 10px; color: #888; font-size: 1rem; border-right: none;">$</span>
                                    <input type="text" id="newOutcall" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-left: none; border-radius: 0 5px 5px 0; font-size: 1rem; color: #222; font-weight: 600;">
                                </div>
                            </div>
                        </div>
                        <div style="margin-bottom: 18px;">
                            <label style="font-weight: 700; color: #222; margin-bottom: 4px; display: block;">Extra info <span style="font-weight: 400; color: #888; font-size: 1rem;">optional</span></label>
                            <textarea id="extraInfo" rows="2" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; color: #222; font-weight: 600;"></textarea>
                            <div style="color: #3490dc; font-size: 0.97rem; margin-top: 2px;">What do i type in here?</div>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" style="padding: 8px 28px; background: #e04ecb; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; color: white; cursor: pointer; margin-right: 5px;">+ add rate</button>
                            <button type="button" onclick="toggleAddRateForm()" style="padding: 8px 22px; background: #eee; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; color: #b3aeb5; cursor: pointer;">cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <hr style="border: none; border-top: 1px solid #bbb; margin: 35px 0 25px 0;">
        <!-- Create a new group Section -->
        <div style="padding: 0;">
            <h2 style="font-size: 1.2rem; font-weight: 500; color: #222; margin-bottom: 8px;">Create a new group</h2>
            <p style="font-size: 1rem; color: #7a7a7a; margin-bottom: 15px; line-height: 1.5;">If you like to create groups, use the button below to create a group for a specific service you offer.</p>
            <button style="padding: 8px 28px; background: #e04ecb; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; color: white; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 10px rgba(224,78,203,0.2);">create a new rates group</button>
        </div>
    </div>
</div>


<script>

    function toggleAddRateForm() {
        var form = document.getElementById('addRateForm');
        var btn = document.getElementById('showAddRateBtn');
        var tableHead = document.getElementById('ratesTableHead');
        if (form.style.display === 'none') {
            form.style.display = 'block';
            btn.style.display = 'none';
            tableHead.style.display = '';
        } else {
            form.style.display = 'none';
            btn.style.display = 'inline-block';
            tableHead.style.display = 'none';
        }
    }

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
            <td style="padding: 10px; border: 1px solid #ddd; color: #222; font-size: 1.05rem; font-weight: 600;">${desc}</td>
            <td style="padding: 10px; border: 1px solid #ddd; color: #222; font-size: 1.05rem; font-weight: 600;">${incall}</td>
            <td style="padding: 10px; border: 1px solid #ddd; color: #222; font-size: 1.05rem; font-weight: 600;">${outcall}</td>
        `;
        tableBody.appendChild(newRow);

        // Clear only the first row inputs (the main ones)
        document.getElementById('newDesc').value = '';
        document.getElementById('newIncall').value = '';
        document.getElementById('newOutcall').value = '';
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
