<?php /* SIIM • OAEs + Alertas + Gerenciar Tipos/Indicadores + Monitoramento */ ?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root{ --nav-h:56px; --rail-w:64px; --panel-w:420px; --rail-bg:#3F5660; --orange:#ff7a00; }
        html,body{ height:100%; } body{ overflow:hidden; background:#f6f7fb; }
        .navbar-green{ background:#3F5660 !important; }
        .navbar-green .navbar-brand{ color:#fff; }
        .navbar-green .btn-outline-light{ color:#fff; border-color:#fff; }
        .navbar-green .btn-outline-light:hover,.navbar-green .btn-outline-light.active{ color:#3F5660; background:#fff; border-color:#fff; }
        .btn-orange{ --bs-btn-color:#fff; --bs-btn-bg:var(--orange); --bs-btn-border-color:var(--orange);
            --bs-btn-hover-bg:#e56e00; --bs-btn-hover-border-color:#e56e00;
            --bs-btn-active-bg:#cc6200; --bs-btn-active-border-color:#cc6200;
            --bs-btn-focus-shadow-rgb:255,122,0; }
        #map{ height:calc(100vh - var(--nav-h)); }

        .right-shell{ position:absolute; z-index:1000; top:var(--nav-h); right:0; height:calc(100vh - var(--nav-h)); display:flex; flex-direction:row-reverse; pointer-events:none; }
        .rail{ width:var(--rail-w); height:100%; background:var(--rail-bg); display:flex; flex-direction:column; align-items:center; gap:.75rem; padding:.75rem .5rem; box-shadow:-2px 0 8px rgba(0,0,0,.18); pointer-events:auto; }
        .rail .rail-btn{ width:44px; height:44px; border:0; border-radius:10px; display:flex; align-items:center; justify-content:center; background:#4c6570; color:#fff; font-size:1.25rem; transition:transform .15s, background .2s; }
        .rail .rail-btn:hover{ background:#5a7683; transform:translateY(-1px); }
        .rail .rail-btn.primary{ background:#0b8c7d; }
        .rail .rail-btn.primary:hover{ background:#0aa08f; }

        .sidepanel{ width:0; height:100%; overflow:hidden; background:#fff; border-left:1px solid rgba(0,0,0,.1); box-shadow:-10px 0 18px rgba(0,0,0,.12); pointer-events:auto; transition:width .35s cubic-bezier(.22,.61,.36,1); }
        .right-shell.open .sidepanel{ width:var(--panel-w); }
        @media(max-width:540px){ .right-shell.open .sidepanel{ width:min(95vw, var(--panel-w)); } }
        .sp-body{ opacity:0; transition:opacity .25s .12s ease; height:100%; overflow:auto; }
        .right-shell.open .sp-body{ opacity:1; }
        .sp-header{ display:flex; align-items:center; justify-content:space-between; padding:.6rem .85rem; border-bottom:1px solid rgba(0,0,0,.08); background:#f9fafb; }
        .sp-header .title{ font-weight:600; }

        .chips-control{ display:flex; align-items:center; flex-wrap:wrap; gap:.25rem; width:100%; min-height:38px; padding:.25rem .5rem; background:#fff; border:1px solid #ced4da; border-radius:.375rem; }
        .chip{ display:inline-flex; align-items:center; gap:.35rem; background:#e9f2ff; color:#0b5ed7; border:1px solid #cfe2ff; padding:.15rem .5rem; border-radius:999px; font-size:.85rem; }
        .chip .x{ cursor:pointer; font-weight:700; line-height:1; }
        .chips-input{ flex:1 1 140px; min-width:120px; border:0; outline:0; height:30px; }

        .tiny-dot{ width:10px; height:10px; border-radius:2px; display:inline-block; }
        .status-muted{ color:#6b7280; }

        .alerts-grid{ display:grid; grid-template-columns:1fr 1fr; gap:.5rem; }
        @media(max-width:480px){ .alerts-grid{ grid-template-columns:1fr; } }
        .alert-card{ border:0; border-radius:.55rem; display:flex; flex-direction:column; align-items:flex-start; justify-content:space-between; padding:.55rem .6rem; min-height:92px; box-shadow:0 2px 10px rgba(0,0,0,.08); }
        .alert-title{ display:flex; align-items:center; gap:.35rem; font-weight:600; font-size:.85rem; color:#111; }
        .alert-ico{ font-size:1.05rem; line-height:1; color:#111; }
        .alert-count{ font-size:1.15rem; font-weight:800; color:#111; }
        .alert-switch .form-check-label{ font-size:.75rem; color:#111; opacity:.9; }
        .alert-switch .form-check-input{ transform:scale(.9); }
        .card-accident{ background:#ffa726; color:#111; }
        .card-hazard{ background:#ef5350; color:#111; }
        .card-jam{ background:#42a5f5; color:#111; }
        .card-roadclosed{ background:#c62828; color:#111; }

        .sidepanel.disabled .sp-body{ opacity:.45; pointer-events:none; }
        .right-shell.open #btn-toggle i{ transform:rotate(180deg); transition:transform .25s ease; }

        .badge-traffic{ font-weight:700; border-radius:999px; padding:.25rem .5rem; min-width:72px; display:inline-block; text-align:center; color:#111; }
        .badge-t1{ background:#a5d6a7; } .badge-t2{ background:#ffe082; } .badge-t3{ background:#ffcc80; } .badge-t4{ background:#ef9a9a; } .badge-t5{ background:#ffab91; }

        .sp-body .panel-pad{ height:20px; } @media (max-height:740px){ .sp-body .panel-pad{ height:60px; } }
        .feed{ max-height:320px; overflow:auto; }
        .feed .item{ border-bottom:1px solid rgba(0,0,0,.075); padding:.5rem .25rem; }
        .feed .t{ font-weight:600; }
        .feed .d{ color:#6b7280; font-size:.85rem; }
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

<div id="right-shell" class="right-shell">
    <aside id="sidepanel" class="sidepanel">
        <div class="sp-header">
            <span id="sp-title" class="title">Obras de Arte Especiais (OAEs)</span>
            <div id="sp-actions" class="d-flex gap-2">
                <button id="btn-clear-filter" class="btn btn-outline-secondary btn-sm">Limpar Filtro</button>
            </div>
        </div>

        <div class="sp-body p-3">
            <!-- ===== Painel OAEs ===== -->
            <div id="panel-oae">
                <div class="small text-muted mb-2">Selecione uma ou mais OAEs para filtrar os alertas do Waze em um raio de 500m.</div>

                <div class="mb-3">
                    <div id="oae-ms" class="chips-control">
                        <div id="oae-chips"></div>
                        <input id="oae-input" class="chips-input" placeholder="Digite para buscar uma OAE..." list="oaes-list" autocomplete="off">
                        <datalist id="oaes-list"></datalist>
                    </div>
                </div>

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

                <div id="typesBox" class="collapse">
                    <div id="oae-types" class="d-grid gap-2 mb-3"></div>
                </div>

                <!-- Resumo dos alertas no mapa -->
                <div id="alerts-summary" class="mb-3 d-none">
                    <h6 class="mb-2">Alertas</h6>
                    <div class="alerts-grid">
                        <div class="alert-card card-accident" data-cat="ACCIDENT">
                            <div class="alert-title"><i class="bi bi-car-front-fill alert-ico"></i><span>Acidente</span></div>
                            <div class="alert-count"><span class="count-num">0</span> evento(s)</div>
                            <div class="form-check form-switch m-0 alert-switch"><input class="form-check-input cat-toggle" type="checkbox" checked><label class="form-check-label">Mostrar no mapa</label></div>
                        </div>
                        <div class="alert-card card-hazard" data-cat="HAZARD">
                            <div class="alert-title"><i class="bi bi-exclamation-triangle-fill alert-ico"></i><span>Perigo</span></div>
                            <div class="alert-count"><span class="count-num">0</span> evento(s)</div>
                            <div class="form-check form-switch m-0 alert-switch"><input class="form-check-input cat-toggle" type="checkbox" checked><label class="form-check-label">Mostrar no mapa</label></div>
                        </div>
                        <div class="alert-card card-jam" data-cat="JAM">
                            <div class="alert-title"><i class="bi bi-cone-striped alert-ico"></i><span>Congestionamento</span></div>
                            <div class="alert-count"><span class="count-num">0</span> evento(s)</div>
                            <div class="form-check form-switch m-0 alert-switch"><input class="form-check-input cat-toggle" type="checkbox" checked><label class="form-check-label">Mostrar no mapa</label></div>
                        </div>
                        <div class="alert-card card-roadclosed" data-cat="ROAD_CLOSED">
                            <div class="alert-title"><i class="bi bi-slash-circle-fill alert-ico"></i><span>Fechamento de Via</span></div>
                            <div class="alert-count"><span class="count-num">0</span> evento(s)</div>
                            <div class="form-check form-switch m-0 alert-switch"><input class="form-check-input cat-toggle" type="checkbox" checked><label class="form-check-label">Mostrar no mapa</label></div>
                        </div>
                    </div>
                </div>

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
                <div class="panel-pad"></div>
            </div>

            <!-- ===== Painel: Alertas por OAE (monitoramento) ===== -->
            <div id="panel-alerts" class="d-none">
                <div class="mb-2">
                    <div class="small text-muted">Selecione OAEs digitando ou clicando no mapa.</div>
                </div>

                <div class="mb-2">
                    <div id="mon-oae-ms" class="chips-control">
                        <div id="mon-oae-chips"></div>
                        <input id="mon-oae-input" class="chips-input" placeholder="Buscar OAE..." list="mon-oaes-list" autocomplete="off">
                        <datalist id="mon-oaes-list"></datalist>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="mon-category" class="form-label mb-1">Alertas</label>
                    <select id="mon-category" class="form-select">
                        <option value="">Selecione uma opção</option>
                        <option value="ACCIDENT">Acidente</option>
                        <option value="JAM">Congestionamento</option>
                        <option value="ROAD_CLOSED">Via interditada</option>
                        <option value="HAZARD">Perigo</option>
                        <option value="SEMAFORO_OFF">Semáforo desligado</option>
                    </select>
                </div>

                <div class="d-grid mb-3">
                    <button id="mon-switch" class="btn btn-primary" data-active="0">
                        <i class="bi bi-play-circle"></i> Monitorar alerta
                    </button>
                    <div id="monitor-feedback" class="small mt-2 d-none"></div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header py-2">
                        <div class="fw-semibold">Status de Alertas</div>
                    </div>
                    <div class="card-body p-0">
                        <ul id="mon-reqs" class="list-group list-group-flush small">
                            <li class="list-group-item text-muted">
                                Nenhum pedido de monitoramento ativo.
                            </li>
                        </ul>
                    </div>
                </div>



                <!-- ===== Painel Gerenciar Tipos & Indicadores ===== -->
            <div id="panel-ind" class="d-none">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">Cadastro e herança de indicadores</div>
                    <button id="btn-new-oae" class="btn btn-sm btn-primary">Cadastrar OAE</button>
                </div>
                <div class="small text-muted mb-2">Associe os tipos, indicadores e cadastre OAEs com auto-preenchimento.</div>
                <div id="indicadores-content" class="small text-muted">Carregando…</div>
                <div class="panel-pad"></div>
            </div>
        </div>
    </aside>

    <div class="rail">
        <button id="btn-toggle" class="rail-btn" title="Abrir/fechar painel"><i class="bi bi-chevron-left"></i></button>
        <button id="btn-oaes" class="rail-btn primary" title="OAEs"><i class="bi bi-building"></i></button>
        <button id="btn-bell" class="rail-btn" title="Alertas por OAE"><i class="bi bi-bell-fill"></i></button>
        <button id="btn-indicadores" class="rail-btn" title="Gerenciar Indicadores"><i class="bi bi-sliders"></i></button>
    </div>
</div>

<!-- === Modal: Cadastrar OAE === -->
<div class="modal fade" id="modalNewOAE" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cadastrar OAE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nome da OAE</label>
                    <input id="oae-name" class="form-control" placeholder="Ex.: Vd Bresser">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select id="oae-type" class="form-select"></select>
                    <div id="oae-preset-hint" class="form-text">Selecione o tipo para herdar os indicadores padrão.</div>
                </div>
                <div id="oae-preset-list" class="small"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-save-oae" class="btn btn-primary">Salvar</button>
            </div>
        </div></div>
</div>

<script>
    /* ===== Estado global ===== */
    var map, info, activeTab = 'oae';
    var oaeLayers = [], typePolylines = {};
    var alertMarkers = [], markersByCat = { ACCIDENT:[], HAZARD:[], JAM:[], ROAD_CLOSED:[] };
    var layersEnabled = { oaes:true, alerts:true };
    var typeState = {};
    var selectedOAEIds = [];              // painel OAEs
    var monSelectedOAEIds = [];           // painel Alertas/monitoramento
    var allOaeNames = [];
    var oaeAreaRectsById = {};
    var polyIdSeq = 1;

    var monCats = { ACCIDENT:true, HAZARD:true, JAM:true, ROAD_CLOSED:true };
    var monTimer = null, monSeen = {}; // dedupe

    var CAT_STYLE = {
        ACCIDENT:{ fill:'#ffa726', glyph:'🚗' },
        HAZARD:{ fill:'#ef5350', glyph:'⚠' },
        JAM:{ fill:'#42a5f5', glyph:'🚦' },
        ROAD_CLOSED:{ fill:'#c62828', glyph:'⛔' }
    };
    var TYPE_COLORS = {
        "Ponte":"#e53935","Viaduto":"#fb8c00","Passarela":"#6d4c41","Túnel":"#8e24aa",
        "Trincheira / Passagem Inferior":"#1e88e5","Passagem Subterrânea":"#3949ab",
        "Pontilhão":"#00acc1","Complexo Viário":"#43a047","Sem tipo":"#1976d2"
    };

    function setStatus(t){ var el=document.getElementById('status'); if(el) el.textContent = t||''; }
    function openPanel(){ document.getElementById('right-shell').classList.add('open'); }
    function togglePanel(){ document.getElementById('right-shell').classList.toggle('open'); }

    /* ===== Navegação de painéis ===== */
    function setPanel(tab){
        activeTab = tab;
        var title = document.getElementById('sp-title');
        var actions = document.getElementById('sp-actions');
        var pOae = document.getElementById('panel-oae');
        var pBell= document.getElementById('panel-alerts');
        var pInd = document.getElementById('panel-ind');

        pOae.classList.add('d-none'); pBell.classList.add('d-none'); pInd.classList.add('d-none');

        if (tab === 'ind') {
            title.textContent = 'Gerenciar Tipos & Indicadores';
            pInd.classList.remove('d-none');
        } else if (tab === 'alerts') {
            title.textContent = 'Alertas por OAE (monitoramento)';
            pBell.classList.remove('d-none');
        } else {
            title.textContent = 'Obras de Arte Especiais (OAEs)';
            pOae.classList.remove('d-none');
        }
        actions.innerHTML = '<button id="btn-clear-filter" class="btn btn-outline-secondary btn-sm">Limpar Filtro</button>';
        document.getElementById('btn-clear-filter').onclick = function(){
            if (activeTab==='alerts') clearMonOaEs(); else clearOaeFilter();
        };
        openPanel();
    }

    /* ===== Init ===== */
    function initMap(){
        map = new google.maps.Map(document.getElementById('map'), {
            center:{lat:-23.55,lng:-46.63}, zoom:12,
            mapTypeControl:true, streetViewControl:false, fullscreenControl:true
        });
        info = new google.maps.InfoWindow();

        document.getElementById('toggle-oaes').onclick = function(ev){
            var btn = ev.currentTarget; btn.classList.toggle('active');
            layersEnabled.oaes = btn.classList.contains('active');
            btn.setAttribute('data-on', layersEnabled.oaes?'1':'0');
            updateOAEsVisibility();
            document.getElementById('sidepanel').classList.toggle('disabled', !layersEnabled.oaes && activeTab!=='alerts');
        };
        document.getElementById('toggle-alerts').onclick = function(ev){
            var btn = ev.currentTarget; btn.classList.toggle('active');
            layersEnabled.alerts = btn.classList.contains('active');
            btn.setAttribute('data-on', layersEnabled.alerts?'1':'0');
            updateAlertsVisibility();
        };

        document.getElementById('btn-toggle').onclick = togglePanel;
        document.getElementById('btn-oaes').onclick = function(){ setPanel('oae'); };
        document.getElementById('btn-indicadores').onclick = function(){ setPanel('ind'); };
        document.getElementById('btn-bell').onclick = function(){ setPanel('alerts'); };

        document.getElementById('btn-clear').onclick = clearAll;
        document.getElementById('btn-all').onclick  = function(){ setAllTypes(true); };
        document.getElementById('btn-none').onclick = function(){ setAllTypes(false); };

        // OAEs (chips)
        var input = document.getElementById('oae-input');
        input.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); tryAddOAE(input.value); }});
        input.addEventListener('change', function(){ tryAddOAE(input.value); });
        input.addEventListener('focus', openPanel);

        // Monitor (chips)
        var minput = document.getElementById('mon-oae-input');
        minput.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); tryAddMonOAE(minput.value); }});
        minput.addEventListener('change', function(){ tryAddMonOAE(minput.value); });

        // Monitor categorias
        var monCbs=document.querySelectorAll('.mon-cat');
        for (var i=0;i<monCbs.length;i++){
            monCbs[i].onchange=function(ev){
                var cat=ev.target.getAttribute('data-cat'); monCats[cat]=ev.target.checked;
            };
        }
        // Monitor switch
        document.getElementById('mon-switch').onchange=function(ev){
            if (ev.target.checked){ startMonitor(); } else { stopMonitor(); }
        };

        fillTrafficSummary();
        updateWazeUpdated();
        fetchOAEs();

        // painel gestão tipos
        var cont = document.getElementById('indicadores-content');
        cont.innerHTML = '<div class="alert alert-info mb-2">Use o botão <b>Cadastrar OAE</b> para adicionar rapidamente uma OAE herdando os indicadores do Tipo.</div>';
        document.getElementById('btn-new-oae').onclick = openNewOaeModal;
    }

    /* ===== OAEs (mapa) ===== */
    function fetchOAEs(){
        setStatus('Carregando OAEs...');
        fetch('api/oaes.php?mock=1').then(r=>r.json()).then(function(fc){
            renderOAEs(fc);
            setStatus('OAEs carregadas: '+oaeLayers.length+'. Use o campo acima para selecionar.');
        }).catch(function(e){ console.error(e); setStatus('Falha ao carregar OAEs (veja o console).'); });
    }
    var CLICK_TOLERANCE_M = 8;

    function setSelectedStyle(pl, isSelected){
        if (!pl) return;
        if (isSelected){
            if (!pl.__outline){
                pl.__outline = new google.maps.Polyline({
                    path: pl.getPath(), strokeColor:'#000', strokeOpacity:1.0,
                    strokeWeight:(pl.get('strokeWeight')||4)+3, zIndex:(pl.get('zIndex')||0)
                });
            }
            if (layersEnabled.oaes && typeState[pl.__oaeType]) pl.__outline.setMap(map);
            pl.setOptions({ zIndex:(pl.get('zIndex')||0)+1 });
        } else {
            if (pl.__outline) pl.__outline.setMap(null);
            pl.setOptions({ zIndex:null });
        }
    }

    function renderOAEs(fc){
        if(!fc || !fc.features || !fc.features.length) return;

        var presentTypes = {};
        fc.features.forEach(function(f){
            var t = (f.properties && (f.properties.oae_type || f.properties.type)) || 'Sem tipo';
            presentTypes[t] = true;
        });
        buildTypeFilter(Object.keys(presentTypes).sort());

        oaeLayers = [];
        fc.features.forEach(function(f){
            if(!f.geometry || f.geometry.type!=='LineString') return;
            var coords = f.geometry.coordinates.map(function(x){ return {lat:x[1], lng:x[0]}; });
            var oaeName = (f.properties && (f.properties.oae_name || f.properties.name || f.properties.street)) || 'OAE';
            var oaeType = (f.properties && (f.properties.oae_type || f.properties.type)) || 'Sem tipo';
            var color   = TYPE_COLORS[oaeType] || '#1976d2';

            var pl = new google.maps.Polyline({
                path:coords, strokeColor:color, strokeWeight:4, strokeOpacity:.9,
                map:(layersEnabled.oaes && typeState[oaeType]!==false) ? map : null
            });
            pl.__id = polyIdSeq++;
            pl.__oaeName = oaeName;
            pl.__oaeType = oaeType;

            if(!typePolylines[oaeType]) typePolylines[oaeType]=[];
            typePolylines[oaeType].push(pl);
            oaeLayers.push(pl);

            pl.addListener('click', function(ev){
                if (!google.maps.geometry.poly.isLocationOnEdge(ev.latLng, pl, CLICK_TOLERANCE_M)) return;
                if (activeTab==='alerts'){ addMonOAEByPolyline(pl, true); }
                else { addOAEByPolyline(pl, true); }
                showOAEInfo(pl, ev.latLng);
                openPanel();
            });
        });

        fillOaeSuggestions();
        fillMonOaeSuggestions();
    }

    function getPolylinesByName(name){ return oaeLayers.filter(pl => pl.__oaeName === name); }
    function getPolylineById(id){ for (var i=0;i<oaeLayers.length;i++) if (oaeLayers[i].__id===id) return oaeLayers[i]; return null; }

    function showOAEInfo(pl, anchor){
        var lenM = google.maps.geometry.spherical.computeLength(pl.getPath());
        var km = (lenM/1000).toFixed(2)+' km';
        var html = '<div><b>'+pl.__oaeName+'</b><br><small>'+pl.__oaeType+' • '+km+'</small></div>';
        var pos = anchor || pl.getPath().getAt(Math.floor(pl.getPath().getLength()/2));
        info.setContent(html); info.setPosition(pos); info.open(map);
    }

    /* ===== Tipos (filtro) ===== */
    function buildTypeFilter(types){
        var box = document.getElementById('oae-types'); box.innerHTML='';
        types.forEach(function(t){
            typeState[t]=true;
            var color = TYPE_COLORS[t] || '#1976d2';
            var id = 't_'+btoa(t).replace(/=/g,'');
            var row = document.createElement('div'); row.className='form-check d-flex align-items-center gap-2';
            row.innerHTML =
                '<input class="form-check-input" type="checkbox" id="'+id+'" checked>'+
                '<span class="tiny-dot" style="background:'+color+'"></span>'+
                '<label class="form-check-label" for="'+id+'">'+t+'</label>';
            box.appendChild(row);
            row.querySelector('input').addEventListener('change', function(ev){
                toggleType(t, ev.target.checked); updateTypesBadge();
            });
        });
        updateTypesBadge();
    }
    function toggleType(type,on){
        typeState[type]=on;
        (typePolylines[type]||[]).forEach(function(pl){
            pl.setMap(layersEnabled.oaes && on ? map : null);
            if (selectedOAEIds.indexOf(pl.__id)!==-1) setSelectedStyle(pl, layersEnabled.oaes && on);
        });
    }
    function setAllTypes(on){
        for(var k in typeState){ if(!typeState.hasOwnProperty(k)) continue;
            typeState[k]=on; var id='t_'+btoa(k).replace(/=/g,''); var cb=document.getElementById(id); if(cb) cb.checked=on; }
        updateOAEsVisibility(); updateTypesBadge();
    }
    function updateOAEsVisibility(){
        for(var t in typePolylines){ if(!typePolylines.hasOwnProperty(t)) continue;
            (typePolylines[t]||[]).forEach(function(pl){
                pl.setMap(layersEnabled.oaes && typeState[t] ? map : null);
                if (selectedOAEIds.indexOf(pl.__id)!==-1){
                    setSelectedStyle(pl, layersEnabled.oaes && typeState[t]);
                } else setSelectedStyle(pl, false);
            });
        }
    }
    function updateTypesBadge(){ var n=0; for(var k in typeState){ if(typeState.hasOwnProperty(k) && typeState[k]) n++; } var b=document.getElementById('types-badge'); if(b) b.textContent=n; }

    /* ===== Busca/Chips OAEs (painel OAEs) ===== */
    function fillOaeSuggestions(){
        var namesMap={}, arr=[];
        oaeLayers.forEach(function(pl){ namesMap[pl.__oaeName]=true; });
        for(var n in namesMap){ if(namesMap.hasOwnProperty(n)) arr.push(n); }
        arr.sort((a,b)=>a.localeCompare(b));
        allOaeNames=arr;
        var dl=document.getElementById('oaes-list'); dl.innerHTML='';
        allOaeNames.forEach(function(n){ var o=document.createElement('option'); o.value=n; dl.appendChild(o); });
    }
    function tryAddOAE(value){
        var name=(value||'').trim(); if(!name) return;
        var found = allOaeNames.find(n=>n.toLowerCase()===name.toLowerCase()) || name;
        var pl = getPolylinesByName(found)[0];
        if (pl) addOAEByPolyline(pl, true);
        document.getElementById('oae-input').value=''; setPanel('oae');
    }

    function drawAreaForPolyline(pl, meters){
        meters = meters || 500;
        var path = pl.getPath(); if(!path || path.getLength()===0) return;
        var b=new google.maps.LatLngBounds();
        for (var i=0;i<path.getLength();i++) b.extend(path.getAt(i));
        var c=b.getCenter();
        var n=google.maps.geometry.spherical.computeOffset(c,meters,0);
        var s=google.maps.geometry.spherical.computeOffset(c,meters,180);
        var e=google.maps.geometry.spherical.computeOffset(c,meters,90);
        var w=google.maps.geometry.spherical.computeOffset(c,meters,270);
        if (oaeAreaRectsById[pl.__id]) oaeAreaRectsById[pl.__id].setMap(null);
        oaeAreaRectsById[pl.__id] = new google.maps.Rectangle({
            bounds:{ north:n.lat(), south:s.lat(), east:e.lng(), west:w.lng() },
            strokeColor:'#e53935', strokeOpacity:.85, strokeWeight:2,
            fillColor:'#e53935', fillOpacity:.18, map:map
        });
    }
    function addOAEByPolyline(pl, zoom){
        if (selectedOAEIds.indexOf(pl.__id) !== -1) return;
        selectedOAEIds.push(pl.__id);
        renderChips();
        if (typeState[pl.__oaeType] === false) {
            typeState[pl.__oaeType] = true; updateOAEsVisibility();
            var id = 't_' + btoa(pl.__oaeType).replace(/=/g,''); var cb = document.getElementById(id); if (cb) cb.checked = true;
        }
        setSelectedStyle(pl, true); drawAreaForPolyline(pl, 500);
        if (zoom) fitToSelectedOAEs({ maxZoom: 15 });
        fetchAlertsForSelected();
        setStatus('OAEs selecionadas: ' + selectedOAEIds.length);
    }
    function removeOAEById(id){
        selectedOAEIds = selectedOAEIds.filter(x=>x!==id);
        renderChips();
        if (oaeAreaRectsById[id]) { oaeAreaRectsById[id].setMap(null); delete oaeAreaRectsById[id]; }
        var pl = getPolylineById(id); if (pl) setSelectedStyle(pl, false);
        fitToSelectedOAEs({ maxZoom: 15 }); fetchAlertsForSelected();
        setStatus('OAEs selecionadas: ' + selectedOAEIds.length);
    }
    function renderChips(){
        var box=document.getElementById('oae-chips'); box.innerHTML='';
        selectedOAEIds.forEach(function(id){
            var pl = getPolylineById(id); if(!pl) return;
            var chip=document.createElement('span'); chip.className='chip';
            chip.innerHTML='<span>'+pl.__oaeName+'</span><span class="x" title="Remover">&times;</span>';
            chip.querySelector('.x').onclick=function(){ removeOAEById(id); };
            box.appendChild(chip);
        });
    }
    function fitToSelectedOAEs(opts){
        if(!selectedOAEIds.length) return;
        var b = new google.maps.LatLngBounds(), any = false;
        selectedOAEIds.forEach(function(id){
            var pl = getPolylineById(id); if (!pl) return;
            var path = pl.getPath();
            for (var i=0; i<path.getLength(); i++) { b.extend(path.getAt(i)); any = true; }
            setSelectedStyle(pl, true);
        });
        if (!any) return;
        var padding = (opts && opts.padding) || { top:40, left:40, bottom:40, right:40 + 420 + 64 + 16 };
        var maxZoom = (opts && opts.maxZoom) || 15;
        var usedPadding = false;
        try { map.fitBounds(b, padding); usedPadding = true; } catch(e){ map.fitBounds(b); }
        google.maps.event.addListenerOnce(map, 'idle', function(){
            if (map.getZoom() > maxZoom) map.setZoom(maxZoom);
            if (!usedPadding) { var shiftRight = (420 + 64) / 2; map.panBy(-shiftRight, 0); }
        });
    }
    function clearOaeFilter(){
        selectedOAEIds.slice().forEach(removeOAEById);
        selectedOAEIds = []; renderChips();
        clearAlerts(); setStatus('Filtro limpo. Selecione OAEs para ver alertas.');
    }

    /* ===== ALERTAS (render no mapa) ===== */
    function fetchAlertsForSelected(){
        clearAlerts();
        if(!selectedOAEIds.length){ updateSummaryCounts(); return Promise.resolve(0); }
        var namesSet = {};
        selectedOAEIds.forEach(function(id){ var pl = getPolylineById(id); if (pl) namesSet[pl.__oaeName]=true; });
        var names = Object.keys(namesSet);
        var reqs = names.map(function(n){
            return fetch('api/alerts.php?mock=1&oae_name='+encodeURIComponent(n))
                .then(r=>r.json()).catch(()=>({type:'FeatureCollection',features:[]}));
        });
        return Promise.all(reqs).then(function(arr){
            var all={type:'FeatureCollection',features:[]};
            arr.forEach(function(data){ var fc=normalizeAlertsToFC(data,null); all.features = all.features.concat(fc.features||[]); });
            var count=renderAlerts(all);
            setStatus('OAEs selecionadas: '+selectedOAEIds.length+' • Alertas: '+count);
            updateWazeUpdated(); return count;
        });
    }
    function normalizeAlertsToFC(data, nameFilter){
        var features=[];
        if(data && data.type && /featurecollection/i.test(data.type) && data.features && data.features.length){
            features = data.features.filter(function(f){
                if(!f.properties) return false;
                if(!nameFilter) return (f.geometry && f.geometry.type==='Point');
                var n = f.properties.oae_name || f.properties.name || '';
                return (f.geometry && f.geometry.type==='Point') && (n && n.toLowerCase().trim()===nameFilter.toLowerCase().trim());
            }); return { type:'FeatureCollection', features:features };
        }
        if(data && data.alerts && data.alerts.length){
            data.alerts.forEach(function(a){
                if(!a.point || !a.point.geometry || a.point.geometry.type!=='Point') return;
                var n = a.name || (a.point.properties && a.point.properties.name) || '';
                if(nameFilter && n.toLowerCase().trim()!==nameFilter.toLowerCase().trim()) return;
                features.push({ type:'Feature', properties:{
                        name:n, type:a.type||null, alert_type:a.alert_type||null, street:a.street||null, date:a.date||null, hour:a.hour||null
                    }, geometry:a.point.geometry });
            }); return { type:'FeatureCollection', features:features };
        }
        if(data && data.jams && data.jams.length){
            data.jams.forEach(function(a){
                if(!a.point || !a.point.geometry || a.point.geometry.type!=='Point') return;
                var n = a.name || (a.point.properties && a.point.properties.name) || '';
                if(nameFilter && n.toLowerCase().trim()!==nameFilter.toLowerCase().trim()) return;
                features.push({ type:'Feature', properties:{
                        name:n, type:a.type||null, alert_type:a.alert_type||'JAM', street:a.street||null, date:a.date||null, hour:a.hour||null
                    }, geometry:a.point.geometry });
            }); return { type:'FeatureCollection', features:features };
        }
        return { type:'FeatureCollection', features:[] };
    }
    function resetMarkersByCat(){ for(var k in markersByCat){ if(!markersByCat.hasOwnProperty(k)) continue; markersByCat[k].forEach(function(m){ m.setMap(null); }); markersByCat[k]=[]; } }
    function catKeyFrom(type){
        if(!type) return 'JAM';
        var t=String(type).toUpperCase();
        if(t.indexOf('ACCIDENT')>=0) return 'ACCIDENT';
        if(t.indexOf('ROAD_CLOSED')>=0 || t.indexOf('ROAD_CLOSURE')>=0) return 'ROAD_CLOSED';
        if(t.indexOf('HAZARD')>=0) return 'HAZARD';
        if(t.indexOf('JAM')>=0) return 'JAM';
        return 'JAM';
    }
    function makeGlyphIcon(fill,glyph){
        var svg = "<svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 24 24'>"
            + "<circle cx='12' cy='12' r='9' fill='"+fill+"' stroke='#333' stroke-width='1'/>"
            + "<text x='12' y='15' text-anchor='middle' font-size='12' fill='#111' font-family='Segoe UI Emoji, Apple Color Emoji, Noto Color Emoji, Arial, sans-serif'>"+glyph+"</text>"
            + "</svg>";
        return { url:'data:image/svg+xml;charset=UTF-8,'+encodeURIComponent(svg),
            scaledSize:new google.maps.Size(22,22), anchor:new google.maps.Point(11,11) };
    }
    function renderAlerts(fc){
        if(!fc || !fc.features) return 0;
        resetMarkersByCat();
        fc.features.forEach(function(f){
            if(!f.geometry || f.geometry.type!=='Point') return;
            var lng=f.geometry.coordinates[0], lat=f.geometry.coordinates[1];
            var p=f.properties||{};
            var cat=catKeyFrom(p.alert_type || p.type);
            var sty=CAT_STYLE[cat] || {fill:'#ffb300', glyph:'•'};
            var icon=makeGlyphIcon(sty.fill, sty.glyph);
            var m=new google.maps.Marker({ position:{lat:lat,lng:lng}, icon:icon, zIndex:100, map: layersEnabled.alerts ? map : null });
            m.addListener('click', function(){
                var title=(p.alert_type || p.type || 'Alerta').replace(/_/g,' ');
                var when=[p.date,p.hour].filter(Boolean).join(' ');
                var street=p.street||'';
                info.setContent('<div><b>'+title+'</b>'+(street?'<br>'+street:'')+(when?'<br><small>'+when+'</small>':'')+'</div>');
                info.open(map,m);
            });
            alertMarkers.push(m);
            markersByCat[cat].push(m);
        });
        updateSummaryCounts(); bindSummaryToggles(); updateAlertsVisibility();
        return alertMarkers.length;
    }
    function bindSummaryToggles(){
        var sws=document.querySelectorAll('#alerts-summary .cat-toggle');
        for(var i=0;i<sws.length;i++){
            sws[i].onchange=function(ev){
                var card=ev.currentTarget.closest('[data-cat]'); var cat=card.getAttribute('data-cat');
                var on=ev.currentTarget.checked && layersEnabled.alerts;
                (markersByCat[cat]||[]).forEach(function(m){ m.setMap(on?map:null); });
            };
        }
    }
    function updateSummaryCounts(){
        var box=document.getElementById('alerts-summary');
        var empty=true; for(var k in markersByCat){ if(markersByCat.hasOwnProperty(k) && markersByCat[k].length){ empty=false; break; } }
        box.classList.toggle('d-none', empty);
        var cards=box.querySelectorAll('[data-cat]');
        for(var i=0;i<cards.length;i++){
            var cat=cards[i].getAttribute('data-cat'); var n=(markersByCat[cat]||[]).length;
            cards[i].querySelector('.count-num').textContent=n;
        }
    }
    function updateAlertsVisibility(){
        var box=document.getElementById('alerts-summary');
        function on(cat){ var sw=box.querySelector('[data-cat="'+cat+'"] .cat-toggle'); return layersEnabled.alerts && sw && sw.checked; }
        for(var cat in markersByCat){ if(!markersByCat.hasOwnProperty(cat)) continue;
            (markersByCat[cat]||[]).forEach(function(m){ m.setMap(on(cat)?map:null); });
        }
    }

    /* ===== Util ===== */
    function clearAlerts(){ alertMarkers.forEach(function(m){ m.setMap(null); }); alertMarkers.length=0; resetMarkersByCat(); updateSummaryCounts(); }
    function clearAll(){
        oaeLayers.forEach(function(l){ l.setMap(null); }); oaeLayers.length=0;
        for(var k in typePolylines){ if(typePolylines.hasOwnProperty(k)) delete typePolylines[k]; }
        for (var id in oaeAreaRectsById){ if (oaeAreaRectsById[id]) oaeAreaRectsById[id].setMap(null); }
        oaeAreaRectsById = {};
        clearOaeFilter(); clearMonOaEs(); stopMonitor();
        setStatus('Camadas limpas. Recarregue para buscar novamente.');
    }
    function updateWazeUpdated(){
        var el=document.getElementById('waze-updated'); var dt=new Date();
        function pad(n){ n=String(n); return n.length<2 ? '0'+n : n; }
        el.textContent='Atualizado: '+pad(dt.getDate())+'/'+pad(dt.getMonth()+1)+'/'+dt.getFullYear()+', '+pad(dt.getHours())+':'+pad(dt.getMinutes())+':'+pad(dt.getSeconds());
    }
    function fillTrafficSummary(){
        var rows = [
            {name:'Nível 1', km: (Math.random()*80+10).toFixed(2)+' km', cls:'badge-t1'},
            {name:'Nível 2', km: (Math.random()*350+50).toFixed(2)+' km', cls:'badge-t2'},
            {name:'Nível 3', km: (Math.random()*650+120).toFixed(2)+' km', cls:'badge-t3'},
            {name:'Nível 4', km: (Math.random()*350+120).toFixed(2)+' km', cls:'badge-t4'},
            {name:'Nível 5', km: (Math.random()*120+10).toFixed(2)+' km', cls:'badge-t5'}
        ];
        var ul=document.getElementById('traffic-summary'); ul.innerHTML='';
        rows.forEach(function(lv){
            var li=document.createElement('li');
            li.className='list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML='<span>'+lv.name+'</span><span class="badge-traffic '+lv.cls+'">'+lv.km+'</span>';
            ul.appendChild(li);
        });
    }

    /* ===== Monitoramento (painel Alertas) ===== */
    function fillMonOaeSuggestions(){
        var dl=document.getElementById('mon-oaes-list'); if(!dl) return; dl.innerHTML='';
        allOaeNames.forEach(function(n){ var o=document.createElement('option'); o.value=n; dl.appendChild(o); });
    }
    function tryAddMonOAE(value){
        var name=(value||'').trim(); if(!name) return;
        var found = allOaeNames.find(n=>n.toLowerCase()===name.toLowerCase()) || name;
        var pl = getPolylinesByName(found)[0];
        if (pl) addMonOAEByPolyline(pl, true);
        document.getElementById('mon-oae-input').value='';
    }
    function addMonOAEByPolyline(pl, zoom){
        if (monSelectedOAEIds.indexOf(pl.__id)!==-1) return;
        monSelectedOAEIds.push(pl.__id);
        renderMonChips();
        if (zoom){
            var b=new google.maps.LatLngBounds(); var p=pl.getPath();
            for (var i=0;i<p.getLength();i++) b.extend(p.getAt(i));
            map.fitBounds(b);
        }
    }
    function removeMonOAEById(id){
        monSelectedOAEIds = monSelectedOAEIds.filter(x=>x!==id);
        renderMonChips();
    }
    function renderMonChips(){
        var box=document.getElementById('mon-oae-chips'); box.innerHTML='';
        monSelectedOAEIds.forEach(function(id){
            var pl = getPolylineById(id); if(!pl) return;
            var chip=document.createElement('span'); chip.className='chip';
            chip.innerHTML='<span>'+pl.__oaeName+'</span><span class="x" title="Remover">&times;</span>';
            chip.querySelector('.x').onclick=function(){ removeMonOAEById(id); };
            box.appendChild(chip);
        });
    }
    function clearMonOaEs(){
        monSelectedOAEIds = [];
        renderMonChips();
        var feed = document.getElementById('mon-feed');
        if (feed) feed.innerHTML = '';
        monSeen = {};
    }

    function startMonitor(){
        if (monTimer) return;
        monPoll(); monTimer = setInterval(monPoll, 20000);
    }
    function stopMonitor(){ if (monTimer){ clearInterval(monTimer); monTimer=null; } }
    function monPoll(){
        if (!monSelectedOAEIds.length) return;
        var namesSet={}; monSelectedOAEIds.forEach(function(id){ var pl=getPolylineById(id); if(pl) namesSet[pl.__oaeName]=true; });
        var names=Object.keys(namesSet); if(!names.length) return;

        var reqs = names.map(function(n){
            return fetch('api/alerts.php?mock=1&oae_name='+encodeURIComponent(n))
                .then(r=>r.json()).catch(()=>({type:'FeatureCollection',features:[]}));
        });
        Promise.all(reqs).then(function(arr){
            var all=[]; arr.forEach(function(data){
                var fc=normalizeAlertsToFC(data,null); (fc.features||[]).forEach(function(f){ all.push(f); });
            });
            // filtra categorias
            all = all.filter(function(f){
                var cat=catKeyFrom((f.properties||{}).alert_type || (f.properties||{}).type);
                return !!monCats[cat];
            });
            // ordena por data/hora (se houver)
            // dedup e render
            renderMonitorFeed(all);
        });
    }
    function featureKey(f){
        var p=f.properties||{}, g=f.geometry||{};
        var cat=catKeyFrom(p.alert_type||p.type);
        var xy=(g.coordinates||[]).join(',');
        var dt=[p.date,p.hour].filter(Boolean).join(' ');
        return cat+'|'+xy+'|'+dt+'|'+(p.street||'');
    }
    function renderMonitorFeed(features){
        var feed=document.getElementById('mon-feed'); if(!feed) return;
        var added=0;
        for (var i=0;i<features.length;i++){
            var f=features[i]; var key=featureKey(f);
            if (monSeen[key]) continue;
            monSeen[key]=1; added++;
            var p=f.properties||{}, cat=catKeyFrom(p.alert_type||p.type);
            var title=(p.alert_type||p.type||'Alerta').replace(/_/g,' ');
            var when=[p.date,p.hour].filter(Boolean).join(' ');
            var street=p.street||'';
            var div=document.createElement('div'); div.className='item';
            div.innerHTML='<div class="t">'+title+' <span class="badge bg-light text-dark">'+cat+'</span></div>'
                + (street?'<div>'+street+'</div>':'')
                + (when?'<div class="d">'+when+'</div>':'');
            feed.prepend(div);
        }
        // limita feed
        while (feed.children.length>100) feed.removeChild(feed.lastChild);
        if (!feed.children.length) feed.innerHTML='<div class="text-muted p-2">Sem novos alertas.</div>';
    }

    /* ===== Modal Cadastro de OAE ===== */
    function openNewOaeModal(){
        fetch('api/oae_types.php').then(r=>r.json()).then(function(list){
            var sel = document.getElementById('oae-type'); sel.innerHTML='';
            list.forEach(function(t){ var opt=document.createElement('option'); opt.value=t.id; opt.textContent=t.name; sel.appendChild(opt); });
            updatePresetPreview(sel.value);
            sel.onchange = function(){ updatePresetPreview(sel.value); };

            var modal = new bootstrap.Modal(document.getElementById('modalNewOAE'));
            modal.show();

            document.getElementById('btn-save-oae').onclick = function(){
                var name = document.getElementById('oae-name').value.trim();
                var typeId = sel.value;
                if (!name){ alert('Informe o nome.'); return; }
                fetch('api/oaes.php', {
                    method:'POST', headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({ name:name, typeId:typeId })
                }).then(function(r){
                    return r.text().then(function(txt){
                        var ok = r.ok, data=null; try{ data=JSON.parse(txt); }catch(e){}
                        if(!ok) throw new Error('HTTP '+r.status+' – '+(txt||''));
                        if(!data || !data.ok) throw new Error('Resposta inválida: '+txt);
                        return data;
                    });
                }).then(function(){ modal.hide(); alert('OAE cadastrada!'); })
                    .catch(function(err){ console.error(err); alert('Erro de rede/servidor ao salvar OAE.\n'+String(err.message||err)); });
            };
        }).catch(function(){ alert('Não foi possível carregar os tipos. Verifique api/oae_types.php.'); });
    }
    function updatePresetPreview(typeId){
        if (!typeId){ document.getElementById('oae-preset-list').innerHTML=''; return; }
        Promise.all([
            fetch('api/oae_type_indicadores.php?oaeTypeId='+encodeURIComponent(typeId)).then(r=>r.json()),
            fetch('api/indicators.php').then(r=>r.json())
        ]).then(function(arr){
            var links=arr[0]||[], indicators=arr[1]||[];
            var nameById={}; indicators.forEach(function(i){ nameById[i.id]=i.name; });
            var html='<div class="fw-semibold mb-1">Indicadores que serão herdados:</div><ul class="mb-0">';
            if (!links.length) html+='<li>(Nenhum definido para este tipo)</li>';
            links.sort(function(a,b){ var wa=(a.weight!=null)?a.weight:999, wb=(b.weight!=null)?b.weight:999; return wa-wb; });
            links.forEach(function(l){ var nm = nameById[l.indicatorId] || l.indicatorId; html+='<li>'+nm+(l.weight!=null?' <span class="text-muted">(peso '+l.weight+')</span>':'')+'</li>'; });
            html+='</ul>';
            document.getElementById('oae-preset-list').innerHTML = html;
        }).catch(function(){
            document.getElementById('oae-preset-list').innerHTML = '<div class="text-danger">Falha ao carregar os indicadores do tipo.</div>';
        });
    }

    window.addEventListener('load', initMap);

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* ========= Utilitário de Toast (Bootstrap 5) ========= */
    function showToast({title='Sucesso', message='Operação concluída.', variant='success', autohide=true, delay=2400} = {}){
        const ctr = document.getElementById('toast-ctr'); if(!ctr) return;
        const bg = {
            success:'bg-success text-white', danger:'bg-danger text-white', warning:'bg-warning',
            info:'bg-info', primary:'bg-primary text-white', secondary:'bg-secondary text-white'
        }[variant] || 'bg-dark text-white';

        const el = document.createElement('div');
        el.className = `toast align-items-center border-0 shadow`;
        el.setAttribute('role','alert'); el.setAttribute('aria-live','assertive'); el.setAttribute('aria-atomic','true');
        el.innerHTML = `
    <div class="toast-header ${bg}">
      <strong class="me-auto">${title}</strong>
      <small>agora</small>
      <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">${message}</div>
  `;
        ctr.appendChild(el);
        const t = new bootstrap.Toast(el, { autohide, delay });
        t.show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    /* Helpers globais para você chamar quando quiser */
    window.notifyAlertSent = function(oaeName, tipo){
        showToast({ title:'Alerta enviado', message:`${tipo||'Alerta'} para ${oaeName||'OAE'} enviado com sucesso.`, variant:'primary' });
    };
    window.notifyAlertReceived = function(oaeName, tipo){
        showToast({ title:'Novo alerta', message:`${tipo||'Alerta'} detectado em ${oaeName||'OAE'}.`, variant:'warning' });
    };
</script>
<script>
    /* Botão "Monitorar alerta" como toggle + toasts (compatível com seu código) */
    (function(){
        var el = document.getElementById('mon-switch');
        if (!el) return;

        // Se for input checkbox, deixamos seu handler original funcionar.
        if (el.tagName === 'INPUT') return;

        // Se for BUTTON com data-active="0|1"
        el.addEventListener('click', function(){
            var active = el.dataset.active === '1';
            if (!active){
                el.dataset.active = '1';
                el.classList.remove('btn-primary'); el.classList.add('btn-danger');
                el.innerHTML = '<i class="bi bi-stop-circle"></i> Parar monitoramento';
                if (typeof startMonitor === 'function') startMonitor();
                showToast({ title:'Monitoramento', message:'Preferências salvas. Monitoramento iniciado.', variant:'success' });
            } else {
                el.dataset.active = '0';
                el.classList.remove('btn-danger'); el.classList.add('btn-primary');
                el.innerHTML = '<i class="bi bi-play-circle"></i> Monitorar alerta';
                if (typeof stopMonitor === 'function') stopMonitor();
                showToast({ title:'Monitoramento', message:'Monitoramento encerrado.', variant:'secondary' });
            }
        });
    })();
</script>
<script>
    /* ===== Pedidos de alerta (UI local) ===== */
    (function(){
        var btn      = document.getElementById('mon-switch');     // botão Monitorar alerta (já existe)
        var sel      = document.getElementById('mon-category');   // select de tipo
        var listEl   = document.getElementById('mon-reqs');       // lista de pedidos
        var clearAll = document.getElementById('mon-clear-all');

        // estado local dos pedidos: { id, oaeId, oaeName, type, active, createdAt }
        var monRequests = [];

        // mapeia código -> rótulo
        var TYPE_LABEL = {
            ACCIDENT:'Acidente',
            JAM:'Congestionamento',
            ROAD_CLOSED:'Via interditada',
            HAZARD:'Perigo',
            SEMAFORO_OFF:'Semáforo desligado'
        };

        // util: nomes de OAEs selecionadas no painel de monitoramento
        function getMonOaeIds(){ return (window.monSelectedOAEIds || []).slice(); }
        function getOaeNameById(id){
            if (typeof getPolylineById !== 'function') return 'OAE';
            var pl = getPolylineById(id);
            return (pl && pl.__oaeName) ? pl.__oaeName : 'OAE';
        }

        function renderList(){
            listEl.innerHTML = '';
            if (!monRequests.length){
                listEl.innerHTML = '<li class="list-group-item text-muted">Nenhum pedido ainda. Selecione OAEs, escolha um alerta e clique em “Monitorar alerta”.</li>';
                return;
            }
            monRequests.forEach(function(req){
                var li = document.createElement('li');
                li.className = 'list-group-item';
                li.dataset.reqId = req.id;

                var badgeCls = req.active ? 'text-bg-success' : 'text-bg-secondary';
                var statusTxt = req.active ? 'Ativo' : 'Inativo';

                li.innerHTML = `
        <div class="d-flex align-items-start gap-2">
          <div class="flex-grow-1">
            <div class="fw-semibold">${req.oaeName}</div>
            <div class="text-muted">Alerta: ${TYPE_LABEL[req.type] || req.type}</div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge ${badgeCls}">${statusTxt}</span>
            <div class="form-check form-switch m-0" title="Ativar/Inativar">
              <input class="form-check-input req-toggle" type="checkbox" ${req.active ? 'checked':''}>
            </div>
            <button class="btn btn-sm btn-outline-danger req-del" title="Excluir">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      `;
                listEl.appendChild(li);
            });
        }

        // cria/atualiza pedidos para cada OAE selecionada
        function addRequests(type){
            var ids = getMonOaeIds();
            if (!ids.length){
                if (typeof showToast === 'function') showToast({title:'Atenção', message:'Selecione ao menos uma OAE.', variant:'warning'});
                else alert('Selecione ao menos uma OAE.');
                return;
            }
            if (!type){
                if (typeof showToast === 'function') showToast({title:'Atenção', message:'Selecione um tipo de alerta.', variant:'warning'});
                else alert('Selecione um tipo de alerta.');
                return;
            }

            // adiciona (ou reativa) um pedido por OAE
            var created = 0;
            ids.forEach(function(oaeId){
                var oaeName = getOaeNameById(oaeId);
                var key = type + '|' + oaeId;
                var found = monRequests.find(function(r){ return r.key === key; });
                if (found){
                    found.active = true; // reativa se já existia
                } else {
                    monRequests.push({
                        id: 'req_' + Date.now() + '_' + Math.random().toString(36).slice(2),
                        key: key,
                        oaeId: oaeId,
                        oaeName: oaeName,
                        type: type,
                        active: true,
                        createdAt: Date.now()
                    });
                    created++;
                }
            });

            renderList();

            if (typeof showToast === 'function') {
                var msg = created ? 'Pedido(s) criado(s) com sucesso.' : 'Pedido(s) atualizado(s).';
                showToast({ title:'Monitoramento', message: msg, variant:'success' });
            }
        }

        // eventos
        if (btn){
            btn.addEventListener('click', function(){
                addRequests(sel ? sel.value : '');
            });
        }

        if (clearAll){
            clearAll.addEventListener('click', function(){
                if (!monRequests.length) return;
                monRequests = [];
                renderList();
                if (typeof showToast === 'function') showToast({ title:'Limpo', message:'Todos os pedidos foram removidos.', variant:'secondary' });
            });
        }

        // delegação para excluir / ativar-inativar
        listEl.addEventListener('click', function(ev){
            var target = ev.target;
            var li = target.closest('li.list-group-item');
            if (!li) return;
            var id = li.dataset.reqId;

            // excluir
            if (target.closest('.req-del')){
                monRequests = monRequests.filter(function(r){ return r.id !== id; });
                renderList();
                if (typeof showToast === 'function') showToast({ title:'Excluído', message:'Pedido removido.', variant:'secondary' });
                return;
            }
        });

        // mudança do switch Ativo/Inativo
        listEl.addEventListener('change', function(ev){
            if (!ev.target.classList.contains('req-toggle')) return;
            var li = ev.target.closest('li.list-group-item'); if (!li) return;
            var id = li.dataset.reqId;
            var req = monRequests.find(function(r){ return r.id === id; });
            if (!req) return;
            req.active = !!ev.target.checked;
            renderList();
            if (typeof showToast === 'function') {
                showToast({ title:'Status', message: req.active ? 'Pedido ativado.' : 'Pedido inativado.', variant: req.active ? 'success' : 'secondary' });
            }
        });

        // primeira renderização
        renderList();
    })();
</script>

<script>
    /* ===== Lista de pedidos de alerta (UI local) ===== */
    (function(){
        var btn    = document.getElementById('mon-switch');      // teu botão "Monitorar alerta"
        var sel    = document.getElementById('mon-category');    // select de tipo
        var listEl = document.getElementById('mon-reqs');        // lista no card

        // estado local (em window para reuso, se quiser)
        window.monRequests = window.monRequests || []; // [{id,key,oaeId,oaeName,type,active,createdAt}]

        var TYPE_LABEL = {
            ACCIDENT:'Acidente',
            JAM:'Congestionamento',
            ROAD_CLOSED:'Via interditada',
            HAZARD:'Perigo',
            SEMAFORO_OFF:'Semáforo desligado'
        };

        function getMonOaeIds(){ return (window.monSelectedOAEIds || []).slice(); }
        function getOaeNameById(id){
            var pl = (typeof getPolylineById==='function') ? getPolylineById(id) : null;
            return (pl && pl.__oaeName) ? pl.__oaeName : 'OAE';
        }

        function renderList(){
            if (!listEl) return;
            listEl.innerHTML = '';
            if (!window.monRequests.length){
                listEl.innerHTML = '<li class="list-group-item text-muted">Nenhum pedido de monitoramento ativo.</li>';
                return;
            }
            window.monRequests.forEach(function(req){
                var li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.dataset.reqId = req.id;

                var badgeCls = req.active ? 'text-bg-success' : 'text-bg-secondary';
                var statusTxt = req.active ? 'Ativo' : 'Inativo';

                li.innerHTML = `
        <div>
          <div class="fw-semibold">${req.oaeName}</div>
          <div class="text-muted">Alerta: ${TYPE_LABEL[req.type] || req.type}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge ${badgeCls}">${statusTxt}</span>
          <div class="form-check form-switch m-0" title="Ativar/Inativar">
            <input class="form-check-input req-toggle" type="checkbox" ${req.active ? 'checked' : ''}>
          </div>
          <button class="btn btn-sm btn-outline-danger req-del" title="Excluir">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      `;
                listEl.appendChild(li);
            });
        }

        function addRequests(type){
            var ids = getMonOaeIds();
            if (!type){
                if (typeof showToast==='function') showToast({title:'Atenção', message:'Selecione um tipo de alerta.', variant:'warning'}); else alert('Selecione um tipo de alerta.');
                return;
            }
            if (!ids.length){
                if (typeof showToast==='function') showToast({title:'Atenção', message:'Selecione ao menos uma OAE.', variant:'warning'}); else alert('Selecione ao menos uma OAE.');
                return;
            }

            var created = 0;
            ids.forEach(function(oaeId){
                var key = type + '|' + oaeId;
                var found = window.monRequests.find(function(r){ return r.key === key; });
                if (found){
                    found.active = true; // reativa se já existia
                } else {
                    window.monRequests.push({
                        id: 'req_' + Date.now() + '_' + Math.random().toString(36).slice(2),
                        key: key,
                        oaeId: oaeId,
                        oaeName: getOaeNameById(oaeId),
                        type: type,
                        active: true,
                        createdAt: Date.now()
                    });
                    created++;
                }
            });

            renderList();
            if (typeof showToast==='function'){
                showToast({ title:'Status de alertas', message: created ? 'Pedido(s) criado(s) com sucesso.' : 'Pedido(s) atualizado(s).', variant:'success' });
            }
        }

        // Garantir que o botão NÃO vire "Parar monitoramento" (remove listeners antigos)
        if (btn){
            var clone = btn.cloneNode(true);
            btn.parentNode.replaceChild(clone, btn);
            clone.classList.remove('btn-danger'); // fica sempre primário
            clone.classList.add('btn-primary');
            clone.innerHTML = '<i class="bi bi-play-circle"></i> Monitorar alerta';
            clone.addEventListener('click', function(){
                addRequests(sel ? sel.value : '');
            });
        }

        // excluir e ativar/inativar
        if (listEl){
            listEl.addEventListener('click', function(ev){
                var li = ev.target.closest('li.list-group-item'); if (!li) return;
                var id = li.dataset.reqId;
                if (ev.target.closest('.req-del')){
                    window.monRequests = window.monRequests.filter(function(r){ return r.id !== id; });
                    renderList();
                    if (typeof showToast==='function') showToast({ title:'Excluído', message:'Pedido removido.', variant:'secondary' });
                }
            });
            listEl.addEventListener('change', function(ev){
                if (!ev.target.classList.contains('req-toggle')) return;
                var li = ev.target.closest('li.list-group-item'); if (!li) return;
                var id = li.dataset.reqId;
                var req = window.monRequests.find(function(r){ return r.id === id; });
                if (!req) return;
                req.active = !!ev.target.checked;
                renderList();
                if (typeof showToast==='function'){
                    showToast({ title:'Status', message: req.active ? 'Pedido ativado.' : 'Pedido inativado.', variant: req.active ? 'success' : 'secondary' });
                }
            });
        }

        renderList();
    })();
</script>


<script src="https://maps.google.com/maps/api/js?v=beta&libraries=visualization,drawing,geometry,places&key=AIzaSyCd3zT_keK2xr7T6ujvR3TvLj5c9u0PtsM&callback=Function.prototype"></script>

<div id="toast-ctr" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;"></div>

</body>
</html>
