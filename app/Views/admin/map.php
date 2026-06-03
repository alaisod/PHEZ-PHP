<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แผนที่ร้านค้า | PH.EASY Admin</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css?v=<?= file_exists(FCPATH . 'assets/css/bulma.min.css') ? filemtime(FCPATH . 'assets/css/bulma.min.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/theme.css?v=<?= file_exists(FCPATH . 'assets/css/theme.css') ? filemtime(FCPATH . 'assets/css/theme.css') : '1' ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <style>
        /* ── Page Layout ──────────────────────────────────── */
        html, body { height: 100%; margin: 0; }
        .map-page { padding: 1rem; height: 100%; display: flex; flex-direction: column; }
        .map-page .container.is-fluid { flex: 1; display: flex; flex-direction: column; }

        /* ── Map Container ────────────────────────────────── */
        .map-container {
            flex: 1;
            min-height: 400px;
            border: 2px solid #ffcc00;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            background: #1b1b1b;
        }

        /* ── Loading Spinner ──────────────────────────────── */
        .map-loader {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #0f0f0f;
            z-index: 500;
            gap: 12px;
        }
        .map-loader .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #333;
            border-top-color: #ffcc00;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .map-loader .label { color: #888; font-size: 0.85rem; }

        /* ── Error State ──────────────────────────────────── */
        .map-error {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #0f0f0f;
            z-index: 500;
            gap: 12px;
        }
        .map-error .msg { color: #f14668; font-size: 1rem; }
        .map-error .retry-btn {
            background: #ffcc00;
            color: #1b1b1b;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .map-error .retry-btn:hover { background: #ffe066; }

        /* ── Empty State ──────────────────────────────────── */
        .map-empty {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #0f0f0f;
            z-index: 500;
            gap: 12px;
        }
        .map-empty .msg { color: #aaa; font-size: 1rem; }

        /* ── Search Bar ───────────────────────────────────── */
        .map-search {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 1000;
            background: #1b1b1b;
            border: 1px solid #ffcc00;
            border-radius: 8px;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            max-width: 280px;
        }
        .map-search input {
            background: transparent;
            border: none;
            color: #e0e0e0;
            outline: none;
            font-size: 0.875rem;
            width: 200px;
        }
        .map-search input::placeholder { color: #666; }
        .map-search-clear {
            color: #888;
            cursor: pointer;
            font-size: 1.2rem;
            line-height: 1;
            user-select: none;
        }
        .map-search-clear:hover { color: #ffcc00; }

        /* ── No Results ───────────────────────────────────── */
        .map-no-results {
            position: absolute;
            top: 56px;
            left: 12px;
            z-index: 1000;
            background: #1b1b1b;
            border: 1px solid #ffcc00;
            border-radius: 6px;
            padding: 6px 12px;
            color: #ffcc00;
            font-size: 0.8rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        /* ── Control Buttons (Fullscreen & Zoom-to-Fit) ──── */
        .map-control-btn {
            position: absolute;
            z-index: 1000;
            background: #1b1b1b;
            border: 1px solid #ffcc00;
            color: #ffcc00;
            border-radius: 6px;
            width: 36px;
            height: 36px;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        .map-control-btn:hover { background: #2a2a2a; }
        #mapFullscreenBtn { top: 12px; right: 12px; }
        #mapFitBtn { top: 56px; right: 12px; }

        /* ── Custom Leaflet Popup ─────────────────────────── */
        .leaflet-popup-content-wrapper {
            background: #1b1b1b;
            color: #e0e0e0;
            border: 1px solid #ffcc00;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            padding: 0;
            overflow: hidden;
        }
        .leaflet-popup-tip {
            background: #1b1b1b;
            border: 1px solid #ffcc00;
        }
        .leaflet-popup-content {
            margin: 0;
            font-size: 0.875rem;
            min-width: 220px;
        }
        .map-popup {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
        }
        .map-popup-photo {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid #3a3a5c;
        }
        .map-popup-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .map-popup-body {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .map-popup-name  { color: #ffcc00; font-size: 0.95rem; font-weight: 700; }
        .map-popup-code  { color: #aaa; font-size: 0.75rem; }
        .map-popup-link  { color: #ffcc00; font-size: 0.75rem; text-decoration: underline; margin-top: 4px; }
        .map-popup-link:hover { color: #ffe066; }

        /* ── Cluster Color Override (yellow theme) ────────── */
        .marker-cluster-small  { background-color: rgba(255, 204, 0, 0.3); }
        .marker-cluster-small div  { background-color: #ffcc00; }
        .marker-cluster-medium { background-color: rgba(255, 204, 0, 0.4); }
        .marker-cluster-medium div { background-color: #ffcc00; }
        .marker-cluster-large  { background-color: rgba(255, 204, 0, 0.5); }
        .marker-cluster-large div  { background-color: #ffcc00; }
        .marker-cluster { color: #1b1b1b; font-weight: 700; }

        /* ── Responsive ───────────────────────────────────── */
        @media screen and (max-width: 768px) {
            .map-container { min-height: 300px; }
            .map-search { max-width: 200px; }
            .map-search input { width: 140px; }
            .map-control-btn { width: 32px; height: 32px; font-size: 1rem; }
            #mapFullscreenBtn { right: 8px; top: 8px; }
            #mapFitBtn { right: 8px; top: 48px; }
            .map-popup { flex-direction: column; text-align: center; }
            .map-popup-photo { width: 50px; height: 50px; }
        }
        @media screen and (min-width: 1400px) {
            .map-search input { width: 260px; }
        }
    </style>
</head>
<body class="theme-bg">
    <section class="section map-page">
        <div class="container is-fluid">
            <!-- Header -->
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-3">
                <div>
                    <h1 class="title has-text-warning">แผนที่ร้านค้า</h1>
                    <a href="/admin" class="has-text-grey is-size-7">&larr; กลับสู่หน้าจัดการ</a>
                </div>
                <div id="shopCount" class="has-text-grey is-size-7">กำลังโหลด…</div>
            </div>

            <!-- Map Container -->
            <div id="map" class="map-container">
                <!-- Loading Spinner -->
                <div class="map-loader" id="mapLoader">
                    <div class="spinner"></div>
                    <div class="label">กำลังโหลดข้อมูลร้านค้า…</div>
                </div>

                <!-- Search Bar -->
                <div class="map-search" id="searchBar">
                    <input type="text" id="mapSearch" placeholder="ค้นหาร้านค้า…">
                    <span id="mapSearchClear" class="map-search-clear is-hidden">&times;</span>
                </div>

                <!-- No Results Message -->
                <div id="noResultsMsg" class="map-no-results is-hidden">ไม่พบร้านค้า</div>

                <!-- Control Buttons -->
                <button id="mapFullscreenBtn" class="map-control-btn" title="ขยายเต็มจอ">&#x26F6;</button>
                <button id="mapFitBtn" class="map-control-btn" title="แสดงทั้งหมด">&#x229E;</button>
            </div>
        </div>
    </section>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
    (function () {
        'use strict';

        // ── DOM refs ────────────────────────────────────────
        var mapContainer = document.getElementById('map');
        var mapLoader    = document.getElementById('mapLoader');
        var shopCountEl  = document.getElementById('shopCount');

        // ── State ───────────────────────────────────────────
        var map, mcg, allMarkers = [];

        // ── Fetch data ───────────────────────────────────────
        async function loadShops() {
            try {
                var res = await fetch('/admin/map-data');
                if (!res.ok) throw new Error('HTTP ' + res.status);
                var shops = await res.json();
                initMap(shops);
            } catch (err) {
                console.error('Map data fetch failed:', err);
                showError();
            }
        }

        // ── Show error ──────────────────────────────────────
        function showError() {
            mapLoader.remove();
            hideControls();
            var div = document.createElement('div');
            div.className = 'map-error';
            div.innerHTML =
                '<div class="msg">\u0E40\u0E01\u0E34\u0E14\u0E02\u0E49\u0E2D\u0E1C\u0E34\u0E14\u0E1E\u0E25\u0E32\u0E14\u0E43\u0E19\u0E01\u0E32\u0E23\u0E42\u0E2B\u0E25\u0E14\u0E02\u0E49\u0E2D\u0E21\u0E39\u0E25</div>' +
                '<button class="retry-btn" onclick="location.reload()">\u0E25\u0E2D\u0E07\u0E2D\u0E35\u0E01\u0E04\u0E23\u0E31\u0E07</button>';
            mapContainer.appendChild(div);
            shopCountEl.textContent = '\u0E40\u0E01\u0E34\u0E14\u0E02\u0E49\u0E2D\u0E1C\u0E34\u0E14\u0E1E\u0E25\u0E32\u0E14';
        }

        // ── Show empty state ────────────────────────────────
        function showEmpty() {
            mapLoader.remove();
            hideControls();
            var div = document.createElement('div');
            div.className = 'map-empty';
            div.innerHTML =
                '<div class="msg">\u0E44\u0E21\u0E48\u0E21\u0E35\u0E02\u0E49\u0E2D\u0E21\u0E39\u0E25\u0E23\u0E49\u0E32\u0E19\u0E04\u0E49\u0E32\u0E1A\u0E19\u0E41\u0E1C\u0E19\u0E17\u0E35\u0E48</div>' +
                '<a href="/admin" class="has-text-warning is-size-7">&larr; \u0E01\u0E25\u0E31\u0E1A\u0E2A\u0E39\u0E48\u0E2B\u0E19\u0E49\u0E32\u0E08\u0E31\u0E14\u0E01\u0E32\u0E23</a>';
            mapContainer.appendChild(div);
            shopCountEl.textContent = '\u0E44\u0E21\u0E48\u0E1E\u0E1A\u0E23\u0E49\u0E32\u0E19\u0E04\u0E49\u0E32\u0E1A\u0E19\u0E41\u0E1C\u0E19\u0E17\u0E35\u0E48';
        }

        // ── Hide controls helper ────────────────────────────
        function hideControls() {
            var el;
            el = document.getElementById('searchBar'); if (el) el.style.display = 'none';
            el = document.getElementById('mapFullscreenBtn'); if (el) el.style.display = 'none';
            el = document.getElementById('mapFitBtn'); if (el) el.style.display = 'none';
        }

        // ── Build popup HTML ────────────────────────────────
        function popupHtml(shop) {
            var photoHtml = shop.store_photo
                ? '<div class="map-popup-photo"><img src="/uploads/store_photos/' + escHtml(shop.store_photo) + '" alt=""></div>'
                : '';
            var cls = shop.store_photo ? 'map-popup' : 'map-popup no-photo';
            return '<div class="' + cls + '">' +
                photoHtml +
                '<div class="map-popup-body">' +
                '<strong class="map-popup-name">' + escHtml(shop.shop_name) + '</strong>' +
                '<span class="map-popup-code">#' + escHtml(shop.member_code) + '</span>' +
                '<a href="/admin/edit/' + shop.id + '" class="map-popup-link">\u0E41\u0E01\u0E49\u0E44\u0E02</a>' +
                '</div></div>';
        }

        function escHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        }

        // ── Initialize map ──────────────────────────────────
        function initMap(shops) {
            // Filter only members with valid geo_location
            var validShops = [];
            shops.forEach(function (s) {
                if (!s.geo_location) return;
                var parts = s.geo_location.split(',');
                var lat = parseFloat(parts[0]);
                var lng = parseFloat(parts[1]);
                if (isNaN(lat) || isNaN(lng)) return;
                if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
                validShops.push({ shop: s, lat: lat, lng: lng });
            });

            if (validShops.length === 0) {
                showEmpty();
                return;
            }

            // Remove loader
            mapLoader.remove();

            // Create map
            map = L.map('map', {
                center: [16.4419, 102.8350],
                zoom: 13,
                zoomControl: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            // Marker cluster group
            mcg = L.markerClusterGroup({
                chunkedLoading: true,
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
            });

            // Create markers
            allMarkers = [];
            validShops.forEach(function (item) {
                var marker = L.marker([item.lat, item.lng]);
                marker.shopName   = item.shop.shop_name;
                marker.memberCode = item.shop.member_code;
                marker.bindPopup(popupHtml(item.shop));
                allMarkers.push(marker);
                mcg.addLayer(marker);
            });

            mcg.addTo(map);
            shopCountEl.textContent = '\u0E1E\u0E1A ' + validShops.length + ' \u0E23\u0E49\u0E32\u0E19\u0E04\u0E49\u0E32';

            // Fit bounds after a short delay to let tiles load
            setTimeout(function () {
                if (mcg.getLayers().length > 0) {
                    map.fitBounds(mcg.getBounds().pad(0.1));
                }
            }, 300);
        }

        // ── Search / Filter ─────────────────────────────────
        function setupSearch() {
            var searchInput = document.getElementById('mapSearch');
            var searchClear = document.getElementById('mapSearchClear');
            var noResults   = document.getElementById('noResultsMsg');
            if (!searchInput) return;
            var timer;

            function filterMarkers(query) {
                mcg.clearLayers();
                if (query === '') {
                    allMarkers.forEach(function (m) { mcg.addLayer(m); });
                    if (noResults) noResults.classList.add('is-hidden');
                    return;
                }
                var q = query.toLowerCase();
                var matched = allMarkers.filter(function (marker) {
                    var name = (marker.shopName || '').toLowerCase();
                    var code = (marker.memberCode || '').toString();
                    return name.indexOf(q) !== -1 || code.indexOf(q) !== -1;
                });
                matched.forEach(function (m) { mcg.addLayer(m); });
                if (noResults) {
                    noResults.classList.toggle('is-hidden', matched.length > 0);
                }
            }

            searchInput.addEventListener('input', function () {
                var val = this.value.trim();
                searchClear.classList.toggle('is-hidden', val === '');
                clearTimeout(timer);
                timer = setTimeout(function () { filterMarkers(val); }, 300);
            });

            searchClear.addEventListener('click', function () {
                searchInput.value = '';
                searchClear.classList.add('is-hidden');
                filterMarkers('');
                searchInput.focus();
            });
        }

        // ── Fullscreen Toggle ───────────────────────────────
        function setupFullscreen() {
            var btn = document.getElementById('mapFullscreenBtn');
            if (!btn) return;
            if (!document.fullscreenEnabled) {
                btn.style.display = 'none';
                return;
            }
            btn.addEventListener('click', function () {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                    btn.title = '\u0E02\u0E22\u0E32\u0E22\u0E40\u0E15\u0E47\u0E21\u0E08\u0E2D';
                } else {
                    document.documentElement.requestFullscreen();
                    btn.title = '\u0E22\u0E48\u0E2D\u0E2D\u0E2D\u0E01';
                }
            });
            document.addEventListener('fullscreenchange', function () {
                if (map) setTimeout(function () { map.invalidateSize(); }, 200);
            });
        }

        // ── Zoom-to-Fit ─────────────────────────────────────
        function setupFit() {
            var btn = document.getElementById('mapFitBtn');
            if (!btn) return;
            btn.addEventListener('click', function () {
                if (mcg && mcg.getLayers().length > 0) {
                    map.fitBounds(mcg.getBounds().pad(0.1));
                }
            });
        }

        // ── Kick off ────────────────────────────────────────
        loadShops().then(function () {
            if (!map) return;  // empty or error state
            setupSearch();
            setupFullscreen();
            setupFit();
        });
    })();
    </script>
</body>
</html>
