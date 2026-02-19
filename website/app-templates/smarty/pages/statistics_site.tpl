{extends file='base.tpl'}

{block name=title}{t}GeoKrety Statistics{/t}{/block}

{\GeoKrety\Assets::instance()->addJs(constant('GK_CDN_D3_JS')) && ''}

{block name=content}
    <h1>{t}GeoKrety Statistics{/t}</h1>

    <div class="alert alert-info" role="alert">
        {t}This page shows global statistics about GeoKrety activity across the world.{/t}
    </div>

    <!-- Map Section -->
    <section class="statistics-section">
        <h2>{t}Geographic Distribution{/t}</h2>
        <p class="help-block">{t}This map displays the geographic distribution of GeoKrety activity. Toggle between viewing the number of GeoKrety currently in cache per country, or the total number of moves logged in each country.{/t}</p>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}World Map{/t}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="text-center" style="margin-bottom: 15px;">
                            <div class="btn-group btn-group-sm" role="group" id="map-mode-switcher">
                                <button type="button" class="btn btn-primary active" data-mode="geokrety">
                                    {t}GeoKrety in Cache{/t}
                                </button>
                                <button type="button" class="btn btn-default" data-mode="moves">
                                    {t}Total Moves{/t}
                                </button>
                            </div>
                        </div>
                        <div id="countries-map-container" style="width: 100%; height: 500px;">
                            <svg id="countries-map-chart" style="width: 100%; height: 100%;"></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Country Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}GeoKrety per Country{/t}</h3>
                    </div>
                    <div class="panel-body">
                        <p class="help-block">{t}This table shows the number of GeoKrety currently in cache in each country, along with the percentage of total GeoKrety. The trend sparklines are color-coded: green indicates increasing activity, red indicates decreasing activity, and gray indicates stable activity.{/t}</p>
                        <div style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-striped table-hover" id="country-stats-table">
                                <thead>
                                    <tr>
                                        <th class="text-right">#</th>
                                        <th>{t}Country{/t}</th>
                                        <th class="text-right">{t}All GeoKrety in cache{/t}</th>
                                        <th class="text-right">{t}Percentage{/t}</th>
                                        <th class="text-center">{t}Drop Trend{/t}</th>
                                        <th class="text-center" style="width: 120px;">{t}Dip Trend{/t}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <em>{t}Loading...{/t}</em>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="country-table-cache-info" class="text-muted small text-center" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Time Series Charts -->
    <section class="statistics-section">
        <h2>{t}Growth Over Time{/t}</h2>
        <p class="help-block">{t}These charts track the growth of the GeoKrety community. The graphs show monthly registration counts and cumulative totals over time for both users and GeoKrety.{/t}</p>
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}User Registrations{/t}</h3>
                    </div>
                    <div class="panel-body">
                        <p class="help-block"><small>{t}This chart shows how many new users joined the GeoKrety community each month, along with the cumulative total of registered users.{/t}</small></p>
                        <div id="users-chart-container" style="width: 100%; height: 350px;">
                            <svg id="users-chart" style="width: 100%; height: 100%;"></svg>
                        </div>
                        <div id="users-chart-cache-info" class="text-muted small text-center" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}GeoKrety Registrations{/t}</h3>
                    </div>
                    <div class="panel-body">
                        <p class="help-block"><small>{t}This chart shows how many new GeoKrety were registered each month, along with the cumulative total of registered GeoKrety.{/t}</small></p>
                        <div id="geokrety-chart-container" style="width: 100%; height: 350px;">
                            <svg id="geokrety-chart" style="width: 100%; height: 100%;"></svg>
                        </div>
                        <div id="geokrety-chart-cache-info" class="text-muted small text-center" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Distribution Charts -->
    <section class="statistics-section">
        <h2>{t}Activity Distribution{/t}</h2>
        <p class="help-block">{t}These charts show the distribution of different types of activities in the GeoKrety community.{/t}</p>

        <!-- Time Filter Controls -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="text-center">
                            <label>{t}Filter by year:{/t}</label>
                            <div class="btn-group btn-group-sm" role="group" id="year-filter-buttons" style="margin: 0 10px;">
                                <button type="button" class="btn btn-primary active" data-year="all">{t}All Time{/t}</button>
                            </div>
                            <div style="margin-top: 15px;">
                                <input type="range" id="year-slider" class="form-control-range" style="width: 80%; display: inline-block;" min="2007" max="2026" value="2026" step="1">
                                <span id="year-slider-value" style="margin-left: 10px; font-weight: bold;">2026</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}Move Type Distribution{/t}</h3>
                    </div>
                    <div class="panel-body">
                        <p class="help-block"><small>{t}Distribution of move types: Drop, Grab, Comment, Met, Archived, Dip, and Seen.{/t}</small></p>
                        <div id="move-type-chart-container" style="width: 100%; height: 350px;">
                            <svg id="move-type-chart" style="width: 100%; height: 100%;"></svg>
                        </div>
                        <div id="move-type-cache-info" class="text-muted small text-center" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}GeoKret Type Distribution{/t}</h3>
                    </div>
                    <div class="panel-body">
                        <p class="help-block"><small>{t}Distribution of GeoKret types: Traditional, Book/CD/DVD, Human, Coin, and KretyPost.{/t}</small></p>
                        <div id="geokrety-type-chart-container" style="width: 100%; height: 350px;">
                            <svg id="geokrety-type-chart" style="width: 100%; height: 100%;"></svg>
                        </div>
                        <div id="geokrety-type-cache-info" class="text-muted small text-center" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Leaderboards -->
    <section class="statistics-section">
        <h2>{t}Leaderboards{/t}</h2>
        <p class="help-block">{t}Top performers and most active locations in the GeoKrety network.{/t}</p>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}Top 50 Waypoints{/t}</h3>
                    </div>
                    <div class="panel-body">
                        <p class="help-block">{t}Most visited caches/waypoints sorted by number of unique GeoKrety.{/t}</p>
                        <div style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-striped table-hover" id="waypoints-table">
                                <thead>
                                    <tr>
                                        <th class="text-right">#</th>
                                        <th>{t}Waypoint{/t}</th>
                                        <th class="text-right">{t}Unique GeoKrety{/t}</th>
                                        <th class="text-right">{t}Visits{/t}</th>
                                        <th class="text-center">{t}Trend{/t}</th>
                                        <th>{t}Last Visit{/t}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <em>{t}Loading...{/t}</em>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="waypoints-cache-info" class="text-muted small text-center" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Media & Engagement -->
    <section class="statistics-section">
        <h2>{t}Media & Engagement{/t}</h2>
        <p class="help-block">{t}Community engagement through pictures and comments over time.{/t}</p>
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}Picture Uploads{/t}</h3>
                    </div>
                    <div class="panel-body">
                        <p class="help-block"><small>{t}Monthly picture uploads by type (GeoKret avatars, move pictures, user avatars).{/t}</small></p>
                        <div id="pictures-chart-container" style="width: 100%; height: 350px;">
                            <svg id="pictures-chart" style="width: 100%; height: 100%;"></svg>
                        </div>
                        <div id="pictures-cache-info" class="text-muted small text-center" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}Comments & Reports{/t}</h3>
                    </div>
                    <div class="panel-body">
                        <p class="help-block"><small>{t}Monthly comments and missing reports showing community engagement.{/t}</small></p>
                        <div id="comments-chart-container" style="width: 100%; height: 350px;">
                            <svg id="comments-chart" style="width: 100%; height: 100%;"></svg>
                        </div>
                        <div id="comments-cache-info" class="text-muted small text-center" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sparkline Zoom Modal -->
    <div class="modal fade" id="sparklineModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                    <h4 class="modal-title">{t}Trend Detail{/t} - <span id="sparklineCountryFlag" style="margin-right: 5px;"></span><span id="sparklineCountryName"></span></h4>
                </div>
                <div class="modal-body">
                    <div id="sparkline-detail-chart" style="width: 100%; height: 300px;">
                        <svg style="width: 100%; height: 100%;"></svg>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Close{/t}</button>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name=javascript}
{include 'js/d3/countries_heatmap.js'}
{include 'js/d3/time_series_chart.js'}
{include 'js/d3/pie_chart.js'}
{include 'js/d3/stacked_chart.js'}
{literal}
$(document).ready(function() {
    // Initialize map mode
    let currentMapMode = "geokrety";
    let cachedCountryData = null; // Cache for country data to avoid duplicate API calls
    let cachedMovesData = null; // Cache for moves data

    // Helper function to create inline sparkline SVG
    function createSparkline(data, width, height, country) {
        // Ensure data is an array
        if (!data) {
            return "<span class=\"text-muted\">-</span>";
        }

        // If data is a string (PostgreSQL array format like "{1,2,3}"), parse it
        if (typeof data === 'string') {
            try {
                // Remove curly braces and split by comma
                const cleaned = data.replace(/[{}]/g, '');
                data = cleaned ? cleaned.split(',').map(Number) : [];
            } catch (e) {
                console.error('Failed to parse sparkline data:', data, e);
                return "<span class=\"text-muted\">-</span>";
            }
        }

        // Ensure it's an array and has data
        if (!Array.isArray(data) || data.length === 0) {
            return "<span class=\"text-muted\">-</span>";
        }

        // Always start from 2007 (GeoKrety creation year)
        const firstYear = 2007;
        const years = [];
        for (let i = 0; i < data.length; i++) {
            years.push(firstYear + i);
        }

        const max = Math.max(...data, 1);
        const min = Math.min(...data, 0);
        const range = max - min || 1;
        const step = width / (data.length - 1 || 1);

        const points = data.map(function(value, index) {
            const x = index * step;
            const y = height - ((value - min) / range) * height;
            return x + "," + y;
        }).join(" ");

        const lastValue = data[data.length - 1];
        const prevValue = data[data.length - 2] || lastValue;
        const trendColor = lastValue > prevValue ? "#5cb85c" : (lastValue < prevValue ? "#d9534f" : "#999");

        // Build SVG with line and dots
        let svg = "<svg class=\"sparkline-svg\" data-country=\"" + (country || "") + "\" data-years='" + JSON.stringify(years) + "' data-values='" + JSON.stringify(data) + "' width=\"" + width + "\" height=\"" + height + "\" style=\"vertical-align: middle; cursor: pointer;\" title=\"{/literal}{t}Click to view details{/t}{literal}\">" +
               "<polyline fill=\"none\" stroke=\"" + trendColor + "\" stroke-width=\"1.5\" points=\"" + points + "\"/>";

        // Add dots at each data point
        data.forEach(function(value, index) {
            const x = index * step;
            const y = height - ((value - min) / range) * height;
            const r = index === data.length - 1 ? 2.5 : 1.5;
            const year = years[index];
            svg += "<circle class=\"sparkline-dot\" cx=\"" + x + "\" cy=\"" + y + "\" r=\"" + r + "\" fill=\"" + trendColor + "\" opacity=\"0.7\" style=\"cursor: pointer;\" data-year=\"" + year + "\" data-value=\"" + value + "\">" +
                   "<title>" + year + ": " + value.toLocaleString() + " GeoKrety</title>" +
                   "</circle>";
        });

        svg += "</svg>";
        return svg;
    }

    // Helper function to format cache duration
    function formatCacheDuration(ttl) {
        if (!ttl || ttl <= 0) return "";

        const ttlMinutes = Math.floor(ttl / 60);
        let ttlText = "";
        if (ttlMinutes < 60) {
            ttlText = ttlMinutes + " {/literal}{t}minutes{/t}{literal}";
        } else {
            const ttlHours = Math.floor(ttlMinutes / 60);
            if (ttlHours === 1) {
                ttlText = "1 {/literal}{t}hour{/t}{literal}";
            } else {
                ttlText = ttlHours + " {/literal}{t}hours{/t}{literal}";
            }
        }
        return "{/literal}{t}cache duration:{/t}{literal} " + ttlText;
    }

    // Function to populate country table
    function populateCountryTable(response) {
        const tbody = $("#country-stats-table tbody");
        tbody.empty();

        const data = response.data || response;
        if (data && data.length > 0) {
            data.forEach(function(item, index) {
                const row = $("<tr>");
                const countryCode = item.country ? item.country.toLowerCase() : "";
                const countryName = item.country ? item.country.toUpperCase() : "{/literal}{t}Unknown{/t}{literal}";

                row.append($("<td class=\"text-right\">").text(index + 1));

                const countryCell = $("<td>");
                if (countryCode) {
                    countryCell.append($("<span>").addClass("flag-icon flag-icon-" + countryCode));
                    countryCell.append(" ");
                }
                countryCell.append(countryName);
                row.append(countryCell);

                row.append($("<td class=\"text-right\">").text(item.count.toLocaleString()));
                row.append($("<td class=\"text-right\">").text(item.percentage.toFixed(2) + "%"));

                const sparklineSvg = createSparkline(item.trend || [], 120, 30, countryName);
                row.append($("<td class=\"text-center\">").html(sparklineSvg));

                const dipSparklineSvg = createSparkline(item.dip_trend || [], 100, 30, countryName);
                row.append($("<td class=\"text-center\">").html(dipSparklineSvg));

                tbody.append(row);
            });

            if (response.ttl) {
                $("#country-table-cache-info").html("<i class=\"fa fa-clock-o\"></i> " + formatCacheDuration(response.ttl));
            }
        } else {
            tbody.append("<tr><td colspan=\"6\" class=\"text-center\"><em>{/literal}{t}No data available{/t}{literal}</em></td></tr>");
        }
    }

    // Initialize countries heatmap with cached or fresh data
    function initMap(mode) {
        const legendTitle = mode === "geokrety"
            ? "{/literal}{t}GeoKrety in cache per country{/t}{literal}"
            : "{/literal}{t}Total moves per country{/t}{literal}";

        // Check if we have cached data
        const cachedData = mode === "geokrety" ? cachedCountryData : cachedMovesData;

        if (cachedData) {
            // Use cached data
            initCountriesHeatmap({
                anchor: "#countries-map-chart",
                data: cachedData,
                worldUrl: "{/literal}{constant('GK_CDN_LIBRARIES_WORLD_ATLAS_URL')}{literal}",
                topojsonUrl: "{/literal}{constant('GK_CDN_LIBRARIES_TOPOJSON_CLIENT_URL')}{literal}",
                legendTitle: legendTitle
            });
        } else {
            // Fetch data
            const dataUrl = mode === "geokrety"
                ? "{/literal}{'api_v1_stats_geokrety_per_country'|alias}{literal}"
                : "{/literal}{'api_v1_stats_moves_per_country'|alias}{literal}";

            initCountriesHeatmap({
                anchor: "#countries-map-chart",
                dataUrl: dataUrl,
                worldUrl: "{/literal}{constant('GK_CDN_LIBRARIES_WORLD_ATLAS_URL')}{literal}",
                topojsonUrl: "{/literal}{constant('GK_CDN_LIBRARIES_TOPOJSON_CLIENT_URL')}{literal}",
                legendTitle: legendTitle,
                onDataLoaded: function(data) {
                    // Cache the data
                    if (mode === "geokrety") {
                        cachedCountryData = data;
                        populateCountryTable(data);
                    } else {
                        cachedMovesData = data;
                    }
                }
            });
        }
    }

    // Initialize with default mode
    initMap(currentMapMode);

    // Map mode switcher
    $("#map-mode-switcher button").click(function() {
        const mode = $(this).data("mode");
        if (mode !== currentMapMode) {
            currentMapMode = mode;
            $("#map-mode-switcher button").removeClass("active btn-primary").addClass("btn-default");
            $(this).removeClass("btn-default").addClass("active btn-primary");
            initMap(mode);
        }
    });

    // Country table will be populated by initMap callback (no separate AJAX call needed)

    // Sparkline click handler for detail view
    $(document).on("click", ".sparkline-svg", function() {
        const $svg = $(this);
        const country = $svg.data("country");
        const years = $svg.data("years");
        const values = $svg.data("values");

        if (!years || !values || years.length === 0 || values.length === 0) {
            return;
        }

        // Set modal title with flag
        const $row = $svg.closest('tr');
        const flagClass = $row.find('.flag-icon').attr('class');
        if (flagClass) {
            $("#sparklineCountryFlag").attr('class', flagClass).text('');
        }
        $("#sparklineCountryName").text(country);

        // Prepare data for D3 line chart
        const chartData = years.map(function(year, i) {
            return { year: year, count: values[i] || 0 };
        });

        // Render detailed chart
        renderSparklineDetail(chartData);

        // Show modal
        $("#sparklineModal").modal("show");
    });

    // Function to render detailed sparkline chart using D3
    function renderSparklineDetail(data) {
        const container = d3.select("#sparkline-detail-chart svg");
        container.selectAll("*").remove();

        const margin = { top: 20, right: 30, bottom: 40, left: 60 };
        const width = 750 - margin.left - margin.right;
        const height = 300 - margin.top - margin.bottom;

        const svg = container
            .attr("viewBox", `0 0 750 300`)
            .append("g")
            .attr("transform", `translate(${margin.left},${margin.top})`);

        // Scales
        const x = d3.scaleLinear()
            .domain(d3.extent(data, d => d.year))
            .range([0, width]);

        const y = d3.scaleLinear()
            .domain([0, d3.max(data, d => d.count) || 1])
            .nice()
            .range([height, 0]);

        // Line generator
        const line = d3.line()
            .x(d => x(d.year))
            .y(d => y(d.count));

        // Add grid lines
        svg.append("g")
            .attr("class", "grid")
            .attr("opacity", 0.1)
            .call(d3.axisLeft(y)
                .tickSize(-width)
                .tickFormat(""));

        // Add axes
        svg.append("g")
            .attr("transform", `translate(0,${height})`)
            .call(d3.axisBottom(x).tickFormat(d3.format("d")));

        svg.append("g")
            .call(d3.axisLeft(y).tickFormat(d3.format(",")).ticks(5));

        // Add line
        svg.append("path")
            .datum(data)
            .attr("fill", "none")
            .attr("stroke", "#337ab7")
            .attr("stroke-width", 2)
            .attr("d", line);

        // Add dots
        svg.selectAll(".dot")
            .data(data)
            .enter()
            .append("circle")
            .attr("class", "dot")
            .attr("cx", d => x(d.year))
            .attr("cy", d => y(d.count))
            .attr("r", 4)
            .attr("fill", "#337ab7");

        // Add tooltip
        const tooltip = d3.select("body").selectAll(".tooltip-sparkline-detail")
            .data([0])
            .join("div")
            .attr("class", "tooltip-sparkline-detail")
            .style("position", "absolute")
            .style("background", "rgba(0, 0, 0, 0.8)")
            .style("color", "#fff")
            .style("padding", "8px")
            .style("border-radius", "4px")
            .style("font-size", "12px")
            .style("pointer-events", "none")
            .style("opacity", 0)
            .style("z-index", 9999);

        // Add focus circle for hover
        const focus = svg.append("g")
            .attr("class", "focus")
            .style("display", "none");

        focus.append("circle")
            .attr("r", 6)
            .attr("fill", "#337ab7")
            .attr("stroke", "#fff")
            .attr("stroke-width", 2);

        // Add overlay for mouse tracking
        svg.append("rect")
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
            const bisect = d3.bisector(d => d.year).left;
            const i = bisect(data, x0, 1);
            const d0 = data[i - 1];
            const d1 = data[i];
            const d = d1 && x0 - d0.year > d1.year - x0 ? d1 : d0;

            if (d) {
                focus.attr("transform", `translate(${x(d.year)},${y(d.count)})`);

                tooltip
                    .style("opacity", 1)
                    .html(`
                        <strong>${d.year}</strong><br/>
                        ${d.count.toLocaleString()} GeoKrety
                    `)
                    .style("left", (event.pageX + 10) + "px")
                    .style("top", (event.pageY - 28) + "px");
            }
        }

        // Add axis labels
        svg.append("text")
            .attr("transform", `translate(${width / 2},${height + 35})`)
            .style("text-anchor", "middle")
            .text("{/literal}{t}Year{/t}{literal}");

        svg.append("text")
            .attr("transform", "rotate(-90)")
            .attr("y", 0 - margin.left)
            .attr("x", 0 - (height / 2))
            .attr("dy", "1em")
            .style("text-anchor", "middle")
            .text("{/literal}{t}Number of GeoKrety{/t}{literal}");
    }

    // Initialize time series charts
    initTimeSeriesChart({
        anchor: "#users-chart",
        dataUrl: "{/literal}{'api_v1_stats_users_registrations'|alias}{literal}",
        title: "{/literal}{t}User Registrations{/t}{literal}",
        color: "#5cb85c",
        cacheInfoElement: "#users-chart-cache-info",
        formatCacheDuration: formatCacheDuration
    });

    initTimeSeriesChart({
        anchor: "#geokrety-chart",
        dataUrl: "{/literal}{'api_v1_stats_geokrety_registrations'|alias}{literal}",
        title: "{/literal}{t}GeoKrety Registrations{/t}{literal}",
        color: "#337ab7",
        cacheInfoElement: "#geokrety-chart-cache-info",
        formatCacheDuration: formatCacheDuration
    });

    // Load top waypoints table
    $.ajax({
        url: "{/literal}{'api_v1_stats_top_waypoints'|alias}{literal}",
        method: "GET",
        dataType: "json",
        success: function(response) {
            const tbody = $("#waypoints-table tbody");
            tbody.empty();

            const data = response.data || response;
            if (data && data.length > 0) {
                data.forEach(function(item, index) {
                    const row = $("<tr>");
                    row.append($("<td class=\"text-right\">").text(index + 1));

                    // Create waypoint link
                    const waypointLink = "{/literal}{constant('GK_SITE_BASE_SERVER_URL')}{literal}/go2geo/?wpt=" + encodeURIComponent(item.waypoint || "");
                    const waypointCell = $("<td>").html($("<a>").attr("href", waypointLink).attr("target", "_blank").text(item.waypoint || ""));
                    row.append(waypointCell);

                    // Swap columns: Unique GeoKrety before Visits
                    row.append($("<td class=\"text-right\">").text(item.unique_geokrety.toLocaleString()));
                    row.append($("<td class=\"text-right\">").text(item.visit_count.toLocaleString()));

                    // Add trend sparkline
                    const trendSparkline = createSparkline(item.trend || [], 80, 25, item.waypoint);
                    row.append($("<td class=\"text-center\">").html(trendSparkline));

                    const lastVisit = item.last_visit ? new Date(item.last_visit).toLocaleDateString() : "-";
                    row.append($("<td>").text(lastVisit));
                    tbody.append(row);
                });

                if (response.ttl) {
                    $("#waypoints-cache-info").html("<i class=\"fa fa-clock-o\"></i> " + formatCacheDuration(response.ttl));
                }
            } else {
                tbody.append("<tr><td colspan=\"6\" class=\"text-center\"><em>{/literal}{t}No data available{/t}{literal}</em></td></tr>");
            }
        },
        error: function() {
            $("#waypoints-table tbody").html(
                "<tr><td colspan=\"6\" class=\"text-center text-danger\"><em>{/literal}{t}Error loading data{/t}{literal}</em></td></tr>"
            );
        }
    });

    // Year filter state
    let currentFilterYear = "all";
    const currentYear = new Date().getFullYear();
    const startYear = 2007; // GeoKrety start year

    // Dynamically populate year buttons
    function populateYearButtons() {
        const container = $("#year-filter-buttons");
        container.empty();

        // All Time button
        container.append(
            $("<button>")
                .attr("type", "button")
                .addClass("btn btn-primary active")
                .attr("data-year", "all")
                .text("{/literal}{t}All Time{/t}{literal}")
        );

        // Add last 5 years as quick buttons
        const recentYears = [];
        for (let i = 0; i < 5; i++) {
            recentYears.push(currentYear - i);
        }

        recentYears.forEach(function(year) {
            container.append(
                $("<button>")
                    .attr("type", "button")
                    .addClass("btn btn-default")
                    .attr("data-year", year)
                    .text(year)
            );
        });
    }

    // Initialize year filter controls
    populateYearButtons();
    $("#year-slider").attr("min", startYear).attr("max", currentYear).val(currentYear);
    $("#year-slider-value").text(currentYear);

    // Function to load pie charts with year filter
    function loadDistributionCharts(year) {
        const yearParam = year === "all" ? "" : "?year=" + year;

        // Load move type distribution
        $.ajax({
            url: "{/literal}{'api_v1_stats_move_type_distribution'|alias}{literal}" + yearParam,
            method: "GET",
            dataType: "json",
            success: function(response) {
                const data = response.data || response;
                const chartData = data.map(function(d) {
                    return {
                        label: d.label,
                        count: parseInt(d.count),
                        percentage: parseFloat(d.percentage)
                    };
                });
                initPieChart({
                    anchor: "#move-type-chart",
                    data: chartData,
                    colorScheme: d3.schemeSet2
                });
                if (response.ttl) {
                    $("#move-type-cache-info").html("<i class=\"fa fa-clock-o\"></i> " + formatCacheDuration(response.ttl));
                }
            }
        });

        // Load GeoKret type distribution
        $.ajax({
            url: "{/literal}{'api_v1_stats_geokrety_type_distribution'|alias}{literal}" + yearParam,
            method: "GET",
            dataType: "json",
            success: function(response) {
                const data = response.data || response;
                const chartData = data.map(function(d) {
                    return {
                        label: d.label,
                        count: parseInt(d.count),
                        percentage: parseFloat(d.percentage)
                    };
                });
                initPieChart({
                    anchor: "#geokrety-type-chart",
                    data: chartData,
                    colorScheme: d3.schemeTableau10
                });
                if (response.ttl) {
                    $("#geokrety-type-cache-info").html("<i class=\"fa fa-clock-o\"></i> " + formatCacheDuration(response.ttl));
                }
            }
        });
    }

    // Initial load
    loadDistributionCharts("all");

    // Year filter button click handler
    $(document).on("click", "#year-filter-buttons button", function() {
        const year = $(this).data("year");
        currentFilterYear = year;

        // Update button states
        $("#year-filter-buttons button").removeClass("active btn-primary").addClass("btn-default");
        $(this).removeClass("btn-default").addClass("active btn-primary");

        // Update slider if not "all"
        if (year !== "all") {
            $("#year-slider").val(year);
            $("#year-slider-value").text(year);
        }

        // Reload charts
        loadDistributionCharts(year);
    });

    // Year slider change handler
    $("#year-slider").on("input", function() {
        const year = parseInt($(this).val());
        $("#year-slider-value").text(year);
        currentFilterYear = year;

        // Update button states - deactivate all quick buttons
        $("#year-filter-buttons button").removeClass("active btn-primary").addClass("btn-default");

        // If slider matches one of the quick buttons, activate it
        const matchingBtn = $("#year-filter-buttons button[data-year='" + year + "']");
        if (matchingBtn.length > 0) {
            matchingBtn.removeClass("btn-default").addClass("active btn-primary");
        }

        // Reload charts
        loadDistributionCharts(year);
    });

    // Load picture trends (stacked area chart)
    initStackedChart({
        anchor: "#pictures-chart",
        dataUrl: "{/literal}{'api_v1_stats_picture_trends'|alias}{literal}",
        colorScheme: d3.schemeCategory10,
        cacheInfoElement: "#pictures-cache-info",
        formatCacheDuration: formatCacheDuration
    });

    // Load comment statistics (stacked area chart)
    initStackedChart({
        anchor: "#comments-chart",
        dataUrl: "{/literal}{'api_v1_stats_comment_statistics'|alias}{literal}",
        colorScheme: d3.schemePaired,
        cacheInfoElement: "#comments-cache-info",
        formatCacheDuration: formatCacheDuration
    });
});
{/literal}
{/block}
