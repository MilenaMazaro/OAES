<?php /* SIIM • OAEs + Alertas (tags + cards 2x2 + ícones nos pinos + área da OAE) */ ?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Navbar verde (tom enviado) */
        .navbar-green{ background:#3F5660 !important; }
        .navbar-green .navbar-brand{ color:#fff; }
        .navbar-green .btn-outline-light{ color:#fff; border-color:#fff; }
        .navbar-green .btn-outline-light:hover,
        .navbar-green .btn-outline-light.active{ color:#3F5660; background:#fff; border-color:#fff; }

        /* Botão laranja */
        .btn-orange{
            --bs-btn-color:#fff; --bs-btn-bg:#ff7a00; --bs-btn-border-color:#ff7a00;
            --bs-btn-hover-bg:#e56e00; --bs-btn-hover-border-color:#e56e00;
            --bs-btn-active-bg:#cc6200; --bs-btn-active-border-color:#cc6200;
            --bs-btn-focus-shadow-rgb:255,122,0;
        }

        :root { --nav-h:56px; }
        html, body { height:100%; }
        body { overflow:hidden; background:#f6f7fb; }
        #map { height: calc(100vh - var(--nav-h)); }

        /* ===== Painel (direita) ===== */
        #filter-panel{
            position:absolute; z-index:1000; right:1rem; top:7.8rem;
            width:min(420px, calc(100% - 2rem));
            max-height: calc(100vh - 9rem);
            overflow: hidden;
        }
        #filter-panel .card-body{
            max-height: calc(100vh - 12rem);
            overflow: auto;
            padding-bottom: .75rem;
        }
        #filter-panel .btn-min{ width:2.25rem; height:2.25rem; line-height:1; padding:0; font-weight:700; }
        #filter-panel.collapsed{ width:auto; background:transparent; box-shadow:none; border:0; }
        #filter-panel.collapsed .card-header{ background:transparent; border:0; padding:0; }
        #filter-panel.collapsed .card-body{ display:none; }
        #filter-panel.collapsed .filter-title{ display:none; }
        #filter-panel.collapsed .btn-min{
            width:42px; height:42px; border-radius:50%; background:#fff; border:1px solid rgba(0,0,0,.15);
            box-shadow:0 4px 12px rgba(0,0,0,.15);
        }
        #filter-panel.disabled .card-body{ opacity:.45; pointer-events:none; }

        .tiny-dot{ width:10px;height:10px;border-radius:2px;display:inline-block }
        .status-muted{ color:#6b7280; }

        /* ===== Campo único “tags” ===== */
        .chips-control{
            display:flex; align-items:center; flex-wrap:wrap; gap:.25rem;
            width:100%;
            min-height:38px;
            padding:.25rem .5rem;
            background:#fff;
            border:1px solid #ced4da; border-radius:.375rem;
        }
        .chips-control:focus-within{ box-shadow:0 0 0 .25rem rgba(13,110,253,.25); border-color:#86b7fe; }
        .chip{
            display:inline-flex; align-items:center; gap:.35rem;
            background:#e9f2ff; color:#0b5ed7; border:1px solid #cfe2ff;
            padding:.15rem .5rem; border-radius:999px; font-size:.85rem;
        }
        .chip .x{ cursor:pointer; font-weight:700; line-height:1; }
        .chips-input{ flex:1 1 140px; min-width:120px; border:0; outline:0; height:30px; }

        /* ===== Cards de alertas 2×2 ===== */
        .alerts-grid{ display:grid; grid-template-columns: 1fr 1fr; gap:.5rem; }
        @media (max-width: 480px){ .alerts-grid{ grid-template-columns: 1fr; } }

        .alert-card{
            border:0; border-radius:.55rem;
            display:flex; flex-direction:column; align-items:flex-start; justify-content:space-between;
            padding:.55rem .6rem; min-height:92px; box-shadow:0 2px 10px rgba(0,0,0,.08);
        }
        .alert-title{ display:flex; align-items:center; gap:.35rem; font-weight:600; font-size:.85rem; color:#111; }
        .alert-ico{ font-size:1.05rem; line-height:1; color:#111; }
        .alert-count{ font-size:1.15rem; font-weight:800; color:#111; }
        .alert-switch .form-check-label{ font-size:.75rem; color:#111; opacity:.9; }
        .alert-switch .form-check-input{ transform:scale(.9); }

        .card-accident   { background:#ffa726; color:#111; }
        .card-hazard     { background:#ef5350; color:#111; }
        .card-jam        { background:#42a5f5; color:#111; }
        .card-roadclosed { background:#c62828; color:#111; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-green" style="height:var(--nav-h);">
    <div class="container-fluid">
        <div class="d-flex align-items-center gap-3">
            <span class="navbar-brand mb-0 h1">SIIM</span>
            <div class="btn-group" role="group" aria-label="Camadas">
                <button id="toggle-oaes"   class="btn btn-outline-light btn-sm active" data-on="1">OAEs</button>
                <button id="toggle-alerts" class="btn btn-outline-light btn-sm active" data-on="1">Alertas</button>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button id="btn-clear" class="btn btn-orange btn-sm">Limpar desenhos</button>
        </div>
    </div>
</nav>

<div id="map"></div>

<!-- Painel lateral -->
<div id="filter-panel" class="card shadow">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="filter-title fw-semibold">Obras de Arte Especiais (OAEs)</span>
        <div class="d-flex gap-2">
            <button id="btn-clear-filter" class="btn btn-outline-secondary btn-sm">Limpar Filtro</button>
            <button id="filter-collapse" class="btn btn-light btn-sm btn-min" title="Minimizar">–</button>
        </div>
    </div>

    <div class="card-body">
        <div class="small text-muted mb-2">
            Selecione uma ou mais OAEs para filtrar os alertas do Waze em um raio de 500m.
        </div>

        <!-- Campo único: tags + input com sugestões -->
        <div class="mb-3">
            <div id="oae-ms" class="chips-control">
                <div id="oae-chips"></div>
                <input id="oae-input" class="chips-input" placeholder="Digite para buscar uma OAE..." list="oaes-list" autocomplete="off">
                <datalist id="oaes-list"></datalist>
            </div>
        </div>

        <!-- Tipos + colapso -->
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">Tipos:</span>
                <button id="btn-all"  type="button" class="btn btn-sm btn-primary">Todos</button>
                <button id="btn-none" type="button" class="btn btn-sm btn-outline-secondary">Nenhum</button>
            </div>
            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="collapse" data-bs-target="#typesBox" aria-expanded="false" aria-controls="typesBox">
                Mostrar tipos <span id="types-badge" class="badge text-bg-secondary ms-1">0</span>
            </button>
        </div>

        <!-- Tipos (legenda+checkbox) — começa fechado -->
        <div id="typesBox" class="collapse">
            <div id="oae-types" class="d-grid gap-2 mb-3"></div>
        </div>

        <!-- Cards de Alertas -->
        <div id="alerts-summary" class="mb-3 d-none">
            <h6 class="mb-2">Alertas</h6>
            <div class="alerts-grid">
                <div class="alert-card card-accident" data-cat="ACCIDENT">
                    <div class="alert-title"><i class="bi bi-car-front-fill alert-ico"></i><span>Acidente</span></div>
                    <div class="alert-count"><span class="count-num">0</span> evento(s)</div>
                    <div class="form-check form-switch m-0 alert-switch">
                        <input class="form-check-input cat-toggle" type="checkbox" checked>
                        <label class="form-check-label">Mostrar no mapa</label>
                    </div>
                </div>
                <div class="alert-card card-hazard" data-cat="HAZARD">
                    <div class="alert-title"><i class="bi bi-exclamation-triangle-fill alert-ico"></i><span>Perigo</span></div>
                    <div class="alert-count"><span class="count-num">0</span> evento(s)</div>
                    <div class="form-check form-switch m-0 alert-switch">
                        <input class="form-check-input cat-toggle" type="checkbox" checked>
                        <label class="form-check-label">Mostrar no mapa</label>
                    </div>
                </div>
                <div class="alert-card card-jam" data-cat="JAM">
                    <div class="alert-title"><i class="bi bi-cone-striped alert-ico"></i><span>Congestionamento</span></div>
                    <div class="alert-count"><span class="count-num">0</span> evento(s)</div>
                    <div class="form-check form-switch m-0 alert-switch">
                        <input class="form-check-input cat-toggle" type="checkbox" checked>
                        <label class="form-check-label">Mostrar no mapa</label>
                    </div>
                </div>
                <div class="alert-card card-roadclosed" data-cat="ROAD_CLOSED">
                    <div class="alert-title"><i class="bi bi-slash-circle-fill alert-ico"></i><span>Fechamento de Via</span></div>
                    <div class="alert-count"><span class="count-num">0</span> evento(s)</div>
                    <div class="form-check form-switch m-0 alert-switch">
                        <input class="form-check-input cat-toggle" type="checkbox" checked>
                        <label class="form-check-label">Mostrar no mapa</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados do Waze -->
        <div class="card border-0 shadow-sm mb-2">
            <div class="card-header bg-info text-white py-2">
                <div class="fw-semibold">Dados do Waze</div>
                <div id="waze-updated" class="small">Atualizado: —</div>
            </div>
            <div class="card-body p-3">
                <div class="fw-semibold mb-2">Resumo do Trânsito</div>
                <ul id="traffic-summary" class="list-group list-group-flush small"></ul>
            </div>
        </div>

        <div id="status" class="small status-muted mt-2"></div>
    </div>
</div>

<script>
    /* ===== Estado ===== */
    var map, info;
    var oaeLayers = [];
    var typePolylines = {};
    var alertMarkers = [];
    var markersByCat = { ACCIDENT:[], HAZARD:[], JAM:[], ROAD_CLOSED:[] };
    var layersEnabled = { oaes:true, alerts:true };
    var typeState = {};
    var selectedOAE = [];        // nomes selecionados (tags)
    var allOaeNames = [];        // para sugestões

    // NOVO: retângulo da área da OAE selecionada (~500m)
    var oaeAreaRect = null;

    // NOVO: estilo (cor + glifo) para os pinos por categoria
    var CAT_STYLE = {
        ACCIDENT:     { fill:'#ffa726', glyph:'🚗' },
        HAZARD:       { fill:'#ef5350', glyph:'⚠'  },
        JAM:          { fill:'#42a5f5', glyph:'🚦' },
        ROAD_CLOSED:  { fill:'#c62828', glyph:'⛔' }
    };

    var TYPE_COLORS = {
        "Ponte":"#e53935","Viaduto":"#fb8c00","Passarela":"#6d4c41","Túnel":"#8e24aa",
        "Trincheira / Passagem Inferior":"#1e88e5","Passagem Subterrânea":"#3949ab",
        "Pontilhão":"#00acc1","Complexo Viário":"#43a047","Sem tipo":"#1976d2"
    };

    function statusEl(){ return document.getElementById('status'); }
    function setStatus(t){ statusEl().textContent = t||''; }

    /* ===== Mapa ===== */
    function initMap(){
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat:-23.55, lng:-46.63}, zoom: 12,
            mapTypeControl: true, streetViewControl: false, fullscreenControl: true
        });
        info = new google.maps.InfoWindow();

        document.getElementById('filter-collapse').onclick =
            function(){ document.getElementById('filter-panel').classList.toggle('collapsed'); };

        document.getElementById('toggle-oaes').onclick = function(ev){
            var btn = ev.currentTarget;
            btn.classList.toggle('active');
            layersEnabled.oaes = btn.classList.contains('active');
            btn.setAttribute('data-on', layersEnabled.oaes ? '1':'0');
            updateOAEsVisibility();
            document.getElementById('filter-panel').classList.toggle('disabled', !layersEnabled.oaes);
        };
        document.getElementById('toggle-alerts').onclick = function(ev){
            var btn = ev.currentTarget;
            btn.classList.toggle('active');
            layersEnabled.alerts = btn.classList.contains('active');
            btn.setAttribute('data-on', layersEnabled.alerts ? '1':'0');
            updateAlertsVisibility();
        };

        document.getElementById('btn-clear').onclick = clearAll;
        document.getElementById('btn-all').onclick  = function(){ setAllTypes(true); };
        document.getElementById('btn-none').onclick = function(){ setAllTypes(false); };
        document.getElementById('btn-clear-filter').onclick = clearOaeFilter;

        // Entrada do multi-select (uma caixa só)
        var input = document.getElementById('oae-input');
        input.addEventListener('keydown', function(e){
            if (e.key === 'Enter') { e.preventDefault(); tryAddOAE(input.value); }
        });
        input.addEventListener('change', function(){ tryAddOAE(input.value); });

        fillTrafficSummary();
        updateWazeUpdated();
        fetchOAEs();
    }

    /* ===== OAEs ===== */
    function fetchOAEs(){
        setStatus('Carregando OAEs...');
        fetch('api/oaes.php?mock=1').then(function(r){ return r.json(); }).then(function(fc){
            renderOAEs(fc);
            setStatus('OAEs carregadas: ' + oaeLayers.length + '. Use o campo acima para selecionar.');
        }).catch(function(e){
            console.error(e); setStatus('Falha ao carregar OAEs (veja o console).');
        });
    }

    function renderOAEs(fc){
        if (!fc || !fc.features || !fc.features.length) return;

        var presentTypes = {};
        fc.features.forEach(function(f){
            var t = (f.properties && (f.properties.oae_type || f.properties.type)) || 'Sem tipo';
            presentTypes[t] = true;
        });
        buildTypeFilter(Object.keys(presentTypes).sort());

        oaeLayers = [];
        fc.features.forEach(function(f){
            if (!f.geometry || f.geometry.type!=='LineString') return;
            var coords = f.geometry.coordinates.map(function(x){ return {lat:x[1], lng:x[0]}; });
            var oaeName = (f.properties && (f.properties.oae_name || f.properties.name || f.properties.street)) || 'OAE';
            var oaeType = (f.properties && (f.properties.oae_type || f.properties.type)) || 'Sem tipo';
            var color   = TYPE_COLORS[oaeType] || '#1976d2';

            var pl = new google.maps.Polyline({
                path: coords, strokeColor: color, strokeWeight: 4, strokeOpacity: 0.9,
                map: (layersEnabled.oaes && typeState[oaeType]!==false) ? map : null
            });
            pl.__oaeName = oaeName; pl.__oaeType = oaeType;
            if (!typePolylines[oaeType]) typePolylines[oaeType] = [];
            typePolylines[oaeType].push(pl);
            oaeLayers.push(pl);

            pl.addListener('click', function(){ addOAE(oaeName, true); });
        });

        fillOaeSuggestions();
    }

    /* ===== Tipos ===== */
    function buildTypeFilter(types){
        var box = document.getElementById('oae-types'); box.innerHTML = '';
        types.forEach(function(t){
            typeState[t] = true;
            var color = TYPE_COLORS[t] || '#1976d2';
            var id = 't_' + btoa(t).replace(/=/g,'');
            var row = document.createElement('div'); row.className = 'form-check d-flex align-items-center gap-2';
            row.innerHTML =
                '<input class="form-check-input" type="checkbox" id="'+id+'" checked>' +
                '<span class="tiny-dot" style="background:'+color+'"></span>' +
                '<label class="form-check-label" for="'+id+'">'+t+'</label>';
            box.appendChild(row);
            row.querySelector('input').addEventListener('change', function(ev){
                toggleType(t, ev.target.checked); updateTypesBadge();
            });
        });
        updateTypesBadge();
    }
    function toggleType(type, on){
        typeState[type] = on;
        (typePolylines[type]||[]).forEach(function(pl){ pl.setMap(layersEnabled.oaes && on ? map : null); });
    }
    function setAllTypes(on){
        for (var k in typeState){ if (!typeState.hasOwnProperty(k)) continue;
            typeState[k]=on; var id='t_'+btoa(k).replace(/=/g,''); var cb=document.getElementById(id); if (cb) cb.checked=on; }
        updateOAEsVisibility(); updateTypesBadge();
    }
    function updateOAEsVisibility(){
        for (var t in typePolylines){ if (!typePolylines.hasOwnProperty(t)) continue;
            (typePolylines[t]||[]).forEach(function(pl){ pl.setMap(layersEnabled.oaes && typeState[t] ? map : null); });
        }
    }
    function updateTypesBadge(){
        var n=0; for (var k in typeState){ if (typeState.hasOwnProperty(k) && typeState[k]) n++; }
        var b = document.getElementById('types-badge'); if (b) b.textContent = n;
    }

    /* ===== Multi-select por tags ===== */
    function fillOaeSuggestions(){
        var namesMap = {}; allOaeNames = [];
        oaeLayers.forEach(function(pl){ namesMap[pl.__oaeName]=true; });
        for (var n in namesMap){ if (namesMap.hasOwnProperty(n)) allOaeNames.push(n); }
        allOaeNames.sort(function(a,b){ return a.localeCompare(b); });

        var dl = document.getElementById('oaes-list'); dl.innerHTML='';
        allOaeNames.forEach(function(n){ var o=document.createElement('option'); o.value=n; dl.appendChild(o); });
    }

    function tryAddOAE(value){
        var name = (value||'').trim();
        if (!name) return;
        var found = null; for (var i=0;i<allOaeNames.length;i++){ if (allOaeNames[i].toLowerCase()===name.toLowerCase()){ found=allOaeNames[i]; break; } }
        addOAE(found || name, true);
        document.getElementById('oae-input').value = '';
    }

    // NOVO: desenha retângulo ~500m em torno da OAE
    function drawAreaForPolyline(pl, meters){
        meters = meters || 500;
        var path = pl.getPath();
        if (!path || path.getLength()===0) return;
        var b = new google.maps.LatLngBounds();
        for (var i=0;i<path.getLength();i++) b.extend(path.getAt(i));
        var center = b.getCenter();

        var n = google.maps.geometry.spherical.computeOffset(center, meters,   0);
        var s = google.maps.geometry.spherical.computeOffset(center, meters, 180);
        var e = google.maps.geometry.spherical.computeOffset(center, meters,  90);
        var w = google.maps.geometry.spherical.computeOffset(center, meters, 270);

        var rectBounds = { north: n.lat(), south: s.lat(), east: e.lng(), west: w.lng() };
        if (oaeAreaRect) oaeAreaRect.setMap(null);
        oaeAreaRect = new google.maps.Rectangle({
            bounds: rectBounds,
            strokeColor: '#e53935', strokeOpacity: 0.85, strokeWeight: 2,
            fillColor: '#e53935', fillOpacity: 0.18,
            map: map
        });
    }

    function addOAE(name, zoom){
        for (var i=0;i<selectedOAE.length;i++){ if (selectedOAE[i]===name) return; }
        selectedOAE.push(name);
        renderChips();

        var pl = findPolylineByName(name);
        if (pl){
            if (zoom) fitToSelectedOAEs();
            drawAreaForPolyline(pl, 500); // NOVO: desenha área ao selecionar
        }

        fetchAlertsForOAEs(selectedOAE);
    }

    function removeOAE(name){
        selectedOAE = selectedOAE.filter(function(n){ return n!==name; });
        renderChips();
        fitToSelectedOAEs();
        fetchAlertsForOAEs(selectedOAE);
    }

    function renderChips(){
        var box = document.getElementById('oae-chips'); box.innerHTML='';
        selectedOAE.forEach(function(n){
            var chip = document.createElement('span'); chip.className='chip';
            chip.innerHTML = '<span>'+n+'</span><span class="x" title="Remover">&times;</span>';
            chip.querySelector('.x').onclick = function(){ removeOAE(n); };
            box.appendChild(chip);
        });
    }

    function fitToSelectedOAEs(){
        if (!selectedOAE.length) return;
        var b = new google.maps.LatLngBounds(); var any=false;
        selectedOAE.forEach(function(n){
            var pl = findPolylineByName(n);
            if (pl){ var path=pl.getPath(); for (var i=0;i<path.getLength();i++) { b.extend(path.getAt(i)); any=true; } pl.setOptions({strokeWeight:6, strokeOpacity:1}); }
        });
        if (any) map.fitBounds(b);
    }

    function findPolylineByName(name){
        for (var i=0;i<oaeLayers.length;i++){ if (oaeLayers[i].__oaeName===name) return oaeLayers[i]; }
        return null;
    }

    function clearOaeFilter(){
        selectedOAE = [];
        renderChips();
        clearAlerts();
        if (oaeAreaRect){ oaeAreaRect.setMap(null); oaeAreaRect=null; } // NOVO: limpa área
        setStatus('Filtro limpo. Selecione OAEs para ver alertas.');
    }

    /* ===== ALERTAS (multi-OAE) ===== */
    function eqName(a,b){ return String(a||'').replace(/^\s+|\s+$/g,'').toLowerCase() === String(b||'').replace(/^\s+|\s+$/g,'').toLowerCase(); }

    function fetchAlertsForOAEs(names){
        clearAlerts();
        if (!names || !names.length){ updateSummaryCounts(); return Promise.resolve(0); }

        var reqs = names.map(function(n){
            return fetch('api/alerts.php?mock=1&oae_name='+encodeURIComponent(n))
                .then(function(r){ return r.json(); })
                .catch(function(){ return {type:'FeatureCollection',features:[]}; });
        });
        return Promise.all(reqs).then(function(arr){
            var all = { type:'FeatureCollection', features:[] };
            arr.forEach(function(data){
                var fc = normalizeAlertsToFC(data, null);
                all.features = all.features.concat(fc.features||[]);
            });
            var count = renderAlerts(all);
            setStatus('OAEs selecionadas: '+names.length+' • Alertas: '+count);
            updateWazeUpdated();
            return count;
        });
    }

    function normalizeAlertsToFC(data, nameFilter){
        var features = [];
        if (data && data.type && /featurecollection/i.test(data.type) && data.features && data.features.length) {
            features = data.features.filter(function(f){
                if (!f.properties) return false;
                if (!nameFilter) return (f.geometry && f.geometry.type==='Point');
                var n = f.properties.oae_name || f.properties.name || '';
                return (f.geometry && f.geometry.type==='Point') && (nameFilter ? eqName(n, nameFilter) : true);
            });
            return { type:'FeatureCollection', features: features };
        }
        if (data && data.alerts && data.alerts.length) {
            data.alerts.forEach(function(a){
                if (!a.point || !a.point.geometry || a.point.geometry.type!=='Point') return;
                var n = a.name || (a.point.properties && a.point.properties.name) || '';
                if (nameFilter && !eqName(n, nameFilter)) return;
                features.push({ type:'Feature', properties:{
                        name:n, type:a.type||null, alert_type:a.alert_type||null, street:a.street||null, date:a.date||null, hour:a.hour||null
                    }, geometry:a.point.geometry });
            });
            return { type:'FeatureCollection', features:features };
        }
        if (data && data.jams && data.jams.length) {
            data.jams.forEach(function(a){
                if (!a.point || !a.point.geometry || a.point.geometry.type!=='Point') return;
                var n = a.name || (a.point.properties && a.point.properties.name) || '';
                if (nameFilter && !eqName(n, nameFilter)) return;
                features.push({ type:'Feature', properties:{
                        name:n, type:a.type||null, alert_type:a.alert_type||'JAM', street:a.street||null, date:a.date||null, hour:a.hour||null
                    }, geometry:a.point.geometry });
            });
            return { type:'FeatureCollection', features:features };
        }
        return { type:'FeatureCollection', features:[] };
    }

    function resetMarkersByCat() {
        for (var k in markersByCat){
            if (!markersByCat.hasOwnProperty(k)) continue;
            markersByCat[k].forEach(function(m){ m.setMap(null); });
            markersByCat[k] = [];
        }
    }
    function catKeyFrom(type) {
        if (!type) return 'JAM';
        var t = String(type).toUpperCase();
        if (t.indexOf('ACCIDENT')>=0) return 'ACCIDENT';
        if (t.indexOf('ROAD_CLOSED')>=0 || t.indexOf('ROAD_CLOSURE')>=0) return 'ROAD_CLOSED';
        if (t.indexOf('HAZARD')>=0) return 'HAZARD';
        if (t.indexOf('JAM')>=0) return 'JAM';
        return 'JAM';
    }
    function bindSummaryToggles() {
        var sws = document.querySelectorAll('#alerts-summary .cat-toggle');
        for (var i=0;i<sws.length;i++){
            sws[i].onchange = function(ev){
                var card = ev.currentTarget.closest('[data-cat]');
                var cat = card.getAttribute('data-cat');
                var on = ev.currentTarget.checked && layersEnabled.alerts;
                (markersByCat[cat] || []).forEach(function(m){ m.setMap(on ? map : null); });
            };
        }
    }
    function updateSummaryCounts() {
        var box = document.getElementById('alerts-summary');
        var empty = true;
        for (var k in markersByCat){ if (markersByCat.hasOwnProperty(k) && markersByCat[k].length) { empty=false; break; } }
        box.classList.toggle('d-none', empty);
        var cards = box.querySelectorAll('[data-cat]');
        for (var i=0;i<cards.length;i++){
            var cat = cards[i].getAttribute('data-cat');
            var n = (markersByCat[cat] || []).length;
            cards[i].querySelector('.count-num').textContent = n;
        }
    }

    // NOVO: gera ícone (SVG) com bolinha + glifo
    function makeGlyphIcon(fill, glyph){
        var svg =
            "<svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 24 24'>" +
            "<circle cx='12' cy='12' r='9' fill='"+fill+"' stroke='#333' stroke-width='1'/>" +
            "<text x='12' y='15' text-anchor='middle' font-size='12' fill='#111' " +
            "font-family='Segoe UI Emoji, Apple Color Emoji, Noto Color Emoji, Arial, sans-serif'>"+ glyph +"</text>" +
            "</svg>";
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(22,22),
            anchor: new google.maps.Point(11,11)
        };
    }

    function renderAlerts(fc){
        if (!fc || !fc.features) return 0;
        resetMarkersByCat();

        fc.features.forEach(function(f){
            if (!f.geometry || f.geometry.type!=='Point') return;
            var lng = f.geometry.coordinates[0], lat = f.geometry.coordinates[1];
            var p = f.properties || {};
            var cat = catKeyFrom(p.alert_type || p.type);

            var sty = CAT_STYLE[cat] || { fill:'#ffb300', glyph:'•' };
            var icon = makeGlyphIcon(sty.fill, sty.glyph);

            var m = new google.maps.Marker({
                position: {lat:lat, lng:lng},
                icon: icon,
                zIndex: 100,
                map: layersEnabled.alerts ? map : null
            });
            m.addListener('click', function(){
                var title = (p.alert_type || p.type || 'Alerta').replace(/_/g,' ');
                var when  = [p.date, p.hour].filter(Boolean).join(' ');
                var street= p.street || '';
                info.setContent('<div><b>'+title+'</b>'+ (street?'<br>'+street:'') + (when?'<br><small>'+when+'</small>':'') +'</div>');
                info.open(map, m);
            });
            alertMarkers.push(m);
            markersByCat[cat].push(m);
        });

        updateSummaryCounts();
        bindSummaryToggles();
        updateAlertsVisibility();
        return alertMarkers.length;
    }
    function updateAlertsVisibility(){
        var box = document.getElementById('alerts-summary');
        function getOn(cat){
            var sw = box.querySelector('[data-cat="'+cat+'"] .cat-toggle');
            return layersEnabled.alerts && sw && sw.checked;
        }
        for (var cat in markersByCat){
            if (!markersByCat.hasOwnProperty(cat)) continue;
            markersByCat[cat].forEach(function(m){ m.setMap(getOn(cat) ? map : null); });
        }
    }

    /* ===== Util ===== */
    function clearAlerts(){
        alertMarkers.forEach(function(m){ m.setMap(null); });
        alertMarkers.length = 0;
        resetMarkersByCat();
        updateSummaryCounts();
    }
    function clearAll(){
        oaeLayers.forEach(function(l){ l.setMap(null); });   oaeLayers.length=0;
        for (var k in typePolylines){ if (typePolylines.hasOwnProperty(k)) delete typePolylines[k]; }
        if (oaeAreaRect){ oaeAreaRect.setMap(null); oaeAreaRect=null; } // NOVO
        clearOaeFilter();
        setStatus('Camadas limpas. Recarregue para buscar novamente.');
    }

    // “Dados do Waze” fake pra compor o layout
    function updateWazeUpdated(){
        var el = document.getElementById('waze-updated');
        var dt = new Date();
        function pad(n){ n=String(n); return n.length<2 ? '0'+n : n; }
        el.textContent = 'Atualizado: '+pad(dt.getDate())+'/'+pad(dt.getMonth()+1)+'/'+dt.getFullYear()+', '+pad(dt.getHours())+':'+pad(dt.getMinutes())+':'+pad(dt.getSeconds());
    }
    function fillTrafficSummary(){
        var levels = [
            {name:'Nível 1',  km:'—'},
            {name:'Nível 2',  km:'—'},
            {name:'Nível 3',  km:'—'},
            {name:'Nível 4',  km:'—'},
            {name:'Nível 5',  km:'—'}
        ];
        var ul = document.getElementById('traffic-summary'); ul.innerHTML='';
        levels.forEach(function(lv){
            var li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = '<span>'+lv.name+'</span><span class="badge rounded-pill text-bg-light">'+lv.km+'</span>';
            ul.appendChild(li);
        });
    }

    window.addEventListener('load', initMap);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- precisa de geometry para computeOffset -->
<script type="text/javascript" src="https://maps.google.com/maps/api/js?v=beta&libraries=visualization,drawing,geometry,places&key=AIzaSyCd3zT_keK2xr7T6ujvR3TvLj5c9u0PtsM&callback=Function.prototype"></script>
</body>
</html>
