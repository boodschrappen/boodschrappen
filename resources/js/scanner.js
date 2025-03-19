// Based on:
// - https://github.com/zxing-cpp/zxing-cpp/blob/master/wrappers/wasm/demo_cam_reader.html
// - https://github.com/Design-The-Box/barcode-field/blob/main/resources/js/barcode-scanner.js

// TODO: Maybe do a cleanup?

import { readBarcodes } from "zxing-wasm/reader";

const readerOptions = {
    tryHarder: true,
    formats: ["EAN-13"],
    maxNumberOfSymbols: 1,
};

let canvas, ctx;

const video = document.createElement("video");
video.autoplay = true;

function escapeTags(htmlStr) {
    return htmlStr
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function drawResult(code) {
    ctx.beginPath();
    ctx.lineWidth = 4;
    ctx.strokeStyle = "red";
    ctx.moveTo(code.position.topLeft.x, code.position.topLeft.y);
    ctx.lineTo(code.position.topRight.x, code.position.topRight.y);
    ctx.stroke();
}

/**
 * Read from image data
 */
const processFrame = async function () {
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

    const [code] = await readBarcodes(imageData, readerOptions);

    if (code?.text) {
        Livewire.navigate("/products?tableSearch=" + escapeTags(code.text));
        drawResult(code);
        return;
    }

    requestAnimationFrame(processFrame);
};

function startScanner() {
    canvas = document.getElementById("canvas");
    ctx = canvas.getContext("2d", { willReadFrequently: true });

    // To ensure the camera switch, it is advisable to free up the media resources
    if (video.srcObject) {
        video.srcObject.getTracks().forEach((track) => track.stop());
    }

    navigator.mediaDevices
        .getUserMedia({ video: { facingMode: "environment" }, audio: false })
        .then(function (stream) {
            let settings = stream.getVideoTracks()[0].getSettings();

            video.width = canvas.width = settings.width;
            video.height = canvas.height = settings.height;

            video.srcObject = stream;
            video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
            video.play();

            processFrame();
        })
        .catch(function (error) {
            console.error("Error accessing camera:", error);
        });
}

function stopScanning() {
    if (video.srcObject) {
        video.srcObject.getTracks().forEach((track) => track.stop());
    }
    video.style.display = "none";
}

function startCamera() {
    startScanner();
}

// Listen for modal opening and start camera
window.addEventListener("open-modal", (event) => {
    console.debug("Modal opened, starting camera");
    requestAnimationFrame(startCamera);
});

// Listen for modal closing and stop camera
window.addEventListener("close-modal", (event) => {
    console.debug("Modal closed, stopping camera");
    stopScanning();
});
