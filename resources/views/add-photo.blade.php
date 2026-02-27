@extends('layouts.frontend')

@section('content')
<!-- Main Content - Add Photos Page -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">

        <button onclick="window.history.back()" style="background: #cfa1b8; color: white; border: none; border-radius: 8px; padding: 6px 18px; font-size: 1rem; font-weight: 500; margin-bottom: 30px; cursor: pointer;">&lt; back to profile</button>

        <!-- Page Title -->
        <h1 style="font-size: 2.8rem; font-weight: 400; color: #444; margin-bottom: 10px;">Add photos to your profile</h1>

         <!-- Description - now left-aligned -->
        <div style="max-width: 520px; margin-bottom: 40px;">
            <p style="font-size: 1.2rem; color: #444; line-height: 1.5;">
                You can add photos to your profile from computer files, your webcam and even directly from your Instagram account. Multiple files in one upload is possible, you can upload an unlimited amount of photos.
            </p>
        </div>

        <!-- Upload button / Click to add photos - now left-aligned -->
        <div style="margin-top: 10px;">
            <div class="photo-btn-row">
                <button id="openUploadModalBtn" class="photo-main-btn">Click to add photos</button>
                <a href="#" class="photo-secondary-btn">Continue setting up your profile</a>
            </div>
        </div>

        <!-- MODAL - Upload Interface -->
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
                    Continue setting up your profile
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const openBtn = document.getElementById('openUploadModalBtn');
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

    // Open modal
    if (openBtn && modal) {
        openBtn.addEventListener('click', function() {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    }

    // Close modal function
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

    // Click outside to close
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
    if (captureBtn && video && canvas) {
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

<style>
/* Add photo page button row */
.photo-btn-row {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 12px;
    margin-top: 0.5rem;
    margin-bottom: 1.5rem;
}
.photo-main-btn {
    padding: 10px 24px;
    background: #e04ecb;
    border: none;
    border-radius: 8px;
    font-size: 1.15rem;
    font-weight: 400;
    color: white;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(224,78,203,0.18);
    transition: all 0.3s;
    outline: none;
    min-width: 180px;
}
.photo-secondary-btn {
    padding: 10px 24px;
    background: #fff;
    border: 2px solid #e04ecb;
    border-radius: 8px;
    font-size: 1.15rem;
    font-weight: 400;
    color: #b3aeb5;
    cursor: pointer;
    min-width: 180px;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s;
    outline: none;
    box-sizing: border-box;
}
.photo-main-btn:hover, .photo-secondary-btn:hover {
    opacity: 0.92;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(224,78,203,0.22);
}

@media (max-width: 600px) {
    .photo-btn-row {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    .photo-main-btn, .photo-secondary-btn {
        width: 100%;
        min-width: 0;
        font-size: 1rem;
        padding: 10px 8px;
    }
}
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

    p {
        font-size: 0.95rem !important;
    }

    button#openUploadModalBtn {
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

/* Small phones */
@media (max-width: 480px) {
    h1 {
        font-size: 1.5rem !important;
    }
}
</style>
@endsection
