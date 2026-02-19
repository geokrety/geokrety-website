/**
 * Time Series Chart for displaying growth over time
 *
 * Initialize with:
 * initTimeSeriesChart({
 *   anchor: "#users-chart",
 *   dataUrl: "/api/v1/statistics/users-registrations",
 *   title: "User Registrations",
 *   color: "#5cb85c"
 * });
 */

// Translations
const i18nTimeSeries = {
    missingConfig: "{t}Time series chart: missing required configuration{/t}",
    missingD3: "{t}Time series chart: d3 library not loaded{/t}",
    missingContainer: "{t}Time series chart: container not found{/t}",
    placeholderLoading: "{t}Loading chart...{/t}",
    placeholderNoData: "{t}No data available{/t}",
    placeholderError: "{t}Unable to load data{/t}",
    monthlyLabel: "{t}Monthly{/t}",
    cumulativeLabel: "{t}Cumulative{/t}",
    totalLabel: "{t}Total{/t}",
};

//{literal}
window.initTimeSeriesChart = function(config) {
    const {
        anchor,
        dataUrl,
        title = "",
        color = "#337ab7",
        cacheInfoElement = null,
        formatCacheDuration = null
    } = config;

    if (!anchor || !dataUrl) {
        console.error(i18nTimeSeries.missingConfig, config);
        return;
    }

    function init() {
        if (typeof d3 === "undefined" || !d3) {
            console.error(i18nTimeSeries.missingD3);
            return;
        }

        const container = d3.select(anchor);
        if (container.empty()) {
            console.warn(i18nTimeSeries.missingContainer, anchor);
            return;
        }

        const margin = { top: 20, right: 60, bottom: 60, left: 60 };

        container
            .attr("preserveAspectRatio", "xMidYMid meet")
            .style("overflow", "visible")
            .style("width", "100%")
            .style("height", "100%")
            .style("display", "block");

        const root = container.append("g").attr("class", "time-series-root");
        const placeholderLayer = container.append("g").attr("class", "time-series-placeholder");

        let isRendering = false;

        function computeSize() {
            const node = container.node();
            const parent = node ? (node.parentElement || node) : null;
            const rect = parent ? parent.getBoundingClientRect() : { width: 400, height: 300 };
            return {
                fullW: Math.max(300, Math.floor(rect.width || 400)),
                fullH: Math.max(200, Math.floor(rect.height || 300)),
            };
        }

        function showPlaceholder(message) {
            const { fullW, fullH } = computeSize();
            container.attr("viewBox", `0 0 ${fullW} ${fullH}`);
            root.selectAll("*").remove();
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
                .attr("font-size", 14)
                .text(message);
        }

        function tearDownPlaceholder() {
            placeholderLayer.selectAll("*").remove();
        }

        function render(data) {
            if (isRendering) return;
            isRendering = true;

            tearDownPlaceholder();

            if (!data || data.length === 0) {
                showPlaceholder(i18nTimeSeries.placeholderNoData);
                isRendering = false;
                return;
            }

            const { fullW, fullH } = computeSize();
            const width = fullW - margin.left - margin.right;
            const height = fullH - margin.top - margin.bottom;

            container.attr("viewBox", `0 0 ${fullW} ${fullH}`);
            root.selectAll("*").remove();

            const g = root.append("g")
                .attr("transform", `translate(${margin.left},${margin.top})`);

            // Parse dates
            const parseDate = d3.timeParse("%Y-%m-%d");
            data.forEach(d => {
                d.parsedDate = parseDate(d.date);
                d.count = +d.count;
                d.cumulative = +d.cumulative;
            });

            // Create scales
            const x = d3.scaleTime()
                .domain(d3.extent(data, d => d.parsedDate))
                .range([0, width]);

            const yMax = d3.max(data, d => d.cumulative);
            const y = d3.scaleLinear()
                .domain([0, yMax])
                .nice()
                .range([height, 0]);

            // Create axes
            const xAxis = d3.axisBottom(x)
                .ticks(Math.min(8, Math.floor(width / 80)))
                .tickFormat(d3.timeFormat("%b %Y"));

            const yAxis = d3.axisLeft(y)
                .ticks(6)
                .tickFormat(d3.format(","));

            // Add X axis
            g.append("g")
                .attr("class", "x-axis")
                .attr("transform", `translate(0,${height})`)
                .call(xAxis)
                .selectAll("text")
                .attr("transform", "rotate(-45)")
                .style("text-anchor", "end");

            // Add Y axis
            g.append("g")
                .attr("class", "y-axis")
                .call(yAxis);

            // Add grid lines
            g.append("g")
                .attr("class", "grid")
                .attr("opacity", 0.1)
                .call(d3.axisLeft(y)
                    .ticks(6)
                    .tickSize(-width)
                    .tickFormat("")
                );

            // Create area generator for cumulative
            const area = d3.area()
                .x(d => x(d.parsedDate))
                .y0(height)
                .y1(d => y(d.cumulative))
                .curve(d3.curveMonotoneX);

            // Create line generator for cumulative
            const line = d3.line()
                .x(d => x(d.parsedDate))
                .y(d => y(d.cumulative))
                .curve(d3.curveMonotoneX);

            // Add gradient
            const gradient = g.append("defs")
                .append("linearGradient")
                .attr("id", `gradient-${anchor.replace('#', '')}`)
                .attr("x1", "0%")
                .attr("y1", "0%")
                .attr("x2", "0%")
                .attr("y2", "100%");

            gradient.append("stop")
                .attr("offset", "0%")
                .attr("stop-color", color)
                .attr("stop-opacity", 0.8);

            gradient.append("stop")
                .attr("offset", "100%")
                .attr("stop-color", color)
                .attr("stop-opacity", 0.1);

            // Add area
            g.append("path")
                .datum(data)
                .attr("class", "area")
                .attr("fill", `url(#gradient-${anchor.replace('#', '')})`)
                .attr("d", area);

            // Add line
            g.append("path")
                .datum(data)
                .attr("class", "line")
                .attr("fill", "none")
                .attr("stroke", color)
                .attr("stroke-width", 2)
                .attr("d", line);

            // Add tooltip
            const tooltip = d3.select("body").selectAll(`.tooltip-${anchor.replace('#', '')}`)
                .data([0])
                .join("div")
                .attr("class", `tooltip-${anchor.replace('#', '')}`)
                .style("position", "absolute")
                .style("background", "rgba(0, 0, 0, 0.8)")
                .style("color", "#fff")
                .style("padding", "8px")
                .style("border-radius", "4px")
                .style("font-size", "12px")
                .style("pointer-events", "none")
                .style("opacity", 0)
                .style("z-index", 9999);

            // Add overlay for mouse tracking
            const focus = g.append("g")
                .attr("class", "focus")
                .style("display", "none");

            focus.append("circle")
                .attr("r", 5)
                .attr("fill", color);

            g.append("rect")
                .attr("class", "overlay")
                .attr("width", width)
                .attr("height", height)
                .attr("fill", "none")
                .attr("pointer-events", "all")
                .on("mouseover", function() {
                    focus.style("display", null);
                })
                .on("mouseout", function() {
                    focus.style("display", "none");
                    tooltip.style("opacity", 0);
                })
                .on("mousemove", mousemove);

            function mousemove(event) {
                const [mx] = d3.pointer(event);
                const x0 = x.invert(mx);
                const bisect = d3.bisector(d => d.parsedDate).left;
                const i = bisect(data, x0, 1);
                const d0 = data[i - 1];
                const d1 = data[i];
                const d = d1 && x0 - d0.parsedDate > d1.parsedDate - x0 ? d1 : d0;

                if (d) {
                    focus.attr("transform", `translate(${x(d.parsedDate)},${y(d.cumulative)})`);

                    tooltip
                        .style("opacity", 1)
                        .html(`
                            <strong>${d3.timeFormat("%B %Y")(d.parsedDate)}</strong><br/>
                            ${i18nTimeSeries.monthlyLabel}: ${d.count.toLocaleString()}<br/>
                            ${i18nTimeSeries.totalLabel}: ${d.cumulative.toLocaleString()}
                        `)
                        .style("left", (event.pageX + 10) + "px")
                        .style("top", (event.pageY - 28) + "px");
                }
            }

            isRendering = false;
        }

        // Load and render data
        showPlaceholder(i18nTimeSeries.placeholderLoading);

        fetch(dataUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(responseData => {
                const data = responseData.data || responseData;
                render(data);

                // Display cache info if available
                if (cacheInfoElement && formatCacheDuration && responseData.ttl) {
                    const cacheInfo = formatCacheDuration(responseData.ttl);
                    const element = document.querySelector(cacheInfoElement);
                    if (element) {
                        element.innerHTML = "<i class=\"fa fa-clock-o\"></i> " + cacheInfo;
                    }
                }
            })
            .catch(error => {
                console.error("Error loading time series data:", error);
                showPlaceholder(i18nTimeSeries.placeholderError);
            });

        // Handle resize
        let resizeTimeout;
        window.addEventListener("resize", function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                fetch(dataUrl)
                    .then(response => response.json())
                    .then(responseData => {
                        const data = responseData.data || responseData;
                        render(data);
                    })
                    .catch(error => console.error("Error on resize:", error));
            }, 250);
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
};
//{/literal}
