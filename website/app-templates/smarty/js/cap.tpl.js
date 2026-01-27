// console.log("CAP: Starting CAP challenge process...");
// $.get('/cap/ping').done((data) => {
//     console.log("CAP: /cap/ping done.");
// }).fail(() => {
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
    //   console.log("✓ CAP challenge solved, token received:", solution.token);
      document.cookie = `geokrety_cap=${ encodeURIComponent(solution.token) }; Path=/; Max-Age=30; SameSite=Lax; Secure`;

      $.get("/cap/pong");
    //   $.get("/cap/pong")
    //     .done((data) => {
    //       console.log("CAP: /cap/pong done.");
    //       return;
    //     })
    //     .fail(() => {
    //       console.warn("CAP: /cap/pong failed.");
    //     });
    } catch (error) {
      console.error("CAP: solve() failed", error);
    }
  })();
});
