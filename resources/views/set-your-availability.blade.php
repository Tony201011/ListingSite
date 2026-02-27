@extends('layouts.frontend')

@section('content')


<!-- Main Content - Set Availability Page -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">



        <button onclick="window.history.back()" style="background: #cfa1b8; color: white; border: none; border-radius: 8px; padding: 6px 18px; font-size: 1rem; font-weight: 500; margin-bottom: 30px; cursor: pointer;">&lt; back to profile</button>

        <!-- Page Title -->
        <h1 style="font-size: 2.2rem; font-weight: 400; color: #222; margin-bottom: 10px;">Set your availability</h1>
        <a href="#" style="color: #4a4a9a; font-size: 1rem; text-decoration: underline; font-weight: 400; margin-bottom: 10px; display: inline-block; margin-top: -5px;">&lt;&lt;&lt; Show me my availability</a>

        <!-- Instructions -->
        <div style="margin-bottom: 18px;">
            <ul style="list-style: none; padding: 0; margin: 0; color: #222; font-size: 1rem; line-height: 1.6;">
                <li style="margin-bottom: 2px;">
                    <span style="color: #4a4a9a; text-decoration: underline; cursor: pointer;">This 7 day schedule will <a href="#" style="color: #4a4a9a; text-decoration: underline;">repeat every week</a>.</span>
                </li>
                <li style="margin-bottom: 2px; color: #444;">Uncheck the days you do not work and for the days you work set times/availability.</li>
                <li style="margin-bottom: 2px; color: #444;">You can always overrule your schedule for specific dates.</li>
            </ul>
        </div>

        <!-- Availability Form (stacked, card style) -->
        <form style="margin-bottom: 30px;">
            @php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            @endphp
            @foreach($days as $day)
            <div style="border-bottom: 1px solid #e0e0e0; padding: 18px 0 10px 0; display: flex; align-items: flex-start; gap: 18px; flex-wrap: wrap;">
                <div style="min-width: 120px; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" checked style="accent-color: #e04ecb; width: 18px; height: 18px;">
                    <span style="font-size: 1.1rem; color: #222; font-weight: 500;">{{ $day }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <select style="padding: 7px 18px 7px 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; color: #444; min-width: 80px;">
                        <option>FROM</option>
                        <option>8:00</option>
                        <option>9:00</option>
                        <option>10:00</option>
                        <option>11:00</option>
                        <option>12:00</option>
                        <option>13:00</option>
                        <option>14:00</option>
                        <option>15:00</option>
                        <option>16:00</option>
                        <option>17:00</option>
                        <option>18:00</option>
                        <option>19:00</option>
                        <option>20:00</option>
                    </select>
                    <span style="color: #888; font-size: 1rem;">TO</span>
                    <select style="padding: 7px 18px 7px 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; color: #444; min-width: 80px;">
                        <option>TILL</option>
                        <option>9:00</option>
                        <option>10:00</option>
                        <option>11:00</option>
                        <option>12:00</option>
                        <option>13:00</option>
                        <option>14:00</option>
                        <option>15:00</option>
                        <option>16:00</option>
                        <option>17:00</option>
                        <option>18:00</option>
                        <option>19:00</option>
                        <option>20:00</option>
                        <option>21:00</option>
                        <option>22:00</option>
                        <option>23:00</option>
                    </select>
                </div>
                <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 4px; font-size: 1rem; color: #222;">
                        <input type="checkbox" style="accent-color: #e04ecb; width: 16px; height: 16px;"> Till late
                    </label>
                    <label style="display: flex; align-items: center; gap: 4px; font-size: 1rem; color: #222;">
                        <input type="checkbox" style="accent-color: #e04ecb; width: 16px; height: 16px;"> All day
                    </label>
                    <label style="display: flex; align-items: center; gap: 4px; font-size: 1rem; color: #222;">
                        <input type="checkbox" style="accent-color: #e04ecb; width: 16px; height: 16px;"> By appointment
                    </label>
                </div>
            </div>
            @endforeach
        </form>

        <!-- Update Button -->
        <div style="display: flex; justify-content: center; margin-top: 30px;">
            <button style="padding: 16px 40px; background: #e04ecb; border: none; border-radius: 8px; font-size: 1.2rem; font-weight: 600; color: white; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(224,78,203,0.3);">update your availability</button>
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

    h1 {
        font-size: 1.8rem !important;
    }

    ul li {
        font-size: 0.9rem !important;
    }

    table, thead, tbody, tr, td, th {
        font-size: 0.9rem;
    }

    td div[style*="gap: 15px"] {
        gap: 8px !important;
        flex-direction: column;
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
    h1 {
        font-size: 1.5rem !important;
    }

    td {
        padding: 8px !important;
    }

    input {
        font-size: 0.85rem;
    }
}
</style>
@endsection
