//{literal}


// set the dimensions and margins of the graph
const margin = {top: 10, right: 30, bottom: 30, left: 50},
    width = 200 - margin.left - margin.right,
    height = 100 - margin.top - margin.bottom;

// append the svg object to the body of the page
const svg = d3.select('#chart')
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
    .append("g")
    .attr("transform",
        "translate(" + margin.left + "," + margin.top + ")");

function to_date(d) {
    d.moved_on_datetime = d3.timeParse("%Y-%m-%dT%H:%M:%S%Z")(d.moved_on_datetime);
    return d;
}
//Read the data
d3.json("{/literal}{'api_v1_geokret_stats_altitude_profile'|alias}{literal}").then(function(data, error) {
    data = data.filter(function(d,i){ return d.elevation > -50 })

    // Add X axis --> it is a date format
    var x = d3.scaleTime()
        .domain(d3.extent(data, function(d) { return d3.timeParse("%Y-%m-%dT%H:%M:%S%Z")(d.moved_on_datetime); }))
        .range([ 0, width ]);
    svg.append("g")
        .attr("transform", "translate(0," + (height) + ")")
        .call(d3.axisBottom(x).ticks(3).tickSizeOuter(0))
        .selectAll("text")
        .style("text-anchor", "end")
        .attr("transform", "rotate(-20)");

    // Axes labels
    svg.append("text")
        .attr("class", "x label")
        .attr("text-anchor", "end")
        .attr("x", 0)
        .attr("y", 0)
        .text("(m)");

    svg.append("text")
        .attr("class", "y label")
        .attr("text-anchor", "end")
        .attr("x", -height)
        .attr("y", width)
        .attr("dy", "1em")
        .attr("transform", "rotate(-90)")
        .text("(date)");

    // Add Y axis
    var y = d3.scaleLinear()
        .domain( d3.extent(data, function(d) { return d.elevation; }) )
        .range([ height, 0 ]);
    svg.append("g")
        .attr("transform", "translate(-0,0)")
        .call(d3.axisLeft(y).ticks(3).tickSizeOuter(0));

    // Add the area
    svg.append("path")
        .datum(data)
        .attr("fill", "#69b3a2")
        .attr("fill-opacity", .3)
        .attr("stroke", "none")
        .attr("d", d3.area()
            .x(function(d) { return x(d3.timeParse("%Y-%m-%dT%H:%M:%S%Z")(d.moved_on_datetime)) })
            .y0( height )
            .y1(function(d) { return y(d.elevation) })
        )

    // Add the line
    svg.append("path")
        .datum(data)
        .attr("fill", "none")
        .attr("stroke", "#69b3a2")
        .attr("stroke-width", 1)
        .attr("d", d3.line()
            .x(function(d) { return x(d3.timeParse("%Y-%m-%dT%H:%M:%S%Z")(d.moved_on_datetime)) })
            .y(function(d) { return y(d.elevation) })
        )

    // Add the line
    svg.selectAll("myCircles")
        .data(data)
        .enter()
        .append("circle")
        .attr("fill", "red")
        .attr("stroke", "none")
        .attr("cx", function(d) { return x(d3.timeParse("%Y-%m-%dT%H:%M:%S%Z")(d.moved_on_datetime)) })
        .attr("cy", function(d) { return y(d.elevation) })
        .attr("r", 1)

});

//{/literal}
