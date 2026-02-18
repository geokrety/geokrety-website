$.get('/cap/ping').fail(() => {
  (async () => {
    window.CAP_CUSTOM_WASM_URL = window.location.protocol + "//" + window.location.host + "/cap/assets/cap_wasm.js";
    if (typeof Cap === "undefined") {
      console.error("CAP: Cap is not defined. Ensure the Cap library is loaded before cap.tpl.js.");
      return;
    }

    const cap = new Cap({
      apiEndpoint: "/cap/{{GK_CAP_SITE_ID}}/",
    });

    try {
      const solution = await cap.solve();
      document.cookie = `geokrety_cap=${ encodeURIComponent(solution.token) }; Path=/; Max-Age=30; SameSite=Lax; Secure`;
      $.get("/cap/pong");
    } catch (error) {
      console.error("CAP: solve() failed", error);
    }
  })();
});
