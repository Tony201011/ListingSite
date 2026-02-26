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

        <!-- Sign up Header -->
        <h1 style="font-size: 2.5rem; font-weight: 700; color: #222; margin-bottom: 5px; border-left: 5px solid #e04ecb; padding-left: 15px;">Sign up</h1>

        <!-- Free Trial Section -->
        <div style="margin: 25px 0 20px 0;">
            <h2 style="font-size: 1.4rem; font-weight: 600; color: #e04ecb; margin-bottom: 15px;">Register today and get 21 days free advertising</h2>

            <!-- Bullet Points - Exactly as in image -->
            <ul style="list-style: none; padding: 0; margin: 0; color: #444; font-size: 0.95rem;">
                <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                    <span style="color: #e04ecb; font-weight: bold;">•</span>
                    <span>No credit card details required for signup, no obligations.</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                    <span style="color: #e04ecb; font-weight: bold;">•</span>
                    <span>We rank first page for many searches like Sydney escorts, Melbourne escorts, Brisbane escorts, Adelaide escorts, Canberra escorts, Gold coast escorts, etc.</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                    <span style="color: #e04ecb; font-weight: bold;">•</span>
                    <span>Unlimited photos and videos, Available NOW, Twitter promotions, touring pages, profile booster features and much more....</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                    <span style="color: #e04ecb; font-weight: bold;">•</span>
                    <span>Advertise from $0.79 a day !!!</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                    <span style="color: #e04ecb; font-weight: bold;">•</span>
                    <span>No charge when your profile is set to hidden.</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                    <span style="color: #e04ecb; font-weight: bold;">•</span>
                    <span>Don't loose your profile, when not advertising you still have access to your profile.</span>
                </li>
            </ul>
        </div>

        <hr style="border: none; border-top: 2px solid #f0f0f0; margin: 25px 0;">

        <!-- Registration Form -->
        <form style="background: #ffffff; border: 1px solid #e5e5e5; border-radius: 12px; padding: 35px 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);" method="POST" action="#">
            @csrf

            <!-- Email -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Your email address</label>
                <input type="email"
                       value="s8811w@gmail.com"
                       style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; transition: all 0.3s;"
                       onfocus="this.style.borderColor='#e04ecb'; this.style.boxShadow='0 0 0 3px rgba(224,78,203,0.1)';"
                       onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';"
                       required>
            </div>

            <!-- Password -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Choose your password</label>
                <input type="password"
                       value="**********"
                       style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;"
                       onfocus="this.style.borderColor='#e04ecb'; this.style.boxShadow='0 0 0 3px rgba(224,78,203,0.1)';"
                       onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';"
                       required>
                <div style="font-size: 0.85rem; color: #888; margin-top: 5px;">Your password must be 8-20 characters long. We do recommend that you use letters and numbers.</div>
            </div>

            <!-- Confirm Password -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Retype your password</label>
                <input type="password"
                       value="**********"
                       style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;"
                       onfocus="this.style.borderColor='#e04ecb'; this.style.boxShadow='0 0 0 3px rgba(224,78,203,0.1)';"
                       onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';"
                       required>
            </div>

            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">

            <!-- Nickname -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Your preferred (nick) name</label>
                <input type="text"
                       style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;"
                       placeholder="e.g., SexyBabe"
                       onfocus="this.style.borderColor='#e04ecb'; this.style.boxShadow='0 0 0 3px rgba(224,78,203,0.1)';"
                       onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';"
                       required>
            </div>

            <!-- Mobile Number with Verification Message -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Your mobile number</label>
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <select style="width: 100px; padding: 12px; border: 1px solid #ddd; border-radius: 6px; background: white;">
                        <option value="+61">🇦🇺 +61</option>
                        <option value="+64">🇳🇿 +64</option>
                        <option value="+44">🇬🇧 +44</option>
                    </select>
                    <input type="tel"
                           style="flex: 1; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;"
                           placeholder="Australian mobile number"
                           onfocus="this.style.borderColor='#e04ecb'; this.style.boxShadow='0 0 0 3px rgba(224,78,203,0.1)';"
                           onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                </div>
                <!-- Verification Message Box - EXACT as in image -->
                <div style="background: #e8f0fe; border-left: 4px solid #e04ecb; border-radius: 6px; padding: 15px; font-size: 0.95rem; color: #333; margin-top: 10px;">
                    <span style="font-weight: 600;">We verify all our babes.</span> We need this number so one of our moderators can give you a call. Remember on realbabes.com.au there only real babes ;-) <span style="font-weight: 600;">We will NEVER publish or share this phone number without your permission.</span>
                    <div style="margin-top: 8px; color: #e04ecb; font-size: 0.9rem;">- Australian mobile number</div>
                </div>
            </div>

            <!-- Suburb -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Suburb <span style="font-weight: normal; color: #888;">(your primary main work suburb, select it from the list while typing)</span></label>
                <input type="text"
                       style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;"
                       placeholder="Start typing your suburb..."
                       onfocus="this.style.borderColor='#e04ecb'; this.style.boxShadow='0 0 0 3px rgba(224,78,203,0.1)';"
                       onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';"
                       required>
            </div>

            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">

            <!-- Referral Code -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">Do you have a friend's referral code? <span style="font-weight: normal; color: #888;">(optional field)</span></label>
                <input type="text"
                       style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;"
                       placeholder="Enter code if you have one"
                       onfocus="this.style.borderColor='#e04ecb'; this.style.boxShadow='0 0 0 3px rgba(224,78,203,0.1)';"
                       onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                <div style="font-size: 0.85rem; color: #888; margin-top: 5px;">If you don't have a referral code, leave this field blank.</div>
            </div>

            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">

            <!-- Age Confirmation -->
            <div style="margin-bottom: 20px; display: flex; align-items: center;">
                <input type="checkbox" id="age_confirm" style="width: 18px; height: 18px; margin-right: 10px; accent-color: #e04ecb;" required>
                <label for="age_confirm" style="font-size: 1rem; color: #333; font-weight: 500;">I confirm that I am 18+ years old</label>
            </div>

            <!-- reCAPTCHA -->
            <div style="margin-bottom: 25px;">
                <div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px; padding: 15px; display: flex; align-items: center;">
                    <div style="width: 28px; height: 28px; background: #e04ecb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <span style="color: white; font-weight: bold; font-size: 14px;">G</span>
                    </div>
                    <span style="font-size: 1rem; color: #333;">I'm not a robot</span>
                    <div style="margin-left: auto; display: flex; align-items: center; gap: 8px;">
                        <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="reCAPTCHA" style="width: 24px; height: 24px; opacity: 0.7;">
                        <span style="font-size: 0.8rem; color: #999;">reCAPTCHA</span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    style="width: 100%; background: linear-gradient(135deg, #e04ecb 0%, #c13ab0 100%); color: white; font-weight: 700; font-size: 1.3rem; padding: 16px 0; border: none; border-radius: 50px; cursor: pointer; transition: all 0.3s; box-shadow: 0 5px 15px rgba(224,78,203,0.3);"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(224,78,203,0.4)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(224,78,203,0.3)';">
                Yes sign me up
            </button>

            <!-- Footer with Naughty Shoes Image - EXACT as in image -->
            <div style="text-align: center; margin-top: 40px;">
                <!-- Naughty Shoes Image - Dummy Image with high heel emoji -->
                <div style="margin: 0 auto 15px auto; width: 70px; height: 70px; background: #e04ecb; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 40px; line-height: 1;">👠</span>
                </div>
                <div style="font-size: 1.1rem; color: #e04ecb; font-weight: 500; letter-spacing: 0.5px;">
                    Put on your naughty shoes and join Realbabes today
                </div>
            </div>
        </form>
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
    div[style*="display: flex"][style*="max-width: 1200px"] {
        flex-direction: column !important;
        gap: 20px !important;
    }
    div[style*="padding-right: 30px"] {
        justify-content: center !important;
        padding-right: 0 !important;
    }
    div[style*="padding-left: 30px"] {
        justify-content: center !important;
        padding-left: 0 !important;
    }
    div[style*="flex: 0 0 auto"] {
        order: -1;
    }
    h1 {
        font-size: 2rem !important;
    }
    form {
        padding: 20px !important;
    }
}

/* Hover Effects */
input:hover, select:hover {
    border-color: #e04ecb !important;
}

/* Smooth Transitions */
input, select, button {
    transition: all 0.3s ease;
}

/* Focus States */
input:focus, select:focus {
    outline: none;
    border-color: #e04ecb;
    box-shadow: 0 0 0 3px rgba(224,78,203,0.1);
}
@media(max-width:768px){
    div[style*="height:220px"]{
        flex-direction:column !important;
        height:auto !important;
    }
    h2{
        font-size:24px !important;
    }
}
</style>
@endsection
