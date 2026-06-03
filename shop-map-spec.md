# Shop Map Feature — Specification

## 1. Overview

Add an interactive map page to the admin dashboard that displays all registered shops as pinned markers on a Leaflet (OpenStreetMap) map. The map is an **additional view** alongside the existing table view at `/admin`.

## 2. Access & Permissions

- **Visibility:** Admin dashboard only (protected by the `auth` filter, same as existing admin routes).
- **Navigation:** A button labeled "แสดงแผนที่" (Show Map) on the existing `/admin` table page, placed next to the "+ เพิ่มสมาชิก" button.
- **Route:** `GET /admin/map` — added within the existing `admin` route group in `app/Config/Routes.php`.

## 3. Map Library & Dependencies

- **Library:** Leaflet v1.9.4 (OpenStreetMap tiles) — already used in the registration form.
  - CSS CDN: `https://unpkg.com/leaflet@1.9.4/dist/leaflet.css`
  - JS CDN: `https://unpkg.com/leaflet@1.9.4/dist/leaflet.js`
  - *Note:* Leaflet JS is loaded at the **end of `<body>`**, CSS in `<head>`, matching the existing pattern in `register.php`.
- **Marker Clustering:** `leaflet.markercluster` **v1.5.4** (latest stable).
  - CSS: `https://unpkg.com/leaflet.markercluster@1.5.4/dist/MarkerCluster.css`
  - CSS: `https://unpkg.com/leaflet.markercluster@1.5.4/dist/MarkerCluster.Default.css`
  - JS:  `https://unpkg.com/leaflet.markercluster@1.5.4/dist/leaflet.markercluster.js`
  - *Both CSS files are required* — `MarkerCluster.css` for core cluster styles, `MarkerCluster.Default.css` for default circle/color styling.
- **No API key required** (OpenStreetMap tiles are free).

## 4. Data Source & Loading

- **Data:** Fetch all members that have a non-empty `geo_location` field (format: `"latitude,longitude"` e.g., `"16.4419,102.8350"`).
- **Loading strategy:** Load all qualifying members at once on page load via an AJAX call to a JSON endpoint.
- **JSON endpoint:** `GET /admin/map-data` — returns a JSON array of members with fields: `id`, `shop_name`, `member_code`, `geo_location`, `store_photo`, `shop_telephone`, `address`.
  - Members without `geo_location` are **excluded** from the response.
- **Default center:** Fixed at `16.4419, 102.8350` (Khon Kaen, Thailand) with zoom level 13 (same default as registration form).

## 5. Map UI & Controls

The map view will include:

### 5.1 Interactive Map

Leaflet map filling the main content area, initialized with:
```js
const map = L.map('map', {
    center: [16.4419, 102.8350],  // Khon Kaen, Thailand
    zoom: 13,
    zoomControl: true,
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);
```

### 5.2 Marker Clustering

Using `Leaflet.markercluster` v1.5.4. Initialize the cluster group and add all markers to it:
```js
const mcg = L.markerClusterGroup({
    chunkedLoading: true,        // Load markers in chunks for performance
    maxClusterRadius: 50,        // Pixels — cluster markers within 50px of each other
    spiderfyOnMaxZoom: true,     // When zoomed to max, "spiderfy" overlapping markers
    showCoverageOnHover: false,  // Don't show cluster coverage area on hover
    zoomToBoundsOnClick: true,   // Click cluster to zoom to its bounds
});

// Create markers with custom properties for search
let allMarkers = [];
shops.forEach(function (shop) {
    const parts = shop.geo_location.split(',');
    const lat = parseFloat(parts[0]);
    const lng = parseFloat(parts[1]);
    if (isNaN(lat) || isNaN(lng)) return;  // skip invalid

    const marker = L.marker([lat, lng]);
    // Attach searchable properties directly on the marker object
    marker.shopName = shop.shop_name;
    marker.memberCode = shop.member_code;
    marker.bindPopup(buildPopupHtml(shop));
    allMarkers.push(marker);
    mcg.addLayer(marker);
});

mcg.addTo(map);
```

### 5.3 Pin Popups

Each marker gets a custom dark-themed popup. Clicking a pin shows:

**Popup HTML structure (dark theme):**
```html
<div class="map-popup">
    <div class="map-popup-photo">
        <img src="/uploads/store_photos/{photo}" alt="{shop_name}">
    </div>
    <div class="map-popup-body">
        <strong class="map-popup-name">{shop_name}</strong>
        <span class="map-popup-code">#{member_code}</span>
        <a href="/admin/edit/{id}" class="map-popup-link">แก้ไข</a>
    </div>
</div>
```

**CSS for popup (inline in view):**
```css
/* ── Custom Leaflet popup dark theme ── */
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
.map-popup-name {
    color: #ffcc00;
    font-size: 0.95rem;
    font-weight: 700;
}
.map-popup-code {
    color: #aaa;
    font-size: 0.75rem;
}
.map-popup-link {
    color: #ffcc00;
    font-size: 0.75rem;
    text-decoration: underline;
    margin-top: 4px;
}
.map-popup-link:hover {
    color: #ffe066;
}
/* Handle popup when no photo */
.map-popup.no-photo {
    padding-left: 12px;
}
```

*If a member has no store photo, the photo div is omitted and the `.map-popup.no-photo` class is added.*

**Photo URL convention:** The JSON endpoint returns just the filename (e.g., `"abc123.jpg"`). The full URL path `/uploads/store_photos/{filename}` is constructed **client-side** in JavaScript when building popup HTML.

### 5.4 Search / Filter

A floating search input bar positioned at the top-left of the map container.

**Behavior:**
1. User types in the search input.
2. A **debounce timer (300ms)** waits for the user to stop typing.
3. When the clear button `×` is clicked: clear the input, restore all markers, hide the clear button.
4. After debounce, iterate all markers in the `allMarkers` array:
   - Compare the query against each marker's `shopName` and `memberCode` (custom properties, case-insensitive).
   - Clear the cluster group (`mcg.clearLayers()`) and re-add only matching markers.
   - `clearLayers()` + `addLayer()` automatically triggers re-clustering — no need to call `refreshClusters()` manually.
5. If the search query is empty, re-add all markers to the cluster group.
6. If no markers match, show a small floating "ไม่พบร้านค้า" ("No shops found") label.
7. Show/hide the clear button (`×`) based on whether input has text.

**Search input HTML:**
```html
<div class="map-search">
    <input type="text" id="mapSearch" placeholder="ค้นหาร้านค้า…" />
    <span id="mapSearchClear" class="map-search-clear is-hidden">&times;</span>
</div>
```

**Implementation pseudocode:**
```js
const searchInput = document.getElementById('mapSearch');
const searchClear = document.getElementById('mapSearchClear');
const noResultsMsg = document.getElementById('noResultsMsg');  // floating "ไม่พบร้านค้า"
let searchTimer;

searchInput.addEventListener('input', function () {
    const val = this.value.trim();
    searchClear.classList.toggle('is-hidden', val === '');
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => filterMarkers(val), 300);
});

searchClear.addEventListener('click', function () {
    searchInput.value = '';
    searchClear.classList.add('is-hidden');
    filterMarkers('');
    searchInput.focus();
});

function filterMarkers(query) {
    mcg.clearLayers();
    if (query === '') {
        allMarkers.forEach(m => mcg.addLayer(m));
        noResultsMsg.classList.add('is-hidden');
        return;
    }
    const q = query.toLowerCase();
    const matched = allMarkers.filter(marker => {
        const name = (marker.shopName || '').toLowerCase();
        const code = (marker.memberCode || '').toString();
        return name.includes(q) || code.includes(q);
    });
    matched.forEach(m => mcg.addLayer(m));
    noResultsMsg.classList.toggle('is-hidden', matched.length > 0);
}
```

**No-results message HTML (floating below search bar):**
```html
<div id="noResultsMsg" class="map-no-results is-hidden">ไม่พบร้านค้า</div>
```

```css
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
```

**Search bar CSS:**
```css
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
.map-search input::placeholder {
    color: #666;
}
.map-search-clear {
    color: #888;
    cursor: pointer;
    font-size: 1.2rem;
    line-height: 1;
}
.map-search-clear:hover {
    color: #ffcc00;
}
```

### 5.5 Fullscreen Toggle Button

A floating button at the top-right of the map container. Uses the **Fullscreen API** (`document.documentElement.requestFullscreen()` / `document.exitFullscreen()`).

```html
<button id="mapFullscreenBtn" class="map-control-btn" title="ขยายเต็มจอ">⛶</button>
```

```css
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
}
.map-control-btn:hover {
    background: #2a2a2a;
}
```

Positioned at `top: 12px; right: 12px;`.

### 5.6 Zoom-to-Fit Button

A floating button positioned below the fullscreen button. When clicked, calls `map.fitBounds(mcg.getBounds())` to zoom the map to encompass all currently visible (filtered) markers.

```html
<button id="mapFitBtn" class="map-control-btn" title="แสดงทั้งหมด">⊞</button>
```

Positioned at `top: 56px; right: 12px;`.

**Implementation:**
```js
document.getElementById('mapFitBtn').addEventListener('click', function () {
    if (mcg.getLayers().length > 0) {
        map.fitBounds(mcg.getBounds().pad(0.1));
    }
});
```

## 6. View & Controller Changes

### New Controller Method: `Admin::map()`

- Route: `GET /admin/map`
- Renders the `admin/map` view.

### New Controller Method: `Admin::mapData()`

- Route: `GET /admin/map-data`
- Queries `MemberModel` joined with `people` table (for contact info).
- Returns JSON response with members having non-empty `geo_location`.
- Each member includes: `id`, `shop_name`, `member_code`, `geo_location`, `store_photo`, `shop_telephone`.

### New View: `app/Views/admin/map.php`

- Full HTML page with Leaflet CSS/JS and marker cluster CSS/JS.
- Inline JavaScript:
  - Fetches JSON from `/admin/map-data` on page load.
  - Initializes Leaflet map centered on Khon Kaen.
  - Adds markers with clustering from the fetched data.
  - Renders custom popups for each pin showing photo, shop name, member code.
  - Implements search filter functionality.
  - Implements fullscreen toggle.
  - Implements zoom-to-fit button.
- Inline CSS for map sizing, popup styling, search bar positioning, and all custom controls.

### Route Changes (`app/Config/Routes.php`)

Add inside the `admin` route group:
```php
$routes->get('map', 'Admin::map');
$routes->get('map-data', 'Admin::mapData');
```

### Template Changes (`app/Views/admin/index.php`)

Add a "แสดงแผนที่" button next to the existing "+ เพิ่มสมาชิก" button:
```html
<a href="/admin/map" class="button is-warning is-light">แสดงแผนที่</a>
```

## 7. Language & Styling

- **Language:** Thai (ภาษาไทย) for all UI labels, matching the rest of the admin panel.
- **Theme:** Dark theme consistent with existing admin pages (`theme-bg`, `theme-card`, `has-text-warning` for accents).
- **Color palette (from project theme):**
  - Background: `#0f0f0f` (body), `#1b1b1b` (card)
  - Accent / warnings: `#ffcc00` (also `#ffdd57` for hover variants)
  - Text: `#e0e0e0` (light), `#ccc` / `#aaa` (dim), `#666` / `#7a7a7a` (muted)
  - Borders: `#3a3a5c` (default), `#ffcc00` (accent)
  - Cluster colors: Default `MarkerCluster.Default.css` uses green/orange/red circles. These are overridden to use the yellow `#ffcc00` accent for consistency with the admin theme:

```css
/* Override marker cluster colors to match admin yellow theme */
.marker-cluster-small  { background-color: rgba(255, 204, 0, 0.3); }
.marker-cluster-small div  { background-color: #ffcc00; }
.marker-cluster-medium { background-color: rgba(255, 204, 0, 0.4); }
.marker-cluster-medium div { background-color: #ffcc00; }
.marker-cluster-large  { background-color: rgba(255, 204, 0, 0.5); }
.marker-cluster-large div  { background-color: #ffcc00; }
.marker-cluster { color: #1b1b1b; font-weight: 700; }
```

### 7.1 Responsive Breakpoints

| Breakpoint | Target | Map adjustments |
|---|---|---|
| `≤ 768px` | Mobile / small tablets | Search bar width reduced to `140px`; header buttons stack vertically; map height reduced from `calc(100vh - 100px)` to `calc(100vh - 80px)`; control buttons `width: 32px; height: 32px`; popup `min-width` removed (auto-width) |
| `769px – 1023px` | Tablets (portrait) | Default layout; search bar `width: 180px` |
| `≥ 1024px` | Desktops / tablets (landscape) | Full layout; search bar `width: 220px`; max popup width `320px` |
| `≥ 1400px` | Wide screens | Search bar `width: 260px`; max popup width `360px` |

**CSS media query pattern (matching existing project convention):**
```css
@media screen and (max-width: 768px) {
    .map-search { max-width: 200px; }
    .map-search input { width: 140px; }
    .map-control-btn { width: 32px; height: 32px; font-size: 1rem; }
    #mapFullscreenBtn { right: 8px; top: 8px; }
    #mapFitBtn { right: 8px; top: 48px; }
    .map-popup { flex-direction: column; text-align: center; }
    .map-popup-photo { width: 50px; height: 50px; }
}
```

### 7.2 Asset Versioning

All locally served CSS/JS assets in the map view should follow the same cache-busting pattern as the existing pages. Either:

- **Option A (inline, preferred):** Inline all styles and scripts directly in the view file — eliminates external file dependencies and simplifies deployment.
- **Option B (external):** Create dedicated `admin-map.css` and `admin-map.js` files in `public/assets/` and load them with the same `?v={filemtime}` versioning helper used in `register.php`.

### 7.3 CDN Integrity Attributes

The Leaflet and MarkerCluster CDN links used in `register.php` specify `integrity` (SRI) attributes. The map view should include the same SRI hashes for security. Use the exact same integrity values from the Leaflet CDN documentation. *If implementing with inline CSS/JS, CDN integrity is less critical since no external JS is loaded.*

### 7.4 Map Container & Header Layout

The map page includes a minimal header bar (consistent with admin theme) plus the map filling the remaining viewport:

```html
<section class="section map-page">
    <div class="container is-fluid">
        <!-- Header bar -->
        <div class="is-flex is-justify-content-space-between is-align-items-center mb-3">
            <div>
                <h1 class="title has-text-warning">แผนที่ร้านค้า</h1>
                <a href="/admin" class="has-text-grey is-size-7">&larr; กลับสู่หน้าจัดการ</a>
            </div>
            <div id="shopCount" class="has-text-grey is-size-7">กำลังโหลด…</div>
        </div>
        <!-- Map container -->
        <div id="map" class="map-container"></div>
    </div>
</section>
```

**Map container CSS:**
```css
.map-container {
    height: calc(100vh - 160px);
    min-height: 400px;
    border: 2px solid #ffcc00;
    border-radius: 10px;
    overflow: hidden;
    position: relative; /* For absolute-positioned controls */
}

@media screen and (max-width: 768px) {
    .map-container {
        height: calc(100vh - 120px);
        min-height: 300px;
    }
}
```

## 8. Edge Cases

- **No shops with geo_location:** The JSON endpoint returns an empty array `[]`. The map still renders (centered on Khon Kaen). The shop count shows "ไม่พบร้านค้าบนแผนที่" ("No shops on map") and the map container shows a centered notification "ไม่มีข้อมูลร้านค้าบนแผนที่" with a link back to `/admin`.
- **Invalid geo_location format:** When parsing each entry's `geo_location` field (format: `"lat,lng"`), split by `,`, parse both parts as floats. If either parse fails or values are out of range (lat: -90 to 90, lng: -180 to 180), skip that entry silently.
- **Single shop:** Map centers on Khon Kaen default. The marker cluster shows a single unclustered pin (the cluster plugin handles this automatically — no cluster circle rendered).
- **Loading state:** On page load, show a centered CSS spinner/loader (using a simple CSS animation with a yellow `#ffcc00` color) inside the map container. The shop count shows "กำลังโหลด…" ("Loading…"). When data arrives, update the shop count to "พบ X ร้านค้า" ("Found X shops") and replace the loader with the actual map.
- **Error state:** If the `fetch('/admin/map-data')` call fails (network error, HTTP 5xx), hide the loader, show a notification "เกิดข้อผิดพลาดในการโหลดข้อมูล" ("Error loading data") with a retry button.
- **Many shops (>500):** With `chunkedLoading: true` on the cluster group, the marker cluster plugin loads markers in chunks to avoid blocking the UI. This is sufficient for up to thousands of markers.
- **No photo available:** If `store_photo` is null/empty, omit the photo div from the popup. Add `.map-popup.no-photo` class to adjust padding.
- **Browser without Fullscreen API:** The fullscreen button should be hidden if `document.fullscreenEnabled` is false.

### 8.1 JSON Endpoint Contract (`GET /admin/map-data`)

### Request
```
GET /admin/map-data
Cookie: PHPSESSID=...  (Must be authenticated)
```

### Response (200 OK)
```json
[
    {
        "id": 1,
        "shop_name": "ร้านของชำบุญมี",
        "member_code": 1001,
        "geo_location": "16.441900,102.835000",
        "store_photo": "abc123.jpg",
        "shop_telephone": "081-234-5678",
        "address": "123 ถ.มิตรภาพ ต.ในเมือง"
    },
    ...
]
```

### Response (empty — no geo-located shops)
```json
[]
```

### Controller implementation notes

In `Admin::mapData()`:
1. Use `MemberModel` to select only members where `geo_location IS NOT NULL` and `geo_location != ''`.
2. Return only the needed fields (minimize response size).
3. Cast `member_code` to int, `id` to int.
4. Set response header `Content-Type: application/json`.
5. No pagination — return **all** matching members.

## 9. Implementation Order (Recommended Steps)

1. Add `map()` and `mapData()` methods to `app/Controllers/Admin.php`
2. Add routes to `app/Config/Routes.php`
3. Create `app/Views/admin/map.php` with inline CSS + JS
4. Add the "แสดงแผนที่" button to `app/Views/admin/index.php`
5. Test manually: visit `/admin/map` and verify pins render

## 10. File Changes Summary

| File | Action |
|---|---|
| `app/Controllers/Admin.php` | Add `map()` and `mapData()` methods |
| `app/Views/admin/map.php` | **New file** — map view page with inline CSS & JS |
| `app/Views/admin/index.php` | Add "แสดงแผนที่" button next to "+ เพิ่มสมาชิก" |
| `app/Config/Routes.php` | Add `map` and `map-data` routes inside the `admin` group |

## 11. Future Considerations (Out of Scope)

- Public-facing map page (not requested).
- Real-time updates via WebSockets.
- Route/direction display between shops.
- Heatmap visualization of shop density.
- GeoJSON export.
- Mobile app integration.
