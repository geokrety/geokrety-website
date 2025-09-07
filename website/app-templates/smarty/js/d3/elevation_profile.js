(function ($anchor="#elevation-profile-chart", $url="{'api_v1_geokret_stats_elevation_profile'|alias}") {
//{literal}
    function init() {
        const container = d3.select($anchor);
        if (container.empty()) {return;}

        const parseUTC = d3.utcParse("%Y-%m-%dT%H:%M:%S%Z");
        const margin = { top: 3, right: 3, bottom: 35, left: 53 };

        container
            .attr("role", "img")
            .style("overflow", "visible")
            .style("width", "100%")
            .style("height", "100%")
            .style("display", "block")
            .attr("preserveAspectRatio", "xMinYMin meet");

        const svg  = container.append("g");
        const defs = container.append("defs");

        // placeholder renderer (responsive)
        function showPlaceholder(message) {
            const node = container.node();
            const parent = node.parentElement || node;
            const rect = parent.getBoundingClientRect();
            const fullW = Math.max(220, Math.floor(rect.width  || 220));
            const fullH = Math.max(120, Math.floor(rect.height || 120));

            container.attr("viewBox", `0 0 ${fullW} ${fullH}`);

            svg.selectAll("*").remove(); // clear any previous content

            // subtle dashed box + message
            svg.append("rect")
                .attr("x", 8).attr("y", 8)
                .attr("rx", 6).attr("ry", 6)
                .attr("width", Math.max(0, fullW - 16))
                .attr("height", Math.max(0, fullH - 16))
                .attr("fill", "none")
                .attr("stroke", "#d9dfe3")
                .attr("stroke-dasharray", "4,4");

            svg.append("text")
                .attr("x", fullW / 2)
                .attr("y", fullH / 2)
                .attr("text-anchor", "middle")
                .attr("dominant-baseline", "middle")
                .attr("fill", "#7a869a")
                .attr("font-size", Math.max(11, Math.min(14, Math.floor(Math.min(fullW, fullH) / 10))))
                .text(message);

            // keep placeholder responsive
            const ro = new ResizeObserver(() => window.requestAnimationFrame(() => showPlaceholder(message)));
            ro.observe(parent);
        }

        // gradient for area
        const grad = defs.append("linearGradient")
            .attr("id", "areaGrad").attr("x1","0%").attr("x2","0%").attr("y1","0%").attr("y2","100%");
        grad.append("stop").attr("offset","0%").attr("stop-opacity",0.55).attr("stop-color","#56a391");
        grad.append("stop").attr("offset","100%").attr("stop-opacity",0.00).attr("stop-color","#56a391");

        const clipR  = defs.append("clipPath").attr("id","clip").append("rect");

        const gXAxis   = svg.append("g").attr("class", "x-axis");
        const gYAxis   = svg.append("g").attr("class", "y-axis");
        const gGridY   = svg.append("g").attr("class", "y-grid");
        const gPlot    = svg.append("g").attr("clip-path","url(#clip)");
        const segG     = gPlot.append("g").attr("class","grade-segments");
        const areaPath = gPlot.append("path").attr("fill", "url(#areaGrad)").attr("stroke", "none");
        const linePath = gPlot.append("path").attr("fill", "none").attr("stroke", "#2f7d71").attr("stroke-width", 1.4);
        const extremaG = gPlot.append("g").attr("class","extrema");
        const xLabel   = svg.append("text").attr("class","x label");

        const toRad = (d) => d * Math.PI / 180;
        function haversine(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dPhi = toRad(lat2 - lat1), dLam = toRad(lon2 - lon1);
            const a = Math.sin(dPhi/2)**2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLam/2)**2;
            return 2 * R * Math.asin(Math.sqrt(a));
        }
        const latOf = (d) => d.lat ?? d.latitude ?? d.coords?.lat ?? d.location?.lat ?? null;
        const lonOf = (d) => d.lon ?? d.long ?? d.lng ?? d.longitude ?? d.coords?.lng ?? d.location?.lon ?? d.location?.lng ?? null;

        d3.json($url).then(function(raw) {
            let data = (raw || [])
                .filter((d) => Number.isFinite(+d.elevation) && +d.elevation > -50 && d.moved_on_datetime)
                .map((d) => ({ date: parseUTC(d.moved_on_datetime), elevation: +d.elevation, lat: +latOf(d), lon: +lonOf(d) }))
                .filter((d) => d.date instanceof Date && !isNaN(d.date))
                .sort((a, b) => a.date - b.date);

            // Empty dataset → placeholder
            if (!data.length) { showPlaceholder("No elevation data"); return; }

            // compute distance/slope if coords exist
            let haveCoords = 0;
            for (const d of data) {if (Number.isFinite(d.lat) && Number.isFinite(d.lon)) {haveCoords++;}}
            const useDistance = haveCoords >= 2;

            if (useDistance) {
                let cum = 0;
                data[0].dist = 0;
                data[0].slope = 0;
                for (let i = 1; i < data.length; i++) {
                    const a = data[i-1], b = data[i];
                    const seg = (Number.isFinite(a.lat)&&Number.isFinite(a.lon)&&Number.isFinite(b.lat)&&Number.isFinite(b.lon))
                        ? haversine(a.lat,a.lon,b.lat,b.lon) : 0;
                    cum += seg; b.dist = cum;
                    const rise = b.elevation - a.elevation;
                    b.slope = seg > 0 ? (100 * rise / seg) : 0;
                }
            }

            // robust gates; inliers drive Y to avoid excess headroom
            const elevs   = data.map((d) => d.elevation).sort(d3.ascending);
            const q02     = d3.quantile(elevs, 0.02);
            const q98     = d3.quantile(elevs, 0.98);
            const inliers = data.filter((d) => d.elevation >= q02 && d.elevation <= q98);

            // Not enough points to draw a line
            if (inliers.length < 2) { showPlaceholder("Not enough data points"); return; }

            const inMin   = d3.min(inliers, (d) => d.elevation) ?? 0;
            const inMax   = d3.max(inliers, (d) => d.elevation) ?? 0;

            const maxGrade = 15;

            function render() {
                const node = container.node();
                const parent = node.parentElement || node;
                const rect = parent.getBoundingClientRect();
                const fullW = Math.max(220, Math.floor(rect.width  || 220));
                const fullH = Math.max(120, Math.floor(rect.height || 120));

                container.attr("viewBox", `0 0 ${fullW} ${fullH}`);

                const width  = Math.max(20, fullW  - margin.left - margin.right);
                const height = Math.max(20, fullH - margin.top  - margin.bottom);

                svg.attr("transform", `translate(${margin.left},${margin.top})`);
                clipR.attr("x", 0).attr("y", -14).attr("width", width).attr("height", height + 14);

                // X scale
                let x, xFmt, xTicks;
                if (useDistance) {
                    const maxM = d3.max(data, (d) => d.dist);
                    x = d3.scaleLinear().domain([0, maxM]).range([0, width]);
                    const km = maxM >= 2000;
                    xFmt = km ? (d) => (d/1000).toFixed(d >= 10000 ? 0 : 1) + " km" : d3.format(".0f");
                    xTicks = Math.max(2, Math.floor(width / 90));
                } else {
                    x = d3.scaleUtc().domain(d3.extent(data, (d) => d.date)).range([0, width]);
                    xFmt = d3.utcFormat("%Y-%m-%d");
                    xTicks = Math.max(2, Math.floor(width / 90));
                }

                // Y scale
                const yMin = Math.min(inMin, 0);
                const yMax = inMax + Math.max(2, (inMax - yMin) * 0.015);
                const y = d3.scaleLinear().domain([yMin, yMax]).range([height, 0]);

                const fontSize = Math.max(8, Math.min(11, Math.floor(Math.min(width, height) / 12)));
                const tickCountY = 4;

                const axX = d3.axisBottom(x).ticks(xTicks).tickSizeOuter(0).tickFormat(xFmt);
                const axY = d3.axisLeft(y).ticks(tickCountY).tickSizeOuter(0).tickFormat((d) => d3.format(",")(d) + " m");

                gXAxis.attr("transform", `translate(0,${height})`).call(axX);
                gYAxis.call(axY);

                // style axes; remove domain lines
                gXAxis.selectAll(".domain").remove();
                gYAxis.selectAll(".domain").remove();
                gXAxis.selectAll("line").attr("stroke", "#c7c7c7").attr("opacity", 0.6);
                gYAxis.selectAll("line").attr("stroke", "#c7c7c7").attr("opacity", 0.6);
                gXAxis.selectAll("text").attr("font-size", fontSize).attr("transform","rotate(-20)").style("text-anchor","end");
                gYAxis.selectAll("text").attr("font-size", fontSize);

                // nudge origin labels apart
                if (useDistance) {
                    gXAxis.selectAll("g.tick").filter((d) => +d === 0).select("text").attr("dy", "1.2em").attr("x", 2);
                }

                // gridlines
                gGridY
                    .attr("transform", "translate(0,0)")
                    .call(d3.axisLeft(y).ticks(tickCountY).tickSize(-width).tickFormat(""))
                    .call((g) => g.selectAll("line").attr("stroke", "#d9dfe3").attr("stroke-opacity", 0.6));
                gGridY.selectAll(".domain").remove();

                // ensure a small right-edge tick in distance mode (unlabeled)
                gXAxis.selectAll("line.end-tick").remove();
                if (useDistance) {
                    const hasEdgeTick = gXAxis.selectAll("g.tick").filter(function () {
                        const m = d3.select(this).attr("transform")?.match(/translate\(([-\d.]+),/);
                        return m && Math.abs(+m[1] - width) < 0.5;
                    }).size() > 0;
                    if (!hasEdgeTick) {
                        gXAxis.append("line")
                            .attr("class", "end-tick")
                            .attr("x1", width).attr("x2", width)
                            .attr("y1", 0).attr("y2", 6)
                            .attr("stroke", "#c7c7c7").attr("opacity", 0.6);
                    }
                }

                // draw area + baseline (inliers)
                const area = d3.area()
                    .curve(d3.curveMonotoneX)
                    .x((d) => useDistance ? x(d.dist) : x(d.date))
                    .y0(height)
                    .y1((d) => y(d.elevation));
                areaPath.datum(inliers).attr("d", area);

                const base = d3.line()
                    .curve(d3.curveMonotoneX)
                    .x((d) => useDistance ? x(d.dist) : x(d.date))
                    .y((d) => y(d.elevation));
                linePath.datum(inliers).attr("d", base).attr("opacity", 0.4);

                // slope-colored segments
                const maxGrade = 15;
                const gradeColor = d3.scaleDiverging().domain([-maxGrade, 0, maxGrade]).interpolator((t) => d3.interpolateRdBu(1 - t));
                const segments = [];
                for (let i = 1; i < inliers.length; i++) {
                    const a = inliers[i-1], b = inliers[i];
                    const x1 = useDistance ? x(a.dist) : x(a.date);
                    const x2 = useDistance ? x(b.dist) : x(b.date);
                    const y1 = y(a.elevation), y2 = y(b.elevation);
                    const segM = useDistance ? (b.dist - a.dist) : Math.max(1, (b.date - a.date)/1000);
                    const s = useDistance ? b.slope : (100 * (b.elevation - a.elevation) / segM);
                    const slope = Math.max(-maxGrade, Math.min(maxGrade, s || 0));
                    segments.push({ x1, y1, x2, y2, slope });
                }
                const segSel = segG.selectAll("path").data(segments);
                segSel.join(
                    (enter) => enter.append("path").attr("stroke-width", 1.8).attr("stroke-linecap", "round"),
                    (update) => update
                ).attr("d", (d) => `M${d.x1},${d.y1}L${d.x2},${d.y2}`)
                    .attr("stroke", (d) => gradeColor(d.slope));

                // sea level baseline if within range
                if (y.domain()[0] < 0 && y.domain()[1] > 0) {
                    const sea = gPlot.selectAll("line.sea").data([0]);
                    sea.join(
                        (enter) => enter.append("line").attr("class","sea")
                            .attr("x1",0).attr("x2",width).attr("y1",y(0)).attr("y2",y(0))
                            .attr("stroke","#0077b6").attr("stroke-dasharray","3,3").attr("stroke-opacity",0.6),
                        (update) => update.attr("x2",width).attr("y1",y(0)).attr("y2",y(0))
                    );
                } else {
                    gPlot.selectAll("line.sea").remove();
                }

                // extremum labels: flip below if near top, shift left/right near edges
                const minD = d3.least(inliers, (d) => d.elevation);
                const maxD = d3.greatest(inliers, (d) => d.elevation);
                const ex = extremaG.selectAll("g.ext").data([minD, maxD].filter(Boolean));
                const fmtElev = d3.format(".0f");

                ex.join(
                    (enter) => {
                        const g = enter.append("g").attr("class","ext");
                        g.append("circle").attr("r", 2.5).attr("fill", "#222");
                        g.append("text").attr("class","ext-label").attr("fill", "#222");
                        return g;
                    },
                    (update) => update
                ).attr("transform", (d) => `translate(${useDistance ? x(d.dist) : x(d.date)},${y(d.elevation)})`);

                extremaG.selectAll("text.ext-label")
                    .attr("font-size", Math.max(8, Math.min(10, Math.floor(Math.min(width, height) / 12))))
                    .attr("dy", (d) => (y(d.elevation) < 12 ? 12 : -4))
                    .attr("text-anchor", (d) => {
                        const X = useDistance ? x(d.dist) : x(d.date);
                        if (X < 12) {
                            return "start";
                        }
                        if (X > width - 12) {
                            return "end";
                        }
                        return "middle";
                    })
                    .attr("dx", (d) => {
                        const X = useDistance ? x(d.dist) : x(d.date);
                        if (X < 12) {
                            return 4;
                        }
                        if (X > width - 12) {
                            return -4;
                        }
                        return 0;
                    })
                    .text((d) => fmtElev(d.elevation) + " m");
            }

            render();

            const node = container.node();
            const parent = node.parentElement || node;
            const ro = new ResizeObserver(() => window.requestAnimationFrame(render));
            ro.observe(parent);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    } else {
        init();
    }
})();
//{/literal}
