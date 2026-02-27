@extends('layouts.frontend')

@section('content')
<div style="background: #fff; min-height: 100vh;">
    <div style="max-width: 1100px; margin: 0 auto; padding: 40px 20px;">

        <!-- Dashboard button & warning banner (as provided) -->
        <button style="background: #e0e0e0; color: #b33e9e; border: none; border-radius: 6px; padding: 6px 18px; font-size: 1rem; font-weight: 500; margin-bottom: 18px; cursor: pointer;">&lt; To dashboard</button>
        <div style="background: #f76c6c; color: #fff; font-size: 1.08rem; font-weight: 500; border-radius: 6px; padding: 10px 18px; margin-bottom: 18px;">
            Your profile is currently not approved. You need to send verification photos. <a href="#" style="color: #fff; text-decoration: underline; font-weight: 600;">More info</a>
        </div>

        <!-- Action buttons grid (responsive) -->
        <div class="profile-btn-grid">
            <button class="profile-btn">edit profile</button>
            <button class="profile-btn">hide profile</button>
            <button class="profile-btn">photos</button>
            <button class="profile-btn">Add photos</button>
            <button class="profile-btn">My videos</button>
            <button class="profile-btn">My rates <span class="profile-btn-new">new</span></button>
            <button class="profile-btn">My tours</button>
            <button class="profile-btn">Availability</button>
            <button class="profile-btn">Short URL</button>
            <button class="profile-btn">Online now</button>
            <button class="profile-btn">Available now</button>
            <button class="profile-btn">Set &amp; Forget</button>
            <button class="profile-btn">My Babe Rank</button>
            <button class="profile-btn">Corona msg</button>
            <button class="profile-btn profile-btn-green">Help &amp; Faq</button>
        </div>

        <!-- Main profile row (left text / right contact+photo) -->
        <div class="profile-main-row">
            <!-- LEFT COLUMN: Profile text -->
            <div class="profile-left">
                <!-- Name + location -->
                <div class="profile-name-row">
                    <h1 class="profile-name">Sourabh wadhwa</h1>
                    <span class="profile-location">Melbourne VIC</span>
                </div>
                <!-- Duplicate name (as per original code) -->
                <div class="profile-displayname">Sourabh wadhwa</div>

                <!-- About me -->
                <section class="profile-section">
                    <h2 class="profile-section-title">About me</h2>
                    <div class="profile-text">
                        It is illegal in Vic & QLD to describe your sexual services in details, you also cannot refer to the term massage. In QLD you cannot advertise 'doubles'. If you are in VIC please do not forget to mention your SWA Licence number
                    </div>
                </section>

                <!-- My Stats -->
                <section class="profile-section">
                    <h2 class="profile-section-title">My Stats</h2>
                    <div class="stats-grid">
                        <div><strong>Age group:</strong> 25 - 29</div>
                        <div><strong>Ethnicity:</strong> Arabian</div>
                        <div><strong>Hair color:</strong> Dark</div>
                        <div><strong>Hair length:</strong> Short</div>
                        <div><strong>Body type:</strong> Curvy</div>
                        <div><strong>Bust size:</strong> Busty</div>
                        <div><strong>Length:</strong> Average (164cm - 176cm)</div>
                    </div>
                    <!-- pill tags -->
                    <div class="profile-tags">
                        <span class="tag">mif</span>
                        <span class="tag">heterosexual</span>
                        <span class="tag">outfit requests welcome</span>
                    </div>

                    <!-- NEW: Contact icons section (fixed unreadable text) -->
                    <div class="contact-icons-section">
                        <div class="contact-phone-row">
                            <span class="phone-icon">📞</span>
                            <span class="contact-phone-number">0415 573 077</span>
                        </div>
                        <div class="contact-icons-row">
                            <div class="contact-icons-block">
                                <a href="tel:+61415573077" style="text-decoration:none; color:#020202;">
                                    <img src="https://www.realbabes.com.au//img/layout/send-nr-to-phone.jpg" alt="call me on my phone" class="contact-icons-img">
                                    <div class="contact-icons-label">call me on your phone</div>
                                </a>
                            </div>
                            <div class="contact-icons-block">
                                <a href="#" onclick="$('#emailform').modal({backdrop: false}); return false;" style="text-decoration:none; color:#020202;">
                                    <img src="https://www.realbabes.com.au//img/layout/email-me.jpg" alt="send me an email" class="contact-icons-img">
                                    <div class="contact-icons-label">email me</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Contact me for -->
                <section class="profile-section">
                    <h2 class="profile-section-title">Contact me for</h2>
                    <ul class="contact-list">
                        <li>» Incalls only</li>
                        <li>» Social, Netflix, Lunch & Dinner dates</li>
                        <li>» Extended or Overnight bookings</li>
                    </ul>
                </section>

                <!-- pending verification -->
                <div class="pending-verification">Pending verification by Realbabes Admin</div>
            </div>

            <!-- RIGHT COLUMN: Contact block + Photos -->
            <div class="profile-right">
                <!-- Phone & booking card (reworked for perfect alignment) -->
                <div class="contact-card">
                    <div class="contact-row">
                        <!-- Phone icon + number + description -->
                        <div class="contact-phone-block">
                            <span class="phone-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                    <rect x="4" y="3" width="16" height="18" rx="3" stroke="#e04ecb" stroke-width="2"/>
                                    <circle cx="12" cy="17" r="1.5" fill="#e04ecb"/>
                                </svg>
                            </span>
                            <div class="phone-details">
                                <span class="phone-number">0415 573 077</span>
                                <span class="phone-desc">I accept phone calls & SMS</span>
                            </div>
                        </div>
                        <!-- Booking button (triggers modal) -->
                        <button class="booking-btn" onclick="$('#bookingModal').show();">Booking enquiries</button>
                    </div>
                </div>

                <!-- My photos card (exactly as in image) -->
                <div class="photos-card">
                    <div class="photos-title">My photos</div>
                    <div class="photo-grid">
                        <img src="https://dummyimage.com/300x400/cccccc/222.png&text=Profile+Photo" alt="Profile photo" class="profile-photo-img">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BOOKING ENQUIRY MODAL (hidden by default) -->
<div id="bookingModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h2 class="modal-title">Email booking enquiry</h2>
        <form>
            <div class="form-group">
                <label>Your name</label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label>Your email (required)</label>
                <input type="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Your phone</label>
                <input type="tel" class="form-control">
            </div>
            <div class="form-group">
                <label>Preferred contact method</label>
                <div class="radio-group">
                    <label><input type="radio" name="contact_method" value="email"> Email</label>
                    <label><input type="radio" name="contact_method" value="phone"> Phone</label>
                    <label><input type="radio" name="contact_method" value="both"> Email or Phone</label>
                </div>
            </div>
            <div class="form-group">
                <label>When / What time do you like to book</label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label>What services are you interested in</label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label>How long for you like to book</label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label>Where would you like to meet</label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label>Any other comments / enquiries things I need to know</label>
                <textarea class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-actions">
                <button type="submit" class="submit-btn">Submit</button>
                <button type="button" class="cancel-btn" onclick="$('#bookingModal').hide();">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ----- base reset ----- */
body, html {
    overflow-x: hidden !important;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

/* ----- action buttons grid (responsive) ----- */
.profile-btn-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 28px;
    margin-top: 8px;
}
.profile-btn {
    background: #fff;
    color: #b48ba0;
    border: 1.5px solid #d6c7d6;
    border-radius: 7px;
    font-size: 1.13rem;
    font-weight: 400;
    padding: 10px 0;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
}
.profile-btn:hover {
    background: #f6e6f6;
    color: #b33e9e;
    border-color: #e04ecb;
}
.profile-btn-green {
    background: #2ecc40 !important;
    color: #fff !important;
    border: none !important;
    font-weight: 600;
}
.profile-btn-new {
    color: #f00;
    font-size: 0.85em;
    font-weight: 600;
    margin-left: 2px;
    vertical-align: super;
}
@media (max-width: 900px) {
    .profile-btn-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 600px) {
    .profile-btn-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .profile-btn {
        font-size: 1rem;
    }
}
@media (max-width: 400px) {
    .profile-btn-grid {
        grid-template-columns: 1fr;
    }
}

/* ----- main row (two columns) ----- */
.profile-main-row {
    display: flex;
    flex-wrap: wrap;
    gap: 32px;
    align-items: flex-start;
}
.profile-left {
    flex: 1 1 380px;
    min-width: 320px;
    max-width: 600px;
}
.profile-right {
    flex: 0 0 320px;
    max-width: 340px;
    width: 100%;
}

/* ----- left column typography & spacing ----- */
.profile-name-row {
    display: flex;
    align-items: baseline;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 5px;
}
.profile-name {
    font-size: 2.1rem;
    font-weight: 700;
    color: #222;
    margin: 0;
}
.profile-location {
    font-size: 1.2rem;
    color: #888;
}
.profile-displayname {
    color: #e04ecb;
    font-size: 1.15rem;
    font-weight: 500;
    margin-bottom: 18px;
}
.profile-section {
    margin-bottom: 24px;
}
.profile-section-title {
    font-size: 1.25rem;
    color: #222;
    font-weight: 500;
    margin: 0 0 8px 0;
}
.profile-text {
    font-size: 1rem;
    color: #444;
    line-height: 1.5;
}
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 24px;
    margin: 8px 0 12px 0;
}
.stats-grid div {
    color: #222;
}
.stats-grid strong {
    color: #555;
    font-weight: 600;
}
.profile-tags {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.tag {
    display: inline-block;
    background: #f6e6f6;
    color: #b33e9e;
    font-size: 0.98rem;
    border-radius: 12px;
    padding: 3px 14px;
}
.contact-list {
    list-style: none;
    padding: 0;
    margin: 8px 0 0 0;
    font-size: 1.08rem;
    color: #b33e9e;
}
.contact-list li {
    margin-bottom: 4px;
}
.pending-verification {
    color: #888;
    font-size: 0.98rem;
    margin-top: 12px;
}

/* ----- New contact icons section (fixed unreadable) ----- */
.contact-icons-section {
    margin: 18px 0 18px 0;
    padding: 16px 14px;
    background: #f9f9f9;
    border: 2px solid #e0cbe0;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(224,78,203,0.08);
}
.contact-phone-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    font-size: 1.5rem;
    font-weight: 700;
    color: #222;
}
.contact-phone-number {
    letter-spacing: 1px;
}
.contact-icons-row {
    display: flex;
    align-items: center;
    gap: 28px;
    flex-wrap: wrap;
    justify-content: flex-start;
}
.contact-icons-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 90px;
}
.contact-icons-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #e04ecb;
    background: #fff;
    margin-bottom: 6px;
    transition: transform 0.2s;
}
.contact-icons-img:hover {
    transform: scale(1.05);
}
.contact-icons-label {
    font-size: 0.9rem;
    color: #222;
    text-align: center;
    line-height: 1.2;
}

/* ----- right column: contact card & photos card ----- */
.contact-card {
    background: #fff;
    border: 2px solid #e0cbe0;
    border-radius: 10px;
    padding: 16px 14px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(224,78,203,0.08);
}
.contact-row {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
}
.contact-phone-block {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 2 1 auto;
}
.phone-icon {
    display: flex;
    align-items: center;
    color: #e04ecb;
}
.phone-details {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
}
.phone-number {
    font-size: 1.35rem;
    font-weight: 700;
    color: #222;
    letter-spacing: 1px;
}
.phone-desc {
    font-size: 1.08rem;
    color: #222;
}
.booking-btn {
    background: #222;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 14px 24px;
    font-size: 1.13rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
    white-space: nowrap;
    flex: 0 0 auto;
}
.booking-btn:hover {
    background: #444;
}

.photos-card {
    background: #fff;
    border: 2px solid #e0cbe0;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 2px 10px rgba(224,78,203,0.08);
    margin-top: 18px;
}
.photos-title {
    font-size: 1.15rem;
    color: #b33e9e;
    font-weight: 600;
    margin-bottom: 10px;
    text-align: center;
}
.photo-grid {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.profile-photo-img {
    width: 100%;
    max-width: 300px;
    border-radius: 10px;
    border: 2px solid #e0cbe0;
    display: block;
    margin: 0 auto;
    box-shadow: 0 2px 8px #e0cbe0;
}

/* ----- MODAL STYLES ----- */
.modal-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-content {
    background: #fff;
    max-width: 550px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 16px;
    padding: 30px 28px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    border: 2px solid #e04ecb;
}
.modal-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #222;
    margin-top: 0;
    margin-bottom: 24px;
    text-align: center;
}
.form-group {
    margin-bottom: 18px;
}
.form-group label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    font-size: 1rem;
}
.form-control {
    width: 100%;
    padding: 12px 14px;
    font-size: 1rem;
    border: 1.5px solid #d6c7d6;
    border-radius: 8px;
    box-sizing: border-box;
    transition: border 0.2s;
}
.form-control:focus {
    outline: none;
    border-color: #e04ecb;
}
.radio-group {
    display: flex;
    gap: 25px;
    align-items: center;
    flex-wrap: wrap;
}
.radio-group label {
    font-weight: 400;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 30px;
}
.submit-btn, .cancel-btn {
    padding: 12px 30px;
    font-size: 1.1rem;
    font-weight: 600;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    transition: 0.2s;
}
.submit-btn {
    background: #222;
    color: #fff;
}
.submit-btn:hover {
    background: #444;
}
.cancel-btn {
    background: #fff;
    color: #222;
    border: 2px solid #222;
}
.cancel-btn:hover {
    background: #f0f0f0;
}

/* ----- responsive adjustments ----- */
@media (max-width: 900px) {
    .profile-main-row {
        flex-direction: column;
        gap: 24px !important;
    }
    .profile-left, .profile-right {
        max-width: 100% !important;
        flex: auto !important;
    }
}
@media (max-width: 600px) {
    .profile-btn-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .profile-btn {
        font-size: 1rem;
    }
    .profile-name {
        font-size: 1.5rem !important;
    }
    .profile-location {
        font-size: 1rem;
    }
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    .contact-icons-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }
    .contact-icons-block {
        align-items: flex-start;
        width: 100%;
    }
    .contact-icons-label {
        width: 100%;
    }
    .profile-photo-img {
        max-width: 100% !important;
        height: auto !important;
    }
    .modal-content {
        padding: 20px 16px;
    }
    .modal-title {
        font-size: 1.5rem;
    }
    .radio-group {
        gap: 15px;
    }
}
@media (max-width: 400px) {
    .profile-name {
        font-size: 1.3rem !important;
    }
    .modal-actions {
        flex-direction: column;
    }
    .submit-btn, .cancel-btn {
        width: 100%;
    }
}
</style>

<script>
// Simple vanilla JS fallback for modal (if jQuery is not available)
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('bookingModal');
    var bookingBtn = document.querySelector('.booking-btn');
    var cancelBtn = document.querySelector('.cancel-btn');

    if (bookingBtn) {
        bookingBtn.addEventListener('click', function() {
            modal.style.display = 'flex';
        });
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    // Close modal if clicked outside content (optional)
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
@endsection
