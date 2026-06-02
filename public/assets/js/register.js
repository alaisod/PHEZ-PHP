// ── Leaflet Map ──────────────────────────────────────────────
const defaultLat = 16.4419;
const defaultLng = 102.8350;

const map = L.map('map').setView([defaultLat, defaultLng], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
}).addTo(map);

let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

const autoLocateBtn = document.getElementById('autoLocate');

function setGeoStatus(text, type) {
    type = type || 'warning';
    autoLocateBtn.textContent = text;
    autoLocateBtn.className = 'button is-' + type;
    if (type === 'warning') {
        autoLocateBtn.classList.add('is-light');
    }
}

function updateGeo(lat, lng) {
    const value = lat.toFixed(6) + ',' + lng.toFixed(6);
    document.getElementById('geoDisplay').value = value;
    document.getElementById('geoPreview').textContent = value;
    setGeoStatus('\u0E44\u0E14\u0E49\u0E1E\u0E34\u0E01\u0E31\u0E14\u0E41\u0E25\u0E49\u0E27', 'success');
}

function setMarker(latlng) {
    marker.setLatLng(latlng);
    updateGeo(latlng.lat, latlng.lng);
}

marker.on('dragend', function (event) {
    var pos = event.target.getLatLng();
    updateGeo(pos.lat, pos.lng);
});

map.on('click', function (event) { setMarker(event.latlng); });

function requestAutoLocate() {
    if (!navigator.geolocation) {
        setGeoStatus('\u0E44\u0E21\u0E48\u0E23\u0E2D\u0E07\u0E23\u0E31\u0E1A GPS', 'danger');
        return;
    }
    setGeoStatus('\u0E01\u0E33\u0E25\u0E31\u0E07\u0E14\u0E36\u0E07\u0E1E\u0E34\u0E01\u0E31\u0E14\u2026', 'warning');
    navigator.geolocation.getCurrentPosition(function (position) {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        map.setView([lat, lng], 16);
        setMarker({ lat: lat, lng: lng });
    }, function () {
        setGeoStatus('\u27F3 \u0E25\u0E2D\u0E07\u0E2D\u0E35\u0E01\u0E04\u0E23\u0E31\u0E07', 'danger');
    }, { enableHighAccuracy: true, timeout: 8000 });
}

// ── Map Modal ────────────────────────────────────────────────
var mapModal = document.getElementById('mapModal');
var openMap = document.getElementById('openMap');
var closeMap = document.getElementById('closeMap');
var confirmMap = document.getElementById('confirmMap');

function openMapModal() {
    mapModal.classList.add('is-active');
    setTimeout(function () {
        map.invalidateSize();
    }, 200);
}

function closeMapModal() {
    mapModal.classList.remove('is-active');
}

openMap.addEventListener('click', openMapModal);
closeMap.addEventListener('click', closeMapModal);
mapModal.querySelector('.modal-background').addEventListener('click', closeMapModal);
confirmMap.addEventListener('click', closeMapModal);

document.getElementById('autoLocate').addEventListener('click', requestAutoLocate);
window.addEventListener('load', requestAutoLocate);

// ── Store Photo ─────────────────────────────────────────────
const photoInput = document.getElementById('storePhotoInput');
const photoArea = document.getElementById('photoArea');
const photoPreview = document.getElementById('photoPreview');
const photoPlaceholder = document.getElementById('photoPlaceholder');
const photoOverlay = document.getElementById('photoOverlay');
const removePhotoBtn = document.getElementById('removePhoto');
const takePhotoBtn = document.getElementById('takePhotoBtn');
const choosePhotoBtn = document.getElementById('choosePhotoBtn');

function handlePhotoSelect(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        photoPreview.src = e.target.result;
        photoPreview.classList.remove('is-hidden');
        photoPlaceholder.classList.add('is-hidden');
        photoOverlay.classList.remove('is-hidden');
        photoArea.classList.add('has-image');
    };
    reader.readAsDataURL(file);
}

function removePhoto() {
    photoInput.value = '';
    photoPreview.src = '';
    photoPreview.classList.add('is-hidden');
    photoPlaceholder.classList.remove('is-hidden');
    photoOverlay.classList.add('is-hidden');
    photoArea.classList.remove('has-image');
}

photoInput.addEventListener('change', function () {
    if (this.files && this.files[0]) {
        handlePhotoSelect(this.files[0]);
    }
});

takePhotoBtn.addEventListener('click', function () {
    photoInput.removeAttribute('capture');
    photoInput.setAttribute('capture', 'environment');
    photoInput.click();
});

choosePhotoBtn.addEventListener('click', function () {
    photoInput.removeAttribute('capture');
    photoInput.click();
});

removePhotoBtn.addEventListener('click', removePhoto);

photoArea.addEventListener('click', function () {
    if (!photoPreview.classList.contains('is-hidden')) return;
    photoInput.removeAttribute('capture');
    photoInput.click();
});

// ── LIFF & Registration ──────────────────────────────────────
var liffReady = false;

function getCsrfToken() {
    var csrfInput = document.querySelector('input[name="csrf_test_name"]');
    return csrfInput ? csrfInput.value : '';
}

function disableForm() {
    var form = document.getElementById('registerForm');
    if (form) { form.classList.add('is-hidden'); }
    var already = document.getElementById('alreadyRegistered');
    if (already) { already.classList.remove('is-hidden'); }
}

function showRegisteredInfo(member) {
    var info = 'Member Code: ' + member.member_code + ' | \u0E23\u0E49\u0E32\u0E19: ' + member.shop_name;
    document.getElementById('registeredInfo').textContent = info;
}

async function checkLineDuplicate(lineId) {
    try {
        var formData = new URLSearchParams();
        formData.append('line_id', lineId);
        formData.append('csrf_test_name', getCsrfToken());
        var response = await fetch('/register/check-line', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString(),
        });
        if (!response.ok) {
            return false;
        }
        var data = await response.json();
        if (data.exists && data.member) {
            disableForm();
            showRegisteredInfo(data.member);
            return true;
        }
    } catch (error) {
        console.error('Check line duplicate failed', error);
    }
    return false;
}

async function initLiff() {
    var liffId = '2010178930-H6pNBOQl';
    if (typeof liff === 'undefined') {
        return false;
    }
    try {
        await liff.init({ liffId: liffId });
        if (!liff.isLoggedIn()) {
            if (liff.isInClient()) {
                liff.login();
                return false;
            }
            return false;
        }
        var profile = await liff.getProfile();
        document.getElementById('lineId').value = profile.userId;
        document.getElementById('lineDisplayName').value = profile.displayName || '';
        if (profile.displayName) {
            document.getElementById('profileName').textContent = profile.displayName;
        }
        if (profile.pictureUrl) {
            document.getElementById('profileImage').src = profile.pictureUrl;
        }
        document.getElementById('isLiff').value = '1';
        liffReady = true;
        if (!window._showWelcome) {
            await checkLineDuplicate(profile.userId);
        }
        return true;
    } catch (error) {
        console.error('LIFF init failed', error);
        return false;
    }
}

if (window._showWelcome) {
    var welcomeSection = document.getElementById('welcomeSection');
    if (welcomeSection) {
        welcomeSection.scrollIntoView({ behavior: 'smooth' });
    }
}

function closeApp() {
    if (typeof liff !== 'undefined' && liff.isInClient()) {
        liff.closeWindow();
    } else {
        window.location.href = '/';
    }
}

document.getElementById('closeLiff').addEventListener('click', closeApp);
document.getElementById('closeRegistered').addEventListener('click', closeApp);

async function sendLineMessage() {
    if (!window._showWelcome || !window._member || !window._member.line_id || typeof liff === 'undefined') {
        return;
    }
    if (!liffReady) {
        return;
    }
    try {
        if (!liff.isInClient()) {
            return;
        }
        var text = '\u0E2A\u0E21\u0E31\u0E04\u0E23\u0E2A\u0E21\u0E32\u0E0A\u0E34\u0E01\u0E2A\u0E33\u0E40\u0E23\u0E47\u0E08\nMember Code: ' + window._member.member_code + '\n\u0E23\u0E49\u0E32\u0E19: ' + window._member.shop_name + '\n\u0E1C\u0E39\u0E49\u0E15\u0E34\u0E14\u0E15\u0E48\u0E2D: ' + window._member.person_name + '\n\u0E40\u0E1A\u0E2D\u0E23\u0E4C\u0E42\u0E17\u0E23: ' + window._member.telephone;
        await liff.sendMessages([{ type: 'text', text: text }]);
    } catch (error) {
        console.error('Send message failed', error);
    }
}

(async function () {
    await initLiff();
    await sendLineMessage();
})();
