@extends('layouts.frontend')

@section('content')
<!-- ================= TOP SIGNUP BANNER ================= -->
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
                hotescorts.com.au
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

<!-- Main Content -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px 20px 20px;">

        <!-- Login Header -->
        <h1 style="font-size: 2.2rem; font-weight: 700; color: #222; margin-bottom: 20px; text-align: center;">
            Login to your HOTESCORTS profile
        </h1>

        <!-- Login Form -->
        <div style="background: #ffffff; border: 1px solid #e5e5e5; border-radius: 12px; padding: 35px 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 25px;">
            <form method="POST" action="#">
                @csrf

                <!-- Email -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Your email address</label>
                    <input type="email"
                           value="s8811w@gmail.com"
                           style="width: 100%; padding: 14px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; transition: all 0.3s; background: #f9f9f9;"
                           onfocus="this.style.borderColor='#e04ecb'; this.style.boxShadow='0 0 0 3px rgba(224,78,203,0.1)'; this.style.background='#fff';"
                           onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none'; this.style.background='#f9f9f9';"
                           required>
                </div>

                <!-- Password -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Your password</label>
                    <input type="password"
                           style="width: 100%; padding: 14px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; transition: all 0.3s; background: #f9f9f9;"
                           onfocus="this.style.borderColor='#e04ecb'; this.style.boxShadow='0 0 0 3px rgba(224,78,203,0.1)'; this.style.background='#fff';"
                           onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none'; this.style.background='#f9f9f9';"
                           required>
                </div>

                <!-- Keep me logged in -->
                <div style="margin-bottom: 25px; display: flex; align-items: center;">
                    <input type="checkbox" id="keep_logged_in" style="width: 18px; height: 18px; margin-right: 10px; accent-color: #e04ecb;">
                    <label for="keep_logged_in" style="font-size: 1rem; color: #555;">Keep me logged in on this device</label>
                </div>

                <!-- reCAPTCHA -->
                <div style="margin-bottom: 30px;">
                    <div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 18px 15px; display: flex; align-items: center;">
                        <div style="width: 32px; height: 32px; background: #e04ecb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <span style="color: white; font-weight: bold; font-size: 16px;">G</span>
                        </div>
                        <span style="font-size: 1.1rem; color: #333;">I'm not a robot</span>
                        <div style="margin-left: auto; display: flex; align-items: center; gap: 10px;">
                            <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="reCAPTCHA" style="width: 28px; height: 28px; opacity: 0.8;">
                            <span style="font-size: 0.85rem; color: #888;">reCAPTCHA</span>
                        </div>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit"
                        style="width: 100%; background: linear-gradient(135deg, #e04ecb 0%, #c13ab0 100%); color: white; font-weight: 700; font-size: 1.3rem; padding: 16px 0; border: none; border-radius: 50px; cursor: pointer; transition: all 0.3s; box-shadow: 0 5px 15px rgba(224,78,203,0.3);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(224,78,203,0.4)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(224,78,203,0.3)';">
                    Login
                </button>
            </form>
        </div>

        <!-- Forgot Password Links -->
        <div style="text-align: center; margin-bottom: 30px;">
            <p style="color: #666; margin-bottom: 12px;">
                Forgot your login details?
                <a href="#" style="color: #e04ecb; text-decoration: none; font-weight: 500;">you can reset it here</a>
            </p>
            <p style="color: #666;">
                If you haven't signed up before,
                <a href="#" style="color: #e04ecb; text-decoration: none; font-weight: 500;">you can sign up here</a>
            </p>
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

/* Responsive Design */
@media (max-width: 900px) {
    div[style*="max-width: 800px"] {
        padding: 20px 15px !important;
    }

    div[style*="display: flex"][style*="gap: 15px"] {
        gap: 10px !important;
        font-size: 0.8rem !important;
    }

    h1 {
        font-size: 1.8rem !important;
    }

    [style*="border-radius: 12px"] {
        padding: 25px 20px !important;
    }
}

@media (max-width: 768px) {
    div[style*="height:350px"] {
        height: 250px !important;
    }

    div[style*="font-size:40px"] {
        font-size: 28px !important;
    }

    div[style*="display: flex"][style*="gap: 25px"] {
        gap: 15px !important;
        flex-direction: column !important;
        align-items: center !important;
    }
}

/* Hover Effects */
input:hover {
    border-color: #e04ecb !important;
}

/* Smooth Transitions */
input, button {
    transition: all 0.3s ease;
}

/* Focus States */
input:focus {
    outline: none;
    border-color: #e04ecb;
    box-shadow: 0 0 0 3px rgba(224,78,203,0.1);
    background: #fff !important;
}

/* Button hover effect */
button[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(224,78,203,0.4);
}

/* Link hover effect */
a:hover {
    color: #e04ecb !important;
    text-decoration: underline !important;
}

/* Top navigation styling */
div[style*="gap: 15px"] a {
    transition: color 0.3s ease;
}

/* Keep the pink Signup link as shown in image */
div[style*="gap: 15px"] a[style*="color: #e04ecb"] {
    color: #e04ecb !important;
    font-weight: 600;
}
</style>
@endsection
