import { readBarcodes } from "zxing-wasm/reader";

const readerOptions = {
    tryHarder: true,
    formats: ["EAN-13"],
    maxNumberOfSymbols: 1,
};

const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d", { willReadFrequently: true });

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
    ctx.lineTo(code.position.bottomRight.x, code.position.bottomRight.y);
    ctx.lineTo(code.position.bottomLeft.x, code.position.bottomLeft.y);
    ctx.lineTo(code.position.topLeft.x, code.position.topLeft.y);
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
        document.getElementById("result").value = escapeTags(code.text);
        drawResult(code);
    }

    requestAnimationFrame(processFrame);
};

function openScannerModal() {
    // Open the Filament modal
    window.dispatchEvent(
        new CustomEvent("open-modal", {
            detail: { id: "barcode-scanner-modal" },
        })
    );
}

function closeScannerModal() {
    // Close the Filament modal
    window.dispatchEvent(
        new CustomEvent("close-modal", {
            detail: { id: "barcode-scanner-modal" },
        })
    );
    stopScanning(); // Make sure to stop the camera when the modal closes
}

function startScanner() {
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
    if (event.detail.id === "barcode-scanner-modal") {
        console.log("Modal opened, starting camera");
        startCamera();
    }
});

// Listen for modal closing and stop camera
window.addEventListener("close-modal", (event) => {
    if (event.detail.id === "barcode-scanner-modal") {
        console.log("Modal closed, stopping camera");
        stopScanning();
    }
});
