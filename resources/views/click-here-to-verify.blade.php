@extends('layouts.frontend')

@section('content')
<!-- Main Container -->
<div style="background: #fff; min-height: 100vh;">
    <div class="verify-main-wrapper">
        <h1 class="verify-title">You have to verify your profile photos</h1>
        <div class="verify-important">Important!</div>
        <div class="verify-desc">
            To get your profile verified and listed on Hotescorts we need two photos of you holding a note with the following text:
            <div class="verify-note">your profile name * "Find me on Hotescorts.com.au" + today's date</div>
        </div>
        <div class="verify-steps">
            <div>Photo 1. Hold the piece of paper in your hand</div>
            <div>Photo 2. Scrunch/crinkle the same piece of paper and then hold it in your <a href="#" class="verify-link">other hand</a>, but make sure we can still see some of the text.</div>
        </div>
        <div class="verify-desc">
            We should be able to match both photos with your other profile photos. <a class="verify-link" href="#">So make sure we can see YOU as well in these pics</a>. Show your face or display same tattoos / body parts / hair style / clothes / jewelry / etc.
        </div>
        <div class="verify-desc">
            You can upload your photos here or send your photos to <a class="verify-email" href="mailto:alice@Hotescorts.com.au">alice@Hotescorts.com.au</a>, SMS 0432007091 or message us on <a class="verify-email" href="mailto:BlueSky@Hotescorts.com.au">BlueSky@Hotescorts.com.au</a>.
        </div>
        <div class="verify-desc">
            We will NOT publish these photos on the website. If you have questions or need help let us know.
        </div>
        <div class="verify-warning"><b>If you don't verify we do not display your profile on our website!!!</b></div>
        <div class="verify-desc">
            The quickest way to get your profile online is if you send us your verification photos here:
        </div>
        <div class="verify-btn-row">
            <button class="verify-btn-main" onclick="openModal()">Upload your verification photos</button>
            <button class="verify-btn-back" onclick="history.back()">back</button>
        </div>
        <div class="verify-examples-title">Examples of the text on the note</div>
        <div class="verify-examples-note">JUST REMEMBER: <a class="verify-examples-link" href="#">we need to be able to see you too !</a></div>
        <div class="verify-examples-note">And that you use the new verification text: <span class="verify-note">your profile name * "Find me on Hotescorts.com.au" + today's date</span></div>
        <div class="verify-examples-img-grid">
            <img src="/images/verify-note-example1.jpg" alt="Example 1">
            <img src="/images/verify-note-example2.jpg" alt="Example 2">
            <img src="/images/verify-note-example3.jpg" alt="Example 3">
            <img src="/images/verify-note-example4.jpg" alt="Example 4">
        </div>
    </div>
</div>

<!-- ADVANCED UPLOAD MODAL (from second code) -->
<div id="uploadModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(80, 110, 140, 0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: #fff; width: 540px; max-width: 96vw; border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); position: relative; margin: 48px auto 0 auto;">
        <!-- Modal Header with Tabs and Close -->
        <div style="display: flex; align-items: center; border-bottom: 2px solid #eaeaea; padding: 0 0 0 0;">
            <div id="tab-files" class="modal-tab active" style="flex: 1; text-align: center; padding: 18px 0 10px 0; cursor: pointer; border-bottom: 3px solid #2196f3; color: #2196f3; font-weight: 500; font-size: 1.15rem; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                <span style="font-size: 1.5rem; margin-bottom: 2px;">
                    <svg width="28" height="28" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="6" y="8" width="16" height="12" rx="2" fill="#2196f3" fill-opacity="0.12"/><rect x="6" y="8" width="16" height="12" rx="2" stroke="#2196f3" stroke-width="2"/><rect x="10" y="12" width="8" height="4" rx="1" fill="#2196f3"/></svg>
                </span>
                My Files
            </div>
            <div id="tab-camera" class="modal-tab" style="flex: 1; text-align: center; padding: 18px 0 10px 0; cursor: pointer; border-bottom: 3px solid transparent; color: #888; font-weight: 500; font-size: 1.15rem; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                <span style="font-size: 1.5rem; margin-bottom: 2px;">
                    <svg width="28" height="28" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="14" cy="16" r="5" fill="#2196f3" fill-opacity="0.12"/><circle cx="14" cy="16" r="5" stroke="#2196f3" stroke-width="2"/><rect x="8" y="8" width="12" height="4" rx="2" fill="#2196f3"/></svg>
                </span>
                Camera
            </div>
            <button onclick="closeModal()" style="position: absolute; top: 10px; right: 18px; background: none; border: none; font-size: 2rem; cursor: pointer; color: #888;">&times;</button>
        </div>
        <!-- Modal Body -->
        <div style="padding: 32px 24px 32px 24px; background: #f6f9fc; border-radius: 0 0 10px 10px;">
            <!-- Files Tab Content (default visible) -->
            <div id="files-content">
                <div id="drop-area" style="border: 2px dashed #b0b8c1; border-radius: 10px; background: #e9eef2; padding: 48px 16px 36px 16px; text-align: center;">
                    <div style="margin-bottom: 18px;">
                        <svg width="64" height="48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M32 8c-7.732 0-14 6.268-14 14 0 7.732 6.268 14 14 14s14-6.268 14-14c0-7.732-6.268-14-14-14Zm0 24c-5.523 0-10-4.477-10-10s4.477-10 10-10 10 4.477 10 10-4.477 10-10 10Z" fill="#b0b8c1"/><path d="M32 0v8m0 32v8m24-24h-8m-32 0H0" stroke="#b0b8c1" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div style="font-size: 1.35rem; color: #9ba7b6; margin-bottom: 10px;">Drag and Drop assets here</div>
                    <div style="color: #9ba7b6; margin-bottom: 18px; font-size: 1.1rem;">Or</div>
                    <button id="browseBtn" style="padding: 10px 32px; background: #2196f3; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: 600; color: white; cursor: pointer;">Browse</button>
                    <input type="file" id="fileInput" multiple style="display: none;">
                </div>
            </div>
            <!-- Camera Tab Content (hidden by default) -->
            <div id="camera-content" style="display: none; text-align: center;">
                <video id="camera-preview" autoplay playsinline style="width: 100%; max-height: 300px; border-radius: 8px; background: #ddd;"></video>
                <canvas id="camera-canvas" style="display: none;"></canvas>
                <div style="margin-top: 20px;">
                    <button id="capture-btn" style="padding: 10px 32px; background: #2196f3; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: 600; color: white; cursor: pointer;">Capture</button>
                </div>
                <div id="captured-preview" style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 15px;"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Verification page styles (from first code) */
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #fff;
    color: #222;
    margin: 0;
    padding: 0;
}
.verify-main-wrapper {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px 0 20px;
}
.verify-title {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 18px;
    color: #222;
}
.verify-important {
    font-weight: bold;
    color: #222;
    margin-bottom: 6px;
}
.verify-desc {
    margin-bottom: 10px;
    font-size: 1rem;
    color: #222;
}
.verify-note {
    color: #e91e63;
    font-style: italic;
    font-size: 1.05rem;
    margin-bottom: 10px;
    display: block;
}
.verify-steps {
    margin-bottom: 10px;
    font-size: 1rem;
    color: #222;
}
.verify-link {
    color: #1976d2;
    text-decoration: underline;
    font-size: 1rem;
}
.verify-email {
    color: #1976d2;
    text-decoration: underline;
    word-break: break-all;
}
.verify-warning {
    font-weight: bold;
    color: #e91e63;
    margin: 12px 0 10px 0;
    font-size: 1.08rem;
}
.verify-btn-row {
    margin: 24px 0 0 0;
    display: flex;
    gap: 16px;
}
.verify-btn-main {
    background: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 10px 28px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}
.verify-btn-main:hover {
    background: #0056b3;
}
.verify-btn-back {
    background: #f2f2f2;
    color: #222;
    border: none;
    border-radius: 4px;
    padding: 10px 28px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    margin-left: 0;
    transition: background 0.2s;
}
.verify-btn-back:hover {
    background: #e0e0e0;
}
.verify-examples-title {
    font-size: 1.1rem;
    font-weight: bold;
    margin-top: 32px;
    margin-bottom: 4px;
    color: #222;
}
.verify-examples-note {
    font-size: 0.98rem;
    color: #222;
    margin-bottom: 2px;
}
.verify-examples-link {
    color: #1976d2;
    text-decoration: underline;
    font-size: 0.98rem;
}
.verify-examples-img-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 10px;
    margin-bottom: 30px;
}
.verify-examples-img-grid img {
    width: 100%;
    height: auto;
    border: 1px solid #eee;
    border-radius: 6px;
    display: block;
}
.verify-footer {
    text-align: center;
    color: #888;
    font-size: 0.95rem;
    margin-top: 40px;
    margin-bottom: 10px;
}
@media (max-width: 600px) {
    .verify-main-wrapper {
        padding: 18px 6px 0 6px;
    }
    .verify-title {
        font-size: 1.3rem;
    }
    .verify-btn-row {
        flex-direction: column;
        gap: 10px;
    }
    .verify-btn-main, .verify-btn-back {
        width: 100%;
    }
    .verify-examples-img-grid {
        grid-template-columns: 1fr;
    }
}

/* Modal styles (from second code) */
.modal-tab {
    transition: all 0.2s;
}
#uploadModal {
    display: none; /* hidden by default */
}
#uploadModal > div {
    max-height: 90vh;
    overflow-y: auto;
}
#camera-preview {
    width: 100%;
    background: #ddd;
}
#captured-preview img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #2196f3;
    cursor: pointer;
}
/* Responsive modal */
@media (max-width: 768px) {
    #uploadModal > div {
        width: 100vw !important;
        max-width: 100vw !important;
        height: 100vh !important;
        max-height: 100vh !important;
        border-radius: 0 !important;
        margin: 0 !important;
        display: flex;
        flex-direction: column;
    }
    #uploadModal > div > div:first-of-type {
        padding-top: 20px;
    }
    #uploadModal > div > div:last-child {
        flex: 1;
        overflow-y: auto;
    }
    #camera-preview {
        max-height: 40vh;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('uploadModal');
    const browseBtn = document.getElementById('browseBtn');
    const fileInput = document.getElementById('fileInput');
    const tabFiles = document.getElementById('tab-files');
    const tabCamera = document.getElementById('tab-camera');
    const filesContent = document.getElementById('files-content');
    const cameraContent = document.getElementById('camera-content');
    const dropArea = document.getElementById('drop-area');
    const video = document.getElementById('camera-preview');
    const canvas = document.getElementById('camera-canvas');
    const captureBtn = document.getElementById('capture-btn');
    const capturedPreview = document.getElementById('captured-preview');

    let stream = null; // camera stream

    // Open modal function (called from button onclick)
    window.openModal = function() {
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };

    // Close modal function (used by close button and overlay)
    window.closeModal = function() {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
        // Stop camera if active
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    };

    // Click outside to close (overlay)
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    // Browse files
    if (browseBtn && fileInput) {
        browseBtn.addEventListener('click', function() {
            fileInput.click();
        });
    }

    // Tab switching
    if (tabFiles && tabCamera && filesContent && cameraContent) {
        tabFiles.addEventListener('click', function() {
            // Stop camera if active
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.srcObject = null;
            // UI
            tabFiles.style.borderBottom = '3px solid #2196f3';
            tabFiles.style.color = '#2196f3';
            tabCamera.style.borderBottom = '3px solid transparent';
            tabCamera.style.color = '#888';
            filesContent.style.display = 'block';
            cameraContent.style.display = 'none';
        });

        tabCamera.addEventListener('click', async function() {
            tabCamera.style.borderBottom = '3px solid #2196f3';
            tabCamera.style.color = '#2196f3';
            tabFiles.style.borderBottom = '3px solid transparent';
            tabFiles.style.color = '#888';
            filesContent.style.display = 'none';
            cameraContent.style.display = 'block';

            // Request camera
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
            } catch (err) {
                alert('Camera access denied or not available.');
                console.error(err);
            }
        });
    }

    // Capture photo
    if (captureBtn && video && canvas && capturedPreview) {
        captureBtn.addEventListener('click', function() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            // Convert to data URL
            const dataURL = canvas.toDataURL('image/png');
            // Show thumbnail
            const img = document.createElement('img');
            img.src = dataURL;
            img.style.width = '80px';
            img.style.height = '80px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '6px';
            img.style.border = '2px solid #2196f3';
            img.onclick = () => {
                // In real app, you'd upload this file.
                alert('Photo captured. (In a real app, it would be uploaded.)');
            };
            capturedPreview.appendChild(img);
        });
    }

    // Drag & drop prevention and highlight
    if (dropArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.style.background = '#f0e6ff';
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.style.background = '#e9eef2';
            });
        });
        dropArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                alert('Dropped ' + files.length + ' file(s) (demo)');
            }
        });
    }

    // Handle file input change (demo)
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                alert('Selected ' + this.files.length + ' file(s) (demo)');
            }
        });
    }
});
</script>

<!-- Footer (optional) -->
<div class="verify-footer">
    About us &nbsp; | &nbsp; Links &nbsp; | &nbsp; Terms of use &nbsp; | &nbsp; Privacy &nbsp; | &nbsp; Contact us<br>
    <span style="font-size:0.92em;">this site is restricted to persons 18 years and over</span>
</div>
@endsection
