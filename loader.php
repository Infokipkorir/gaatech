<!-- loader.php -->
<style>
#loaderPage {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    transition: opacity 0.4s ease, visibility 0.4s ease;
}
#loaderPage.fadeOut {
    opacity: 0;
    visibility: hidden;
}
#loaderLogo {
    width: 90px;
    margin-bottom: 15px;
    animation: pulse 1.3s infinite;
}
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<div id="loaderPage">
    <img src="admin/assets/Gaatech logo2.jpg" alt="Gaatech Logo" id="loaderLogo">
    <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
    <p style="margin-top:10px;color:#2563eb;font-weight:bold;">Loading...</p>
</div>

<script>
function hideLoader() {
    document.getElementById("loaderPage").classList.add("fadeOut");
}

// ✅ Show loader only on full reload (F5, Ctrl+R, or first page load)
window.addEventListener("load", () => {
    setTimeout(hideLoader, 700); // Hide quickly after load
});

// ✅ Detect network change
window.addEventListener("offline", () => {
    document.getElementById("loaderPage").classList.remove("fadeOut");
    document.querySelector("#loaderPage p").textContent = "No Internet Connection...";
});
window.addEventListener("online", () => {
    document.querySelector("#loaderPage p").textContent = "Reconnecting...";
    setTimeout(hideLoader, 1000);
});
</script>
