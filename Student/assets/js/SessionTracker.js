(function () {
  const BASE_URL =
    window.location.hostname === "localhost" ||
    window.location.hostname === "127.0.0.1"
      ? "/ECADYB/"
      : "/";

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
          console.warn("[SessionTracker] Heartbeat failed:", data.message);
        }
      })
      .catch((error) => {
        console.error("[SessionTracker] Error sending heartbeat:", error);
      });
  }

  sendSessionPing();

  setInterval(sendSessionPing, 60000);

  window.addEventListener("beforeunload", function () {
    navigator.sendBeacon(
      BASE_URL + "Connection/Session/TrackSession.php?action=logout"
    );
  });

  console.log("[SessionTracker] Active session tracking enabled");
})();
