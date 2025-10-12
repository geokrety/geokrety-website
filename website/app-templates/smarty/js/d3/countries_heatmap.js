/**
 * Countries Heatmap Chart
 *
 * Initialize with:
 * initCountriesHeatmap({
 *   anchor: "#countries-map-chart",
 *   dataUrl: "/api/v1/geokrety/123/statistics/countries",
 *   worldUrl: "https://cdn.geokrety.org/libraries/world-atlas/countries-110m.json",
 *   topojsonUrl: "https://cdn.geokrety.org/libraries/topojson-client/topojson-client.min.js"
 * });
 */

// Translations
const i18nCountries = {
    missingConfig: "{t}Countries heatmap: missing required configuration{/t}",
    missingD3: "{t}Countries heatmap: d3 library not loaded{/t}",
    missingContainer: "{t}Countries heatmap: container not found{/t}",
    ariaLabel: "{t}World map showing GeoKrety moves per country{/t}",
    placeholderLoading: "{t}Loading world map...{/t}",
    placeholderNoData: "{t}No country data yet{/t}",
    placeholderNoGeometry: "{t}World geometry unavailable{/t}",
    placeholderStatsError: "{t}Unable to load country statistics{/t}",
    noMoves: "{t}no recorded moves{/t}",
    unknownCountry: "{t}Unknown country{/t}",
    unknownLastActivity: "{t}unknown{/t}",
    legendTitle: "{t}Moves per country{/t}",
    movesLabel: "{t}Moves{/t}",
    participantsLabel: "{t}Participants{/t}",
    lastActivity: "{t}Last activity{/t}",
};

//{literal}
window.initCountriesHeatmap = function(config) {
    const {
        anchor = "#countries-map-chart",
        dataUrl,
        worldUrl,
        topojsonUrl
    } = config;

    if (!dataUrl || !worldUrl || !topojsonUrl) {
        console.error(i18nCountries.missingConfig, config);
        return;
    }

    function init() {
        if (typeof d3 === "undefined" || !d3) {
            console.error(i18nCountries.missingD3);
            return;
        }

        const container = d3.select(anchor);
        if (container.empty()) {
            console.warn(i18nCountries.missingContainer, anchor);
            return;
        }

        const margin = { top: 12, right: 12, bottom: 48, left: 12 };
        const projection = d3.geoNaturalEarth1();
        const path = d3.geoPath(projection);
        const colorInterpolator = d3.interpolateRgbBasis(["#b8d4e8", "#7db8da", "#114f8e"]);

        container
            .attr("role", "img")
            .attr("aria-label", i18nCountries.ariaLabel)
            .attr("preserveAspectRatio", "xMidYMid meet")
            .style("overflow", "visible")
            .style("width", "100%")
            .style("height", "100%")
            .style("display", "block");

        const defs = container.append("defs");
        const root = container.append("g").attr("class", "countries-map-root");
        const legend = container.append("g").attr("class", "countries-map-legend");
        const placeholderLayer = container.append("g").attr("class", "countries-map-placeholder");

        let fallbackResizeAttached = false;
        let featureCollection = null;
        let colorScale = null;
        let dataByCode = new Map();
        let isRendering = false;
        let resizeTimeout = null;
        let lastRenderDimensions = null;

        const numberFmt = d3.format(",");

        function computeSize() {
            const node = container.node();
            const parent = node ? (node.parentElement || node) : null;
            const rect = parent ? parent.getBoundingClientRect() : { width: 260, height: 200 };
            return {
                fullW: Math.max(260, Math.floor(rect.width || 260)),
                fullH: Math.max(200, Math.floor(rect.height || 200)),
            };
        }

        function showPlaceholder(message) {
            const { fullW, fullH } = computeSize();
            container.attr("viewBox", `0 0 ${fullW} ${fullH}`);
            root.selectAll("*").remove();
            legend.selectAll("*").remove();
            placeholderLayer.selectAll("*").remove();

            const padding = 12;
            placeholderLayer.append("rect")
                .attr("x", padding)
                .attr("y", padding)
                .attr("width", Math.max(0, fullW - padding * 2))
                .attr("height", Math.max(0, fullH - padding * 2))
                .attr("fill", "none")
                .attr("stroke", "#d9dfe3")
                .attr("stroke-dasharray", "6,6")
                .attr("rx", 9)
                .attr("ry", 9);

            placeholderLayer.append("text")
                .attr("x", fullW / 2)
                .attr("y", fullH / 2)
                .attr("text-anchor", "middle")
                .attr("dominant-baseline", "middle")
                .attr("fill", "#7a869a")
                .attr("font-size", Math.max(11, Math.min(16, Math.floor(Math.min(fullW, fullH) / 12))))
                .text(message);

            // Removed ResizeObserver for placeholder to prevent infinite loop
            // The placeholder is temporary and doesn't need to be responsive
        }

        function tearDownPlaceholder() {
            placeholderLayer.selectAll("*").remove();
        }


        // ISO 3166-1 numeric to alpha-2 mapping for common countries
        const numericToAlpha2 = {
            "4": "AF", "8": "AL", "12": "DZ", "16": "AS", "20": "AD", "24": "AO", "28": "AG", "31": "AZ",
            "32": "AR", "36": "AU", "40": "AT", "44": "BS", "48": "BH", "50": "BD", "51": "AM", "52": "BB",
            "56": "BE", "60": "BM", "64": "BT", "68": "BO", "70": "BA", "72": "BW", "76": "BR", "84": "BZ",
            "90": "SB", "92": "VG", "96": "BN", "100": "BG", "104": "MM", "108": "BI", "112": "BY", "116": "KH",
            "120": "CM", "124": "CA", "132": "CV", "136": "KY", "140": "CF", "144": "LK", "148": "TD", "152": "CL",
            "156": "CN", "158": "TW", "170": "CO", "174": "KM", "178": "CG", "180": "CD", "184": "CK", "188": "CR",
            "191": "HR", "192": "CU", "196": "CY", "203": "CZ", "204": "BJ", "208": "DK", "212": "DM", "214": "DO",
            "218": "EC", "222": "SV", "226": "GQ", "231": "ET", "232": "ER", "233": "EE", "234": "FO", "238": "FK",
            "242": "FJ", "246": "FI", "250": "FR", "254": "GF", "258": "PF", "262": "DJ", "266": "GA", "268": "GE",
            "270": "GM", "275": "PS", "276": "DE", "288": "GH", "292": "GI", "296": "KI", "300": "GR", "304": "GL",
            "308": "GD", "312": "GP", "316": "GU", "320": "GT", "324": "GN", "328": "GY", "332": "HT", "336": "VA",
            "340": "HN", "344": "HK", "348": "HU", "352": "IS", "356": "IN", "360": "ID", "364": "IR", "368": "IQ",
            "372": "IE", "376": "IL", "380": "IT", "384": "CI", "388": "JM", "392": "JP", "398": "KZ", "400": "JO",
            "404": "KE", "408": "KP", "410": "KR", "414": "KW", "417": "KG", "418": "LA", "422": "LB", "426": "LS",
            "428": "LV", "430": "LR", "434": "LY", "438": "LI", "440": "LT", "442": "LU", "446": "MO", "450": "MG",
            "454": "MW", "458": "MY", "462": "MV", "466": "ML", "470": "MT", "474": "MQ", "478": "MR", "480": "MU",
            "484": "MX", "492": "MC", "496": "MN", "498": "MD", "499": "ME", "500": "MS", "504": "MA", "508": "MZ",
            "512": "OM", "516": "NA", "520": "NR", "524": "NP", "528": "NL", "531": "CW", "533": "AW", "534": "SX",
            "535": "BQ", "540": "NC", "548": "VU", "554": "NZ", "558": "NI", "562": "NE", "566": "NG", "570": "NU",
            "574": "NF", "578": "NO", "580": "MP", "581": "UM", "583": "FM", "584": "MH", "585": "PW", "586": "PK",
            "591": "PA", "598": "PG", "600": "PY", "604": "PE", "608": "PH", "612": "PN", "616": "PL", "620": "PT",
            "624": "GW", "626": "TL", "630": "PR", "634": "QA", "638": "RE", "642": "RO", "643": "RU", "646": "RW",
            "652": "BL", "654": "SH", "659": "KN", "660": "AI", "662": "LC", "663": "MF", "666": "PM", "670": "VC",
            "674": "SM", "678": "ST", "682": "SA", "686": "SN", "688": "RS", "690": "SC", "694": "SL", "702": "SG",
            "703": "SK", "704": "VN", "705": "SI", "706": "SO", "710": "ZA", "716": "ZW", "724": "ES", "728": "SS",
            "729": "SD", "732": "EH", "740": "SR", "744": "SJ", "748": "SZ", "752": "SE", "756": "CH", "760": "SY",
            "762": "TJ", "764": "TH", "768": "TG", "772": "TK", "776": "TO", "780": "TT", "784": "AE", "788": "TN",
            "792": "TR", "795": "TM", "796": "TC", "798": "TV", "800": "UG", "804": "UA", "807": "MK", "818": "EG",
            "826": "GB", "831": "GG", "832": "JE", "833": "IM", "834": "TZ", "840": "US", "850": "VI", "854": "BF",
            "858": "UY", "860": "UZ", "862": "VE", "876": "WF", "882": "WS", "887": "YE", "894": "ZM"
        };

        function resolveFeatureCode(feature) {
            if (!feature) {
                return null;
            }
            if (feature.__countryCode) {
                return feature.__countryCode;
            }
            const props = feature.properties || {};
            const candidates = [
                feature.id,
                props.iso_a2, props.ISO_A2,
                props.wb_a2, props.WB_A2,
                props.postal, props.postalAbbr,
                props.cca2, props.CCA2,
            ];
            for (const candidate of candidates) {
                // Check for 2-letter ISO code
                if (typeof candidate === "string" && candidate.length === 2) {
                    feature.__countryCode = candidate.toUpperCase();
                    return feature.__countryCode;
                }
                // Check for numeric ISO 3166-1 code
                if (typeof candidate === "string" && numericToAlpha2[candidate]) {
                    feature.__countryCode = numericToAlpha2[candidate];
                    return feature.__countryCode;
                }
            }
            return null;
        }

        function loadTopojsonClient() {
            if (typeof topojson !== "undefined") {
                return Promise.resolve(topojson);
            }
            return new Promise((resolve, reject) => {
                const script = document.createElement("script");
                script.async = true;
                script.src = topojsonUrl;
                script.onload = () => {
                    if (typeof topojson !== "undefined") {
                        resolve(topojson);
                    } else {
                        reject(new Error("TopoJSON client failed to initialise"));
                    }
                };
                script.onerror = () => reject(new Error("Unable to load TopoJSON client"));
                document.head.appendChild(script);
            });
        }

        function updateLegend(fullW, fullH) {
            if (!colorScale) {
                legend.selectAll("*").remove();
                return;
            }

            const legendWidth = Math.min(240, Math.max(160, fullW - margin.left - margin.right));
            const legendHeight = 12;
            const domain = colorScale.domain();
            const gradientId = "countriesHeatmapGradient";

            const stops = d3.range(0, 1.00001, 0.2).map((t, i, arr) => ({
                offset: arr.length <= 1 ? 0 : t,
                color: colorScale(domain[0] + t * (domain[1] - domain[0])),
            }));

            const gradient = defs.selectAll(`#${gradientId}`).data([0]);
            gradient.enter()
                .append("linearGradient")
                .attr("id", gradientId)
                .attr("x1", "0%")
                .attr("x2", "100%")
                .attr("y1", "0%")
                .attr("y2", "0%");

            defs.select(`#${gradientId}`)
                .selectAll("stop")
                .data(stops)
                .join("stop")
                .attr("offset", (d) => d.offset)
                .attr("stop-color", (d) => d.color);

            legend.selectAll("*").remove();

            const legendGroup = legend
                .attr("transform", `translate(${margin.left}, ${fullH - margin.bottom + 16})`);

            legendGroup.append("rect")
                .attr("width", legendWidth)
                .attr("height", legendHeight)
                .attr("rx", 4)
                .attr("ry", 4)
                .attr("fill", `url(#${gradientId})`)
                .attr("stroke", "#e1e6eb")
                .attr("stroke-width", 0.6);

            const legendScale = d3.scaleLinear().domain(domain).range([0, legendWidth]);
            const ticksCount = Math.min(5, Math.max(3, Math.floor(legendWidth / 60)));
            const axis = d3.axisBottom(legendScale).ticks(ticksCount).tickFormat(d3.format("~s"));

            legendGroup.append("g")
                .attr("class", "legend-axis")
                .attr("transform", `translate(0, ${legendHeight})`)
                .call(axis)
                .call((g) => g.select(".domain").remove())
                .call((g) => g.selectAll("line").attr("stroke", "#c2ccd6").attr("stroke-opacity", 0.8));

            legendGroup.append("text")
                .attr("x", legendWidth / 2)
                .attr("y", legendHeight + 22)
                .attr("text-anchor", "middle")
                .attr("fill", "#516373")
                .attr("font-size", 11)
                .text(i18nCountries.legendTitle);
        }

        function render() {
            if (isRendering) {
                return; // Prevent re-entrant calls
            }
            if (!featureCollection || !featureCollection.features.length) {
                return;
            }

            isRendering = true;
            const { fullW, fullH } = computeSize();
            lastRenderDimensions = `${fullW},${fullH}`;
            const width = Math.max(160, fullW - margin.left - margin.right);
            const height = Math.max(140, fullH - margin.top - margin.bottom);

            container.attr("viewBox", `0 0 ${fullW} ${fullH}`);
            root.attr("transform", `translate(${margin.left}, ${margin.top})`);

            projection.fitSize([width, height], featureCollection);

            const countries = root.selectAll("path.country")
                .data(featureCollection.features);

            countries.exit().remove();

            const countriesEnter = countries.enter()
                .append("path")
                .attr("class", "country")
                .attr("tabindex", 0);

            const countriesMerged = countriesEnter.merge(countries);

            countriesMerged
                .attr("d", path)
                .attr("fill", (feature) => {
                    const code = resolveFeatureCode(feature);
                    const entry = code ? dataByCode.get(code) : null;
                    return entry && entry.move_count > 0 ? colorScale(entry.move_count) : "#f5f8fa";
                })
                .attr("stroke", "#cdd7e0")
                .attr("stroke-width", 0.5)
                .attr("vector-effect", "non-scaling-stroke")
                .attr("data-country-code", (feature) => resolveFeatureCode(feature) || "")
                .attr("aria-label", (feature) => {
                    const code = resolveFeatureCode(feature);
                    const entry = code ? dataByCode.get(code) : null;
                    const name = feature.properties?.name || code || i18nCountries.unknownCountry;
                    if (!entry) {
                        return `${name}: ${i18nCountries.noMoves}`;
                    }
                    let lastSeen = i18nCountries.unknownLastActivity;
                    if (entry.last_moved_on_datetime) {
                        const dt = new Date(entry.last_moved_on_datetime);
                        if (!Number.isNaN(dt.getTime())) {
                            lastSeen = dt.toLocaleString();
                        }
                    }
                    return `${name}: ${i18nCountries.movesLabel} ${entry.move_count}, ${i18nCountries.participantsLabel} ${entry.mover_count}, ${i18nCountries.lastActivity} ${lastSeen}`;
                })
                .on("focus", function () {
                    d3.select(this).attr("stroke", "#3b6a96").attr("stroke-width", 1.1);
                })
                .on("blur", function () {
                    d3.select(this).attr("stroke", "#cdd7e0").attr("stroke-width", 0.5);
                })
                .on("mouseenter", function () {
                    d3.select(this).attr("stroke", "#3b6a96").attr("stroke-width", 1.1);
                })
                .on("mouseleave", function () {
                    d3.select(this).attr("stroke", "#cdd7e0").attr("stroke-width", 0.5);
                });

            countriesMerged.selectAll("title").remove();
            countriesMerged.append("title").text((feature) => {
                const code = resolveFeatureCode(feature);
                const entry = code ? dataByCode.get(code) : null;
                const name = feature.properties?.name || code || i18nCountries.unknownCountry;
                let lastSeen = i18nCountries.unknownLastActivity;
                if (entry && entry.last_moved_on_datetime) {
                    const dt = new Date(entry.last_moved_on_datetime);
                    if (!Number.isNaN(dt.getTime())) {
                        lastSeen = dt.toLocaleString();
                    }
                }
                const movesLine = entry ? `${i18nCountries.movesLabel}: ${numberFmt(entry.move_count)}` : `${i18nCountries.movesLabel}: 0`;
                const participantsLine = entry ? `${i18nCountries.participantsLabel}: ${numberFmt(entry.mover_count)}` : `${i18nCountries.participantsLabel}: 0`;
                return [
                    `${name}${code ? ` (${code})` : ""}`,
                    movesLine,
                    participantsLine,
                    `${i18nCountries.lastActivity}: ${lastSeen}`,
                ].join("\n");
            });

            updateLegend(fullW, fullH);
            isRendering = false;
        }

        function debouncedRender() {
            if (resizeTimeout) {
                clearTimeout(resizeTimeout);
            }
            resizeTimeout = setTimeout(() => {
                const { fullW, fullH } = computeSize();
                const dimensionKey = `${fullW},${fullH}`;
                // Only render if dimensions actually changed
                if (dimensionKey !== lastRenderDimensions) {
                    lastRenderDimensions = dimensionKey;
                    render();
                }
            }, 150);
        }


        function ensureResizeObserver() {
            // Only use window resize, not ResizeObserver
            // ResizeObserver causes infinite loops when SVG changes trigger parent resize
            if (featureCollection && !fallbackResizeAttached) {
                fallbackResizeAttached = true;
                window.addEventListener("resize", debouncedRender, { passive: true });
            }
        }

        function normaliseData(rawStats) {
            dataByCode = new Map();
            (rawStats || []).forEach((entry) => {
                if (!entry || !entry.country) {
                    return;
                }
                const code = String(entry.country).toUpperCase();
                dataByCode.set(code, {
                    country: code,
                    move_count: Number(entry.move_count) || 0,
                    mover_count: Number(entry.mover_count) || 0,
                    last_moved_on_datetime: entry.last_moved_on_datetime || null,
                });
            });

            const counts = Array.from(dataByCode.values())
                .map((d) => d.move_count)
                .filter((value) => Number.isFinite(value) && value > 0);

            if (!counts.length) {
                showPlaceholder(i18nCountries.placeholderNoData);
                return false;
            }

            const maxCount = Math.max(1, d3.max(counts) || 1);
            colorScale = d3.scaleSequentialSqrt(colorInterpolator).domain([1, maxCount]);
            return true;
        }

        showPlaceholder(i18nCountries.placeholderLoading);

        Promise.all([
            d3.json(dataUrl).catch(() => null),
            d3.json(worldUrl).catch(() => null),
        ]).then(async ([statsRaw, worldRaw]) => {
            const stats = Array.isArray(statsRaw) ? statsRaw : [];
            if (!normaliseData(stats)) {
                return;
            }

            let features = [];

            if (worldRaw) {
                if (worldRaw.type === "Topology") {
                    const topo = await loadTopojsonClient().catch(() => null);
                    if (!topo) {
                        showPlaceholder(i18nCountries.placeholderNoGeometry);
                        return;
                    }
                    const objects = worldRaw.objects || {};
                    const objectKey = objects.countries ? "countries" : Object.keys(objects)[0];
                    if (objectKey) {
                        features = topo.feature(worldRaw, objects[objectKey]).features || [];
                    }
                } else if (Array.isArray(worldRaw.features)) {
                    features = worldRaw.features;
                }
            }

            if (!features.length) {
                showPlaceholder(i18nCountries.placeholderNoGeometry);
                return;
            }

            features.forEach((feature) => {
                resolveFeatureCode(feature);
            });

            featureCollection = { type: "FeatureCollection", features };
            tearDownPlaceholder();
            render();
            ensureResizeObserver();
        }).catch(() => {
            showPlaceholder(i18nCountries.placeholderStatsError);
        });
    }

    if (document.readyState !== "loading") {
        init();
    } else {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    }
};
//{/literal}
