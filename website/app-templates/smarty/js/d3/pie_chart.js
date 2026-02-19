/**
 * Simple Pie Chart for displaying distribution data
 *
 * //{literal}
 * Initialize with:
 * initPieChart({
 *   anchor: "#chart",
 *   data: [{label: "Drop", count: 100, percentage: 25}, ...],
 *   colorScheme: d3.schemeCategory10
 * });
 * //{/literal}
 */

// Translations
const i18nPieChart = {
    missingConfig: "{t}Pie chart: missing required configuration{/t}",
    missingD3: "{t}Pie chart: d3 library not loaded{/t}",
    missingContainer: "{t}Pie chart: container not found{/t}",
    placeholderNoData: "{t}No data available{/t}",
};

//{literal}
window.initPieChart = function(config) {
    const {
        anchor,
        data = [],
        colorScheme = d3.schemeCategory10
    } = config;

    if (!anchor) {
        console.error(i18nPieChart.missingConfig, config);
        return;
    }

    function init() {
        if (typeof d3 === "undefined" || !d3) {
            console.error(i18nPieChart.missingD3);
            return;
        }

        const container = d3.select(anchor);
        if (container.empty()) {
            console.warn(i18nPieChart.missingContainer, anchor);
            return;
        }

        const margin = { top: 20, right: 120, bottom: 20, left: 20 };

        container
            .attr("preserveAspectRatio", "xMidYMid meet")
            .style("overflow", "visible")
            .style("width", "100%")
            .style("height", "100%")
            .style("display", "block");

        function computeSize() {
            const node = container.node();
            const parent = node ? (node.parentElement || node) : null;
            const rect = parent ? parent.getBoundingClientRect() : { width: 400, height: 300 };
            return {
                fullW: Math.max(300, Math.floor(rect.width || 400)),
                fullH: Math.max(200, Math.floor(rect.height || 300)),
            };
        }

        // Track hidden segments per chart instance
        const hiddenSegments = new Set();
        const labelToColorMap = {}; // Persistent color mapping for stable colors

        function render() {
            if (!data || data.length === 0) {
                container.selectAll("*").remove();
                container.append("text")
                    .attr("x", "50%")
                    .attr("y", "50%")
                    .attr("text-anchor", "middle")
                    .attr("fill", "#7a869a")
                    .attr("font-size", 14)
                    .text(i18nPieChart.placeholderNoData);
                return;
            }

            const { fullW, fullH } = computeSize();
            const width = fullW - margin.left - margin.right;
            const height = fullH - margin.top - margin.bottom;
            const radius = Math.min(width - 120, height) / 2;

            container.attr("viewBox", `0 0 ${fullW} ${fullH}`);
            container.selectAll("*").remove();

            const g = container.append("g")
                .attr("transform", `translate(${margin.left + radius},${margin.top + height / 2})`);

            const colorScale = d3.scaleOrdinal(colorScheme);

            // Sort data by count descending for consistent legend ordering
            const sortedData = [...data].sort((a, b) => b.count - a.count);

            // Build persistent color mapping on first render
            sortedData.forEach((item, index) => {
                if (!labelToColorMap[item.label]) {
                    labelToColorMap[item.label] = colorScale(index);
                }
            });

            // Filter out hidden segments for pie calculation
            const visibleData = sortedData.filter(d => !hiddenSegments.has(d.label));

            const pie = d3.pie()
                .value(d => d.count)
                .sort(null);

            const arc = d3.arc()
                .innerRadius(0)
                .outerRadius(radius);

            const arcs = g.selectAll(".arc")
                .data(pie(visibleData))
                .enter()
                .append("g")
                .attr("class", "arc");

            arcs.append("path")
                .attr("d", arc)
                .attr("fill", (d) => labelToColorMap[d.data.label])
                .attr("stroke", "white")
                .attr("stroke-width", 2)
                .style("opacity", 0.8)
                .style("cursor", "pointer")
                .on("mouseover", function(event, d) {
                    d3.select(this).style("opacity", 1);

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
                        .style("z-index", 9999)
                        .style("box-shadow", "0 2px 8px rgba(0,0,0,0.3)");

                    tooltip
                        .style("opacity", 1)
                        .html(`<strong>${d.data.label}</strong><br/>${d.data.count.toLocaleString()} (${d.data.percentage.toFixed(1)}%)`)
                        .style("left", (event.pageX + 10) + "px")
                        .style("top", (event.pageY - 28) + "px");

                    // Highlight corresponding legend item
                    const legendRect = d3.select(`.legend-item-${d.data.label.replace(/\s+/g, '_')}`).select("rect");
                    if (!legendRect.empty()) {
                        legendRect.style("filter", "brightness(1.3)");
                    }
                })
                .on("mousemove", function(event) {
                    const tooltip = d3.select(`.tooltip-${anchor.replace('#', '')}`);
                    tooltip
                        .style("left", (event.pageX + 10) + "px")
                        .style("top", (event.pageY - 28) + "px");
                })
                .on("mouseout", function(event, d) {
                    d3.select(this).style("opacity", 0.8);

                    const tooltip = d3.select(`.tooltip-${anchor.replace('#', '')}`);
                    tooltip.style("opacity", 0);

                    // Remove highlight from legend item
                    const legendRect = d3.select(`.legend-item-${d.data.label.replace(/\s+/g, '_')}`).select("rect");
                    if (!legendRect.empty()) {
                        legendRect.style("filter", "brightness(1)");
                    }
                });

            // Add legend
            const legend = container.append("g")
                .attr("transform", `translate(${margin.left + radius * 2 + 30},${margin.top + 20})`);

            const legendItems = legend.selectAll(".legend-item")
                .data(sortedData)
                .enter()
                .append("g")
                .attr("class", d => `legend-item legend-item-${d.label.replace(/\s+/g, '_')}`)
                .attr("transform", (d, i) => `translate(0,${i * 25})`)
                .style("cursor", "pointer");

            // Legend color box
            legendItems.append("rect")
                .attr("width", 18)
                .attr("height", 18)
                .attr("fill", (d) => labelToColorMap[d.label])
                .style("opacity", d => hiddenSegments.has(d.label) ? 0.3 : 0.8)
                .style("stroke", (d) => labelToColorMap[d.label])
                .style("stroke-width", d => hiddenSegments.has(d.label) ? 2 : 0);

            // Legend text
            legendItems.append("text")
                .attr("x", 24)
                .attr("y", 9)
                .attr("dy", "0.35em")
                .style("font-size", "12px")
                .style("opacity", d => hiddenSegments.has(d.label) ? 0.5 : 1)
                .style("text-decoration", d => hiddenSegments.has(d.label) ? "line-through" : "none")
                .text(d => `${d.label}: ${d.count.toLocaleString()} (${d.percentage.toFixed(1)}%)`);

            // Legend interaction: hover and click
            legendItems.on("mouseenter", function(event, d) {
                if (hiddenSegments.has(d.label)) return; // Skip if hidden

                d3.select(this).select("rect").style("filter", "brightness(1.3)");

                // Highlight corresponding pie segment
                g.selectAll(".arc path")
                    .filter(arcData => arcData.data.label === d.label)
                    .style("opacity", 1)
                    .style("filter", "brightness(1.1)");
            })
            .on("mouseleave", function(event, d) {
                if (hiddenSegments.has(d.label)) return; // Skip if hidden

                d3.select(this).select("rect").style("filter", "brightness(1)");

                // Remove highlight from pie segment
                g.selectAll(".arc path")
                    .filter(arcData => arcData.data.label === d.label)
                    .style("opacity", 0.8)
                    .style("filter", "brightness(1)");
            })
            .on("click", function(event, d) {
                if (hiddenSegments.has(d.label)) {
                    hiddenSegments.delete(d.label);
                } else {
                    hiddenSegments.add(d.label);
                }
                render(); // Recompute and redraw chart
            });
        }

        render();

        // Handle resize
        let resizeTimeout;
        window.addEventListener("resize", function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(render, 250);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
};
//{/literal}
