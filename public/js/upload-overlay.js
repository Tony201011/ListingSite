/**
 * Global upload progress overlay.
 *
 * Listens for custom window events dispatched by any upload component:
 *   upload:start   – increment counter, show overlay
 *   upload:end     – decrement counter, hide overlay when counter reaches 0
 *   upload:progress – optional { detail: { percent: 0-100 } } to show progress
 *
 * While the overlay is visible the browser's beforeunload dialog is also
 * triggered to prevent accidental navigation away mid-upload.
 */
(function () {
    'use strict';

    var activeUploads = 0;
    var overlay = null;
    var progressBar = null;
    var progressText = null;

    function getOverlay() {
        return document.getElementById('upload-progress-overlay');
    }

    function showOverlay() {
        var el = getOverlay();
        if (el) {
            el.style.display = 'flex';
        }
    }

    function hideOverlay() {
        var el = getOverlay();
        if (el) {
            el.style.display = 'none';
            resetProgress();
        }
    }

    function resetProgress() {
        var bar = document.getElementById('upload-progress-bar');
        var pct = document.getElementById('upload-progress-pct');
        if (bar) bar.style.width = '0%';
        if (pct) pct.textContent = '';
    }

    function setProgress(percent) {
        var bar = document.getElementById('upload-progress-bar');
        var pct = document.getElementById('upload-progress-pct');
        var clamped = Math.max(0, Math.min(100, percent));
        if (bar) bar.style.width = clamped + '%';
        if (pct) pct.textContent = Math.round(clamped) + '%';
    }

    function beforeUnloadHandler(e) {
        e.preventDefault();
        e.returnValue = '';
    }

    window.addEventListener('upload:start', function () {
        activeUploads++;
        if (activeUploads === 1) {
            resetProgress();
            showOverlay();
            window.addEventListener('beforeunload', beforeUnloadHandler);
        }
    });

    window.addEventListener('upload:end', function () {
        if (activeUploads > 0) {
            activeUploads--;
        }
        if (activeUploads === 0) {
            hideOverlay();
            window.removeEventListener('beforeunload', beforeUnloadHandler);
        }
    });

    window.addEventListener('upload:progress', function (e) {
        if (e.detail && typeof e.detail.percent === 'number') {
            setProgress(e.detail.percent);
        }
    });
}());
