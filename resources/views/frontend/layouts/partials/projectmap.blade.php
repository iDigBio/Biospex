<div class="col-md-8">
    <h2 class="project-page-header">{{ trans('pages.project_map_title') }}</h2>
    <style type="text/css">
        html, body, #googft-mapCanvas {
            height: 300px;
            margin: 0;
            padding: 0;
            width: 500px;
        }
    </style>

    <script type="text/javascript" src="https://maps.google.com/maps/api/js?v=3&key={{ Config::get('google.map_api_key') }}"></script>

    <script type="text/javascript">
        function initialize() {
            google.maps.visualRefresh = true;
            var isMobile = (navigator.userAgent.toLowerCase().indexOf('android') > -1) ||
                (navigator.userAgent.match(/(iPod|iPhone|iPad|BlackBerry|Windows Phone|iemobile)/));
            if (isMobile) {
                var viewport = document.querySelector("meta[name=viewport]");
                viewport.setAttribute('content', 'initial-scale=1.0, user-scalable=no');
            }
            var mapDiv = document.getElementById('googft-mapCanvas');
            mapDiv.style.width = isMobile ? '100%' : '600px';
            mapDiv.style.height = isMobile ? '100%' : '400px';
            var map = new google.maps.Map(mapDiv, {
                center: new google.maps.LatLng(37.423, -122.084),
                zoom: 3,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });

            layer = new google.maps.FusionTablesLayer({
                map: map,
                heatmap: {enabled: false},
                query: {
                    select: "col2",
                    from: "{{ $project->fusion_table_id }}",
                    where: ""
                },
                options: {
                    styleId: 2,
                    templateId: 2
                }
            });

            if (isMobile) {
                var legend = document.getElementById('googft-legend');
                var legendOpenButton = document.getElementById('googft-legend-open');
                var legendCloseButton = document.getElementById('googft-legend-close');
                legend.style.display = 'none';
                legendOpenButton.style.display = 'block';
                legendCloseButton.style.display = 'block';
                legendOpenButton.onclick = function () {
                    legend.style.display = 'block';
                    legendOpenButton.style.display = 'none';
                }
                legendCloseButton.onclick = function () {
                    legend.style.display = 'none';
                    legendOpenButton.style.display = 'block';
                }
            }
        }

        google.maps.event.addDomListener(window, 'load', initialize);
    </script>
    </head>

    <body>
    <div id="googft-mapCanvas"></div>
</div>