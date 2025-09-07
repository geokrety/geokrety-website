//{literal}

(function() {
    const container = d3.select('#chart'); // this is your <svg> element
    const parseUTC = d3.utcParse('%Y-%m-%dT%H:%M:%S%Z');

    // base margins — a bit larger to avoid clipping
    const margin = { top: 8, right: 12, bottom: 22, left: 34 };

    // set responsive plumbing on the root svg
    container
        .attr('role', 'img')
        .style('overflow', 'visible')       // prevent text clipping
        .attr('preserveAspectRatio', 'xMinYMin meet');

    // build static groups/defs once
    const svg = container.append('g');    // we’ll translate this per-size
    const defs = container.append('defs');
    const grad = defs.append('linearGradient')
        .attr('id', 'areaGrad').attr('x1','0%').attr('x2','0%').attr('y1','0%').attr('y2','100%');
    grad.append('stop').attr('offset','0%').attr('stop-opacity',0.35).attr('stop-color','#69b3a2');
    grad.append('stop').attr('offset','100%').attr('stop-opacity',0.0).attr('stop-color','#69b3a2');

    const clip = defs.append('clipPath').attr('id','clip').append('rect');

    const gXAxis = svg.append('g').attr('class', 'x-axis');
    const gYAxis = svg.append('g').attr('class', 'y-axis');
    const gPlot  = svg.append('g').attr('clip-path','url(#clip)');

    const xLabel = svg.append('text').attr('class','x label').attr('text-anchor','end').text('(date)');
    const yLabel = svg.append('text').attr('class','y label').attr('text-anchor','end').attr('transform','rotate(-90)').text('(m)');

    const areaPath = gPlot.append('path').attr('fill', 'url(#areaGrad)').attr('stroke', 'none');
    const linePath = gPlot.append('path').attr('fill', 'none').attr('stroke', '#69b3a2').attr('stroke-width', 1);
    const outliersG = gPlot.append('g');

    // fetch + clean data once
    d3.json("{/literal}{'api_v1_geokret_stats_altitude_profile'|alias}{literal}").then(function(raw) {
        let data = raw
            .filter(d => Number.isFinite(d.elevation) && d.elevation > -50 && d.moved_on_datetime)
            .map(d => ({ date: parseUTC(d.moved_on_datetime), elevation: +d.elevation }))
            .filter(d => d.date instanceof Date && !isNaN(d.date))
            .sort((a,b) => a.date - b.date);

        if (!data.length) return;

        // precompute robust quantiles
        const elevs = data.map(d => d.elevation).sort(d3.ascending);
        const q02 = d3.quantile(elevs, 0.02), q98 = d3.quantile(elevs, 0.98);

        // resize-aware render
        function render() {
            const node = container.node();
            const fullW = node.clientWidth || 200;      // fallback if width not set
            const fullH = node.clientHeight || 100;

            // keep the svg’s width/height/current viewBox in sync
            container.attr('width', fullW).attr('height', fullH)
                .attr('viewBox', `0 0 ${fullW} ${fullH}`);

            const width  = Math.max(20, fullW  - margin.left - margin.right);
            const height = Math.max(20, fullH - margin.top  - margin.bottom);

            // position main group
            svg.attr('transform', `translate(${margin.left},${margin.top})`);

            // clip rect
            clip.attr('width', width).attr('height', height);

            // split data into inliers/outliers (for drawing only)
            const inliers  = data.filter(d => d.elevation >= q02 && d.elevation <= q98);
            const outliers = data.filter(d => d.elevation <  q02 ||  d.elevation >  q98);

            // scales
            const x = d3.scaleUtc()
                .domain(d3.extent(data, d => d.date))
                .range([0, width]);

            const yMin = Math.min(d3.min(elevs), q02);
            const yMax = Math.max(d3.max(elevs), q98);
            const y = d3.scaleLinear()
                .domain([yMin, yMax]).nice()
                .range([height, 0]);

            // size-aware ticks & label font
            const tickCountX = Math.max(2, Math.floor(width / 70));
            const tickCountY = 3;
            const fontSize = Math.max(7, Math.min(10, Math.floor(Math.min(width, height) / 12)));

            gXAxis
                .attr('transform', `translate(0,${height})`)
                .call(d3.axisBottom(x).ticks(tickCountX).tickSizeOuter(0))
                .call(g => g.selectAll('text').attr('font-size', fontSize).attr('transform','rotate(-20)').style('text-anchor','end'))
                .call(g => g.selectAll('path,line').attr('opacity', 0.3));

            gYAxis
                .call(d3.axisLeft(y).ticks(tickCountY).tickSizeOuter(0))
                .call(g => g.selectAll('text').attr('font-size', fontSize))
                .call(g => g.selectAll('path,line').attr('opacity', 0.3));

            // labels positioned inside the plot so they won’t clip
            yLabel
                .attr('x', -height)          // because rotated
                .attr('y', -margin.left + 10)
                .attr('font-size', fontSize);

            xLabel
                .attr('x', width)
                .attr('y', height + margin.bottom - 4)
                .attr('font-size', fontSize);

            const area = d3.area()
                .curve(d3.curveMonotoneX)
                .x(d => x(d.date))
                .y0(height)
                .y1(d => y(d.elevation));

            const line = d3.line()
                .curve(d3.curveMonotoneX)
                .x(d => x(d.date))
                .y(d => y(d.elevation));

            areaPath.datum(inliers).attr('d', area);
            linePath.datum(inliers).attr('d', line);

            // redraw outliers
            const dots = outliersG.selectAll('circle').data(outliers, d => d.date);
            dots.join(
                enter => enter.append('circle')
                    .attr('r', 1.8)
                    .attr('fill', 'red')
                    .attr('opacity', 0.55)
                    .attr('cx', d => x(d.date))
                    .attr('cy', d => y(d.elevation)),
                update => update
                    .attr('cx', d => x(d.date))
                    .attr('cy', d => y(d.elevation))
            );
        }

        // initial render
        render();

        // re-render on container resize (no page reload)
        const ro = new ResizeObserver(() => {
            // batch with rAF so rapid resizes don’t churn
            window.requestAnimationFrame(render);
        });
        ro.observe(container.node());
    });
})();
//{/literal}
