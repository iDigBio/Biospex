<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Chart</title>
    <!-- Resources -->
    <script src="//www.amcharts.com/lib/4/core.js"></script>
    <script src="//www.amcharts.com/lib/4/charts.js"></script>
    <script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>
</head>
<body>
<div>Testing</div>
<div id="chartdiv" style="width: 900px; height: 800px;"></div>
<script>
    // Create chart instance in one go
    let config = {!! $config !!};
    let chart = am4core.createFromConfig(config, "chartdiv", am4charts.XYChart);
</script>
</body>
</html>