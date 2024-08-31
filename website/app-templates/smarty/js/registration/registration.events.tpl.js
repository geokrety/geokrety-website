// Display agresive spam filter rule warning banner
const email = document.getElementById("emailInput");
const banner = document.getElementById("banner-registration-spam-filter");
const strictDomains = ["o2.pl", "wp.pl"];

email.addEventListener("input", () => {
    const matches = email.value.match(/.+@(.+)/);
    if (matches) {
        const domain = matches.pop();
        if (strictDomains.indexOf(domain) > -1) {
            banner.style.display = "block";
            return;
        }
    }
    banner.style.display = "none";
});
