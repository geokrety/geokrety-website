/**
 * Stacked Area/Bar Chart for displaying multi-series time data
 *
 * Initialize with:
 * initStackedChart({
 *   anchor: "#chart",
 *   dataUrl: "/api/v1/statistics/picture-trends",
 *   colorScheme: d3.schemeCategory10,
 *   cacheInfoElement: "#cache-info",
 *   formatCacheDuration: formatCacheDuration
 * });
 */

// Translations
const i18nStackedChart = {
    missingConfig: "{t}Stacked chart: missing required configuration{/t}",
    missingD3: "{t}Stacked chart: d3 library not loaded{/t}",
    missingContainer: "{t}Stacked chart: container not found{/t}",
    placeholderLoading: "{t}Loading chart...{/t}",
    placeholderNoData: "{t}No data available{/t}",
    placeholderError: "{t}Unable to load data{/t}",
};

//{literal}
window.initStackedChart = function(config) {
    const {
        anchor,
        dataUrl,
        colorScheme = d3.schemeCategory10,
        cacheInfoElement = null,
        formatCacheDuration = null
    } = config;

    if (!anchor || !dataUrl) {
        console.error(i18nStackedChart.missingConfig, config);
        return;
    }

    function init() {
        if (typeof d3 === "undefined" || !d3) {
            console.error(i18nStackedChart.missingD3);
            return;
        }

        const container = d3.select(anchor);
        if (container.empty()) {
            console.warn(i18nStackedChart.missingContainer, anchor);
            return;
        }

        const margin = { top: 20, right: 120, bottom: 60, left: 60 };

        container
            .attr("preserveAspectRatio", "xMidYMid meet")
            .style("overflow", "visible")
            .style("width", "100%")
            .style("height", "100%")
            .style("display", "block");

        const root = container.append("g").attr("class", "stacked-root");
        const placeholderLayer = container.append("g").attr("class", "stacked-placeholder");

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

        function render(rawData) {
            tearDownPlaceholder();

            if (!rawData || rawData.length === 0) {
                showPlaceholder(i18nStackedChart.placeholderNoData);
                return;
            }

            const { fullW, fullH } = computeSize();
            const width = fullW - margin.left - margin.right;
            const height = fullH - margin.top - margin.bottom;

            container.attr("viewBox", `0 0 ${fullW} ${fullH}`);
            root.selectAll("*").remove();

            const g = root.append("g")
                .attr("transform", `translate(${margin.left},${margin.top})`);

            // Parse dates and group by date
            const parseDate = d3.timeParse("%Y-%m-%d");
            const dataByDate = d3.rollup(
                rawData,
                v => Object.fromEntries(v.map(d => [d.type_label, d.count])),
                d => d.date
            );

            const dates = Array.from(dataByDate.keys()).sort();
            const types = Array.from(new Set(rawData.map(d => d.type_label)));

            const data = dates.map(date => {
                const obj = { date: parseDate(date) };
                types.forEach(type => {
                    obj[type] = dataByDate.get(date)[type] || 0;
                });
                return obj;
            });

            // Create stacked data
            const stack = d3.stack()
                .keys(types)
                .order(d3.stackOrderNone)
                .offset(d3.stackOffsetNone);

            const series = stack(data);

            // Scales
            const x = d3.scaleTime()
                .domain(d3.extent(data, d => d.date))
                .range([0, width]);

            const y = d3.scaleLinear()
                .domain([0, d3.max(series, s => d3.max(s, d => d[1]))])
                .nice()
                .range([height, 0]);

            const colorScale = d3.scaleOrdinal(colorScheme)
                .domain(types);

            // Area generator
            const area = d3.area()
                .x(d => x(d.data.date))
                .y0(d => y(d[0]))
                .y1(d => y(d[1]))
                .curve(d3.curveMonotoneX);

            // Draw areas
            g.selectAll(".layer")
                .data(series)
                .enter()
                .append("path")
                .attr("class", "layer")
                .attr("d", area)
                .attr("fill", d => colorScale(d.key))
                .style("opacity", 0.7);

            // Axes
            const xAxis = d3.axisBottom(x)
                .ticks(Math.min(8, Math.floor(width / 80)))
                .tickFormat(d3.timeFormat("%b %Y"));

            const yAxis = d3.axisLeft(y)
                .ticks(6)
                .tickFormat(d3.format(","));

            g.append("g")
                .attr("class", "x-axis")
                .attr("transform", `translate(0,${height})`)
                .call(xAxis)
                .selectAll("text")
                .attr("transform", "rotate(-45)")
                .style("text-anchor", "end");

            g.append("g")
                .attr("class", "y-axis")
                .call(yAxis);

            // Grid
            g.append("g")
                .attr("class", "grid")
                .attr("opacity", 0.1)
                .call(d3.axisLeft(y)
                    .ticks(6)
                    .tickSize(-width)
                    .tickFormat("")
                );

            // Legend
            const legend = root.append("g")
                .attr("transform", `translate(${margin.left + width + 20},${margin.top})`);

            const legendItems = legend.selectAll(".legend-item")
                .data(types)
                .enter()
                .append("g")
                .attr("class", "legend-item")
                .attr("transform", (d, i) => `translate(0,${i * 20})`);

            legendItems.append("rect")
                .attr("width", 15)
                .attr("height", 15)
                .attr("fill", d => colorScale(d))
                .style("opacity", 0.7);

            legendItems.append("text")
                .attr("x", 20)
                .attr("y", 7.5)
                .attr("dy", "0.35em")
                .style("font-size", "11px")
                .text(d => d);

            // Add tooltip
            const tooltip = d3.select("body").selectAll(`.tooltip-${anchor.replace('#', '')}`)
                .data([0])
                .join("div")
                .attr("class", `tooltip-${anchor.replace('#', '')}`)
                .style("position", "absolute")
                .style("background", "rgba(0, 0, 0, 0.8)")
                .style("color", "#fff")
                .style("padding", "8px 10px")
                .style("border-radius", "4px")
                .style("font-size", "12px")
                .style("pointer-events", "none")
                .style("opacity", 0)
                .style("z-index", 9999)
                .style("max-width", "250px")
                .style("box-shadow", "0 2px 8px rgba(0,0,0,0.3)");

            // Add overlay for mouse tracking
            g.append("rect")
                .attr("class", "overlay")
                .attr("width", width)
                .attr("height", height)
                .attr("fill", "none")
                .attr("pointer-events", "all")
                .on("mouseover", function() {
                    tooltip.style("opacity", 1);
                })
                .on("mouseout", function() {
                    tooltip.style("opacity", 0);
                })
                .on("mousemove", function(event) {
                    const [mx] = d3.pointer(event);
                    const x0 = x.invert(mx);
                    const bisect = d3.bisector(d => d.date).left;
                    const i = bisect(data, x0, 1);
                    const d0 = data[i - 1];
                    const d1 = data[i];
                    const d = d1 && x0 - d0.date > d1.date - x0 ? d1 : d0;

                    if (d) {
                        let html = `<strong>${d3.timeFormat("%B %d, %Y")(d.date)}</strong><br/>`;
                        types.forEach(type => {
                            html += `<span style="color: ${colorScale(type)};">●</span> ${type}: ${(d[type] || 0).toLocaleString()}<br/>`;
                        });

                        tooltip
                            .html(html)
                            .style("left", (event.pageX + 10) + "px")
                            .style("top", (event.pageY - 28) + "px");
                    }
                });
        }

        // Load data
        showPlaceholder(i18nStackedChart.placeholderLoading);

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

                if (cacheInfoElement && formatCacheDuration && responseData.ttl) {
                    const cacheInfo = formatCacheDuration(responseData.ttl);
                    const element = document.querySelector(cacheInfoElement);
                    if (element) {
                        element.innerHTML = "<i class=\"fa fa-clock-o\"></i> " + cacheInfo;
                    }
                }
            })
            .catch(error => {
                console.error("Error loading stacked chart data:", error);
                showPlaceholder(i18nStackedChart.placeholderError);
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

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
};
//{/literal}
