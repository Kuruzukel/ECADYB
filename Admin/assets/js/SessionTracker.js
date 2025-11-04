// Session tracker - Sends heartbeat to server every 60 seconds
(function () {
    const BASE_URL =
        window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
            ? "/ECADYB/"
            : "/";

    // Send heartbeat ping every 60 seconds
    function sendSessionPing() {
        fetch(BASE_URL + "Connection/Session/TrackSession.php?action=ping", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    // Only log to console in debug mode - suppress "No active session" warnings
                    if (data.message !== 'No active session') {
                        console.warn("[SessionTracker] Admin heartbeat failed:", data.message);
                    }
                }
            })
            .catch((error) => {
                // Suppress network errors during session tracking
                // These are non-critical and shouldn't pollute the console
            });
    }

    // Send initial ping
    sendSessionPing();

    // Send heartbeat every 60 seconds
    setInterval(sendSessionPing, 60000);

    // Send logout ping when user leaves
    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon(
            BASE_URL + "Connection/Session/TrackSession.php?action=logout"
        );
    });

    console.log("[SessionTracker] Admin session tracking enabled");
})();
