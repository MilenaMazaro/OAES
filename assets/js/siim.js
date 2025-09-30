/* SIIM – JS principal (seleção ÚNICA de OAE, sem tags) */
"use strict";

/* ===== Estado global ===== */
var map, info, activeTab = 'oae';
var oaeLayers = [], typePolylines = {};
var alertMarkers = [], markersByCat = { ACCIDENT:[], HAZARD:[], JAM:[], ROAD_CLOSED:[] };
var layersEnabled = { oaes:true, alerts:true };
var typeState = {};
var selectedOAEIds = [];                 // <= agora terá no máx. 1 id
var monSelectedOAEIds = [];
var allOaeNames = [];
var oaeAreaRectsById = {};
var polyIdSeq = 1;

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

    const panelW = (tab === 'ind') ? '450px' : (tab === 'alerts' ? '400px' : '400px');
    document.documentElement.style.setProperty('--panel-w', panelW);

    pOae.classList.add('d-none'); pBell.classList.add('d-none'); pInd.classList.add('d-none');

    if (tab === 'ind') {
        title.textContent = 'Gerenciar Tipos & Indicadores';
        pInd.classList.remove('d-none');
        renderTypesTable();
    } else if (tab === 'alerts') {
        title.textContent = 'Gerenciar Alertas de OAEs';
        pBell.classList.remove('d-none');
        renderRulesTable();
    } else {
        title.textContent = 'Obras de Arte Especiais (OAEs)';
        pOae.classList.remove('d-none');
    }
    actions.innerHTML = '<button id="btn-clear-filter" class="btn btn-outline-secondary btn-sm">Limpar Filtro</button>';
    document.getElementById('btn-clear-filter').onclick = function(){ clearOaeFilter(); };
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

    // Entrada única de OAE (datalist)
    var input = document.getElementById('oae-input');
    if (input){
        input.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); tryAddOAE(input.value); }});
        input.addEventListener('change', function(){ tryAddOAE(input.value); });
        input.addEventListener('focus', openPanel);
    }
    var btnClear = document.getElementById('oae-clear');
    if (btnClear){ btnClear.addEventListener('click', clearOaeFilter); }

    fillTrafficSummary();
    updateWazeUpdated();
    fetchOAEs();

    // Tipos & Indicadores
    ensureSeeds();
    renderTypesTable();

    // Regras
    document.getElementById('btn-new-rule').onclick = function(){ openRuleModal(); };
    document.getElementById('rule-search').oninput = renderRulesTable;
    document.getElementById('rule-filter-status').onchange = renderRulesTable;

    // Tipos CRUD
    document.getElementById('btn-new-type').onclick = function(){ openTipoModal(); };
    document.getElementById('type-search').oninput = debounce(renderTypesTable, 180);

    // Cadastrar OAE
    const btnNewOae = document.getElementById('btn-new-oae');
    if (btnNewOae) btnNewOae.onclick = openNewOaeModal;
}

const CLICK_TOLERANCE_M = 8;

// atalhos simples no painel Tipos
document.addEventListener('keydown', function(e){
    const isIndTab = (document.getElementById('panel-ind') && !document.getElementById('panel-ind').classList.contains('d-none'));
    if(!isIndTab) return;
    const tag = (e.target.tagName||'').toLowerCase();
    if(['input','textarea','select'].includes(tag)) return;
    if(e.key === '/'){ e.preventDefault(); document.getElementById('type-search')?.focus(); }
    if(e.key.toLowerCase() === 'n'){ e.preventDefault(); document.getElementById('btn-new-type')?.click(); }
});

/* ===== OAEs (mapa) ===== */
function fetchOAEs(){
    setStatus('Carregando OAEs...');
    fetch('api/oaes.php?mock=1').then(r=>r.json()).then(function(fc){
        renderOAEs(fc);
        setStatus('OAEs carregadas: '+oaeLayers.length+'. Use o campo acima para selecionar.');
    }).catch(function(e){ console.error(e); setStatus('Falha ao carregar OAEs (veja o console).'); });
}
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
            addOAEByPolyline(pl, true);
            showOAEInfo(pl, ev.latLng);
            openPanel();
        });
    });

    fillOaeSuggestions();
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
    var box = document.getElementById('oae-types'); if (!box) return;
    box.innerHTML='';
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

/* ===== Seleção ÚNICA de OAE (sem tags) ===== */
function fillOaeSuggestions(){
    var namesMap={}, arr=[];
    oaeLayers.forEach(function(pl){ namesMap[pl.__oaeName]=true; });
    for(var n in namesMap){ if(namesMap.hasOwnProperty(n)) arr.push(n); }
    arr.sort((a,b)=>a.localeCompare(b));
    allOaeNames=arr;

    var dl=document.getElementById('oaes-list'); if(!dl) return;
    dl.innerHTML='';
    allOaeNames.forEach(function(n){ var o=document.createElement('option'); o.value=n; dl.appendChild(o); });

    // garante bind do botão limpar (se existir no HTML)
    var btnClear = document.getElementById('oae-clear');
    if (btnClear && !btnClear.__wired){ btnClear.__wired = true; btnClear.addEventListener('click', clearOaeFilter); }
}
function tryAddOAE(value){
    var name=(value||'').trim(); if(!name) return;
    var found = allOaeNames.find(n=>n.toLowerCase()===name.toLowerCase());
    if (!found){ setStatus('OAE não encontrada.'); return; }
    var pl = getPolylinesByName(found)[0];
    if (pl) addOAEByPolyline(pl, true);
    // em vez de limpar, mantenha o nome escolhido no input
    var input = document.getElementById('oae-input'); if (input) input.value = found;
    setPanel('oae');
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
    if (selectedOAEIds.length && selectedOAEIds[0] === pl.__id) return;
    if (selectedOAEIds.length) removeOAEById(selectedOAEIds[0]);

    selectedOAEIds = [pl.__id];

    if (typeState[pl.__oaeType] === false) {
        typeState[pl.__oaeType] = true; updateOAEsVisibility();
        var id = 't_' + btoa(pl.__oaeType).replace(/=/g,''); var cb = document.getElementById(id); if (cb) cb.checked = true;
    }


    var input = document.getElementById('oae-input'); if (input) input.value = pl.__oaeName || '';

    setSelectedStyle(pl, true);
    drawAreaForPolyline(pl, 500);
    if (zoom) fitToSelectedOAEs({ maxZoom: 15 });
    setStatus('OAE selecionada.');
    var picked = document.getElementById('oae-picked');
    if (picked) picked.textContent = 'Selecionada: ' + (pl.__oaeName||'');

    if (typeof fetchAlertsForSelected === 'function') fetchAlertsForSelected();
}

function removeOAEById(id){
    selectedOAEIds = selectedOAEIds.filter(x=>x!==id);
    if (oaeAreaRectsById[id]) { oaeAreaRectsById[id].setMap(null); delete oaeAreaRectsById[id]; }
    var pl = getPolylineById(id); if (pl) setSelectedStyle(pl, false);
    var picked = document.getElementById('oae-picked');
    if (picked) picked.textContent = 'Nenhuma OAE selecionada.';
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
    if (selectedOAEIds.length) removeOAEById(selectedOAEIds[0]);
    selectedOAEIds = [];
    var input = document.getElementById('oae-input'); if (input) input.value='';
    setStatus('Seleção limpa. Escolha uma OAE.');
    // limpa alertas no mapa
    _resetMarkers(); updateAlertsVisibility();
}

/* ===== Util ===== */
function clearAll(){
    oaeLayers.forEach(function(l){ l.setMap(null); }); oaeLayers.length=0;
    for(var k in typePolylines){ if(typePolylines.hasOwnProperty(k)) delete typePolylines[k]; }
    for (var id in oaeAreaRectsById){ if (oaeAreaRectsById[id]) oaeAreaRectsById[id].setMap(null); }
    oaeAreaRectsById = {};
    clearOaeFilter();
    _resetMarkers();
    setStatus('Camadas limpas. Recarregue para buscar novamente.');
}
function updateWazeUpdated(){
    var el=document.getElementById('waze-updated'); var dt=new Date();
    function pad(n){ n=String(n); return n.length<2 ? '0'+n : n; }
    if (el) el.textContent='Atualizado: '+pad(dt.getDate())+'/'+pad(dt.getMonth()+1)+'/'+dt.getFullYear()+', '+pad(dt.getHours())+':'+pad(dt.getMinutes())+':'+pad(dt.getSeconds());
}
function fillTrafficSummary(){
    var rows = [
        {name:'Nível 1', km: (Math.random()*80+10).toFixed(2)+' km', cls:'badge-t1'},
        {name:'Nível 2', km: (Math.random()*350+50).toFixed(2)+' km', cls:'badge-t2'},
        {name:'Nível 3', km: (Math.random()*650+120).toFixed(2)+' km', cls:'badge-t3'},
        {name:'Nível 4', km: (Math.random()*350+120).toFixed(2)+' km', cls:'badge-t4'},
        {name:'Nível 5', km: (Math.random()*120+10).toFixed(2)+' km', cls:'badge-t5'}
    ];
    var ul=document.getElementById('traffic-summary'); if(!ul) return;
    ul.innerHTML='';
    rows.forEach(function(lv){
        var li=document.createElement('li');
        li.className='list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML='<span>'+lv.name+'</span><span class="badge-traffic '+lv.cls+'">'+lv.km+'</span>';
        ul.appendChild(li);
    });
}

/* ========= Toast ========= */
function showToast({title='Sucesso', message='Operação concluída.', variant='success', autohide=true, delay=2400} = {}){
    const ctr = document.getElementById('toast-ctr'); if(!ctr) return;
    const bg = { success:'bg-success text-white', danger:'bg-danger text-white', warning:'bg-warning', info:'bg-info', primary:'bg-primary text-white', secondary:'bg-secondary text-white' }[variant] || 'bg-dark text-white';
    const el = document.createElement('div');
    el.className = `toast align-items-center border-0 shadow`;
    el.setAttribute('role','alert'); el.setAttribute('aria-live','assertive'); el.setAttribute('aria-atomic','true');
    el.innerHTML = `
    <div class="toast-header ${bg}">
      <strong class="me-auto">${title}</strong>
      <small>agora</small>
      <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">${message}</div>`;
    ctr.appendChild(el);
    const t = new bootstrap.Toast(el, { autohide, delay }); t.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
}
window.notifyAlertSent = function(oaeName, tipo){
    showToast({ title:'Alerta enviado', message:`${tipo||'Alerta'} para ${oaeName||'OAE'} enviado com sucesso.`, variant:'primary' });
};
window.notifyAlertReceived = function(oaeName, tipo){
    showToast({ title:'Novo alerta', message:`${tipo||'Alerta'} detectado em ${oaeName||'OAE'}.`, variant:'warning' });
};

/* ======= Seeds + LocalStorage helpers ======= */
const LS_TIPOS='siim_tipos', LS_INDICADORES='siim_indicadores', LS_TIPO_IND='siim_tipo_ind', LS_REGRAS='siim_regras';

function lsGet(k, d){ try{ const v=localStorage.getItem(k); return v?JSON.parse(v):d; }catch(_){ return d; } }
function lsSet(k, v){ localStorage.setItem(k, JSON.stringify(v)); }

function ensureSeeds(){
    if(!lsGet(LS_INDICADORES)){ lsSet(LS_INDICADORES, [
        {id:'velocidade', nome:'Velocidade Média', categoria:'Fluxo', unidade:'km/h'},
        {id:'tempo_travessia', nome:'Tempo Médio de Travessia', categoria:'Fluxo', unidade:'min'},
        {id:'contagem_lentidao', nome:'Contagem de Eventos de Lentidão', categoria:'Fluxo', unidade:'eventos'},
        {id:'duracao_lentidao', nome:'Duração Média da Lentidão', categoria:'Fluxo', unidade:'min'},
        {id:'indice_congestionamento', nome:'Índice de Congestionamento', categoria:'Fluxo', unidade:'%'},
        {id:'acidentes', nome:'Contagem de Acidentes', categoria:'Incidentes', unidade:'eventos'},
        {id:'parados', nome:'Veículos Parados (via/acost.)', categoria:'Incidentes', unidade:'eventos'},
        {id:'buracos', nome:'Buracos na Via', categoria:'Incidentes', unidade:'eventos'},
        {id:'alagamentos', nome:'Alagamentos', categoria:'Incidentes', unidade:'eventos'},
        {id:'perigos', nome:'Outros Perigos', categoria:'Incidentes', unidade:'eventos'},
        {id:'qtd_cameras', nome:'Quantidade de Câmeras', categoria:'Ativos', unidade:'unid'},
        {id:'status_cameras', nome:'Status das Câmeras', categoria:'Ativos', unidade:'%'}
    ]); }
    if(!lsGet(LS_TIPOS)){ lsSet(LS_TIPOS, [
        {id:'ponte', nome:'Ponte', desc:'Estrutura que transpõe cursos d’água.'},
        {id:'viaduto', nome:'Viaduto', desc:'Estrutura que transpõe vias/vales/áreas urbanas.'},
        {id:'tunel', nome:'Túnel', desc:'Passagem subterrânea para veículos.'},
        {id:'passarela', nome:'Passarela', desc:'Travessia elevada exclusiva de pedestres.'},
        {id:'trincheira', nome:'Trincheira / Passagem Inferior', desc:'Via que passa por baixo de outra.'}
    ]); }
    if(!lsGet(LS_TIPO_IND)){ lsSet(LS_TIPO_IND, {
        ponte:['velocidade','tempo_travessia','contagem_lentidao','duracao_lentidao','indice_congestionamento','qtd_cameras','status_cameras'],
        viaduto:['contagem_lentidao','indice_congestionamento','qtd_cameras','status_cameras'],
        tunel:['velocidade','contagem_lentidao','alagamentos','qtd_cameras','status_cameras'],
        passarela:['qtd_cameras','status_cameras'],
        trincheira:['velocidade','contagem_lentidao','alagamentos','qtd_cameras','status_cameras']
    }); }
    if(!lsGet(LS_REGRAS)){ lsSet(LS_REGRAS, []); }
}
function indicadores(){ return lsGet(LS_INDICADORES,[]); }
function tipos(){ return lsGet(LS_TIPOS,[]); }
function tipoIndMap(){ return lsGet(LS_TIPO_IND,{}); }
function setTipoIndMap(m){ lsSet(LS_TIPO_IND,m); }
function regras(){ return lsGet(LS_REGRAS,[]); }
function setRegras(r){ lsSet(LS_REGRAS,r); }

/* ===== Utils ===== */
function debounce(fn, wait){
    let t; return function(...args){
        clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait);
    };
}

/* ===== Tipos & Indicadores (CRUD/UI) ===== */
function renderTypesTable(){
    const body = document.getElementById('types-body'); if(!body) return;
    const q = (document.getElementById('type-search')?.value || '').toLowerCase();
    const list = tipos().filter(t => !q || t.nome.toLowerCase().includes(q));

    if(!list.length){ body.innerHTML='<tr><td colspan="5" class="text-muted">Nenhum tipo.</td></tr>'; return; }

    const mapT = tipoIndMap(); const indsById = Object.fromEntries(indicadores().map(i=>[i.id,i]));
    body.innerHTML='';
    list.forEach((t,idx)=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `
      <td>${idx+1}</td>
      <td>${t.nome}</td>
      <td class="text-muted small">${t.desc||''}</td>
      <td><span class="badge-count">${(mapT[t.id]||[]).length}</span></td>
      <td class="text-end">
        <button class="btn btn-ico-sm btn-outline-primary me-1" data-act="ind" data-id="${t.id}" title="Indicadores">
          <i class="bi bi-sliders"></i>
        </button>
        <button class="btn btn-ico-sm btn-outline-secondary me-1" data-act="edit" data-id="${t.id}" title="Editar">
          <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-ico-sm btn-outline-danger me-1" data-act="del" data-id="${t.id}" title="Excluir">
          <i class="bi bi-trash"></i>
        </button>
      </td>`;
        body.appendChild(tr);
    });
    body.onclick = function(ev){
        const btn = ev.target.closest('button[data-act]');
        const tr  = ev.target.closest('tr');
        if(btn){
            const id = btn.getAttribute('data-id'), act = btn.getAttribute('data-act');
            if(act==='edit') return openTipoModal(id);
            if(act==='del'){
                if(confirm('Excluir este tipo? Esta ação não pode ser desfeita.')){
                    const arr = tipos().filter(t=>t.id!==id);
                    lsSet(LS_TIPOS, arr);
                    renderTypesTable();
                    showToast({title:'Excluído',variant:'secondary',message:'Tipo removido.'});
                }
                return;
            }
            if(act==='ind') return openIndicadoresCanvas(id);
        }else if(tr){
            const idCell = tr.querySelector('button[data-id]')?.getAttribute('data-id');
            if(idCell) openIndicadoresCanvas(idCell);
        }
    };
}
function openTipoModal(id){
    const modal = new bootstrap.Modal(document.getElementById('modalTipo'));
    const t = tipos().find(x=>x.id===id) || {id:'',nome:'',desc:''};
    document.getElementById('tipo-id').value = t.id;
    document.getElementById('tipo-nome').value = t.nome || '';
    document.getElementById('tipo-desc').value = t.desc || '';
    document.getElementById('btn-save-tipo').onclick = function(){
        const idOld = document.getElementById('tipo-id').value;
        const nome = document.getElementById('tipo-nome').value.trim();
        const desc = document.getElementById('tipo-desc').value.trim();
        if(!nome){ alert('Informe o nome do tipo.'); return; }
        let arr = tipos();
        if(idOld){
            arr = arr.map(x=> x.id===idOld ? {...x, nome, desc} : x);
        }else{
            const newId = nome.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'-');
            arr.push({id:newId, nome, desc});
        }
        lsSet(LS_TIPOS, arr); modal.hide(); renderTypesTable(); showToast({title:'Salvo',variant:'success',message:'Tipo salvo.'});
    };
    modal.show();
}
function openIndicadoresCanvas(tipoId){
    const off = new bootstrap.Offcanvas(document.getElementById('ocIndicadores'));
    const body = document.getElementById('oc-indicadores-body');
    const catGroups = { Fluxo:[], Incidentes:[], Ativos:[] };
    indicadores().forEach(i => { (catGroups[i.categoria]||(catGroups[i.categoria]=[])).push(i); });
    const mapT = tipoIndMap(); const selected = new Set(mapT[tipoId]||[]);
    let html='';
    Object.keys(catGroups).forEach(cat=>{
        html += `<div class="mb-2"><div class="fw-semibold mb-1">${cat}</div>`;
        catGroups[cat].forEach(i=>{
            const id = `ind-${i.id}`;
            html += `
        <div class="form-check">
          <input class="form-check-input ind-cb" type="checkbox" id="${id}" data-id="${i.id}" ${selected.has(i.id)?'checked':''}>
          <label class="form-check-label" for="${id}">${i.nome} <span class="text-muted">(${i.unidade})</span></label>
        </div>`;
        });
        html+='</div>';
    });
    body.innerHTML = html;
    document.getElementById('btn-oc-ind-save').onclick = function(){
        const sel = Array.from(body.querySelectorAll('.ind-cb:checked')).map(cb=>cb.getAttribute('data-id'));
        const m = tipoIndMap(); m[tipoId] = sel; setTipoIndMap(m);
        off.hide(); renderTypesTable(); showToast({title:'Indicadores', message:'Vínculos salvos.', variant:'success'});
    };
    off.show();
}

/* ===== Regras ===== */
function renderRulesTable(){
    const tbody = document.getElementById('rules-body'); if(!tbody) return;

    const q  = (document.getElementById('rule-search')?.value||'').toLowerCase();
    const st = (document.getElementById('rule-filter-status')?.value||'all');
    const inds = Object.fromEntries(indicadores().map(i=>[i.id,i]));
    const tps  = Object.fromEntries(tipos().map(t=>[t.id,t]));
    let list = regras();

    if (st==='on') list = list.filter(r=>r.ativa);
    else if (st==='off') list = list.filter(r=>!r.ativa);

    if (q){
        list = list.filter(r=>{
            const scope = r.escopo?.tipoId
                ? (tps[r.escopo.tipoId]?.nome || r.escopo.tipoId)
                : (r.escopo?.oaeNames||[]).join(', ');
            const indNome = inds[r.indicadorId]?.nome || r.indicadorId;
            return (scope+' '+indNome).toLowerCase().includes(q);
        });
    }

    if (!list.length){
        tbody.innerHTML = '<tr><td colspan="10" class="text-muted py-4 text-center">Nenhuma regra cadastrada.</td></tr>';
        return;
    }

    tbody.innerHTML = '';
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = 10;
    td.innerHTML = '<div class="rules-cards"></div>';
    tr.appendChild(td);
    tbody.appendChild(tr);

    const grid = td.firstElementChild;
    const channelBadge = (on, label) =>
        `<span class="badge ${on ? 'text-bg-primary' : 'text-bg-secondary'}">${label}</span>`;

    list.forEach((r) => {
        const scopeTxt = r.escopo?.tipoId
            ? `Tipo: ${tps[r.escopo.tipoId]?.nome || r.escopo.tipoId}`
            : `OAEs: ${(r.escopo?.oaeNames||[]).join(', ') || '—'}`;

        const indNome = inds[r.indicadorId]?.nome || r.indicadorId;
        const unit = (inds[r.indicadorId]?.unidade||'');

        const opText = r.condicao === '<' ? 'menor que'
            : r.condicao === '>' ? 'maior que'
                : 'igual a';

        const card = document.createElement('div');
        card.className = 'rule-card';
        card.innerHTML = `
      <div class="rule-head">
        <div class="rule-scope" title="${scopeTxt}">${scopeTxt}</div>
        <div class="form-check form-switch m-0" title="Ativar/Inativar">
          <input class="form-check-input rule-on" type="checkbox" data-id="${r.id}" ${r.ativa?'checked':''}>
        </div>
      </div>

      <div class="rule-ind">
        <span class="text-muted">Indicador:</span> <span class="fw-semibold">${indNome}</span>
      </div>

      <div class="rule-condition">
        <span class="lab">Alerta se</span>
        <span class="ind text-muted">indicador</span>
        <span class="op fw-bold">${opText}</span>
        <span class="thr fw-bold">${r.threshold}</span>
        ${unit ? `<span class="unit text-muted">${unit}</span>` : ''}
      </div>

      <div class="rule-metas">
        <span class="badge text-bg-light border">Silêncio <span class="fw-semibold">${r.janelaSilencioMin||30} min</span></span>
      </div>

      <div class="rule-channels">
        ${channelBadge(!!(r.canais||{}).tela,  'Tela')}
        ${channelBadge(!!(r.canais||{}).email, 'E-mail')}
        ${channelBadge(!!(r.canais||{}).sms,   'SMS')}
      </div>

      <div class="rule-actions d-grid gap-2">
        <button class="btn btn-outline-secondary btn-sm" data-act="edit" data-id="${r.id}">
          <i class="bi bi-pencil"></i> Editar
        </button>
        <button class="btn btn-outline-primary btn-sm" data-act="test" data-id="${r.id}">
          <i class="bi bi-bell"></i> Testar
        </button>
        <button class="btn btn-outline-danger btn-sm" data-act="del" data-id="${r.id}">
          <i class="bi bi-trash"></i> Excluir
        </button>
      </div>
    `;
        grid.appendChild(card);
    });

    tbody.onclick = function(ev){
        const btn = ev.target.closest('button[data-act]');
        if (!btn) return;
        const id  = btn.getAttribute('data-id');
        const act = btn.getAttribute('data-act');
        if (act === 'edit')  return openRuleModal(id);
        if (act === 'test')  return simulateRule(id);
        if (act === 'del')  {
            setRegras(regras().filter(x=>x.id!==id));
            renderRulesTable();
            return showToast({title:'Excluído',variant:'secondary',message:'Regra removida.'});
        }
    };
    tbody.onchange = function(ev){
        if (!ev.target.classList.contains('rule-on')) return;
        const id = ev.target.getAttribute('data-id');
        setRegras(regras().map(r=> r.id===id ? {...r, ativa: !!ev.target.checked} : r));
        showToast({
            title:'Status',
            message: ev.target.checked ? 'Regra ativada.' : 'Regra desativada.',
            variant: ev.target.checked ? 'success' : 'secondary'
        });
    };
}

function openRuleModal(id){
    ensureSeeds();
    const modal = new bootstrap.Modal(document.getElementById('modalRegra'));
    const r = regras().find(x=>x.id===id) || {
        id:'', escopo:{tipoId:'',oaeIds:[],oaeNames:[]},
        indicadorId:'velocidade', condicao:'<', threshold:20,
        destinatarios:['Operação'],
        canais:{tela:true,email:false,sms:false},
        janelaSilencioMin:30, ativa:true
    };

    // preencher tipos
    const selTipo = document.getElementById('rule-tipoId'); selTipo.innerHTML='';
    tipos().forEach(t=>{ const o=document.createElement('option'); o.value=t.id; o.textContent=t.nome; selTipo.appendChild(o); });
    selTipo.value = r.escopo.tipoId || tipos()[0]?.id || '';

    // indicadores (filtra pelos do tipo)
    function refreshIndicadores(){
        const selInd = document.getElementById('rule-indicador'); selInd.innerHTML='';
        const allowed = tipoIndMap()[selTipo.value] || [];
        const list = allowed.length ? indicadores().filter(i=>allowed.includes(i.id)) : indicadores();
        list.forEach(i=>{ const o=document.createElement('option'); o.value=i.id; o.textContent=`${i.nome} (${i.unidade})`; selInd.appendChild(o); });
        const firstOrR = (r.indicadorId && list.some(i=>i.id===r.indicadorId)) ? r.indicadorId : list[0]?.id;
        document.getElementById('rule-indicador').value = firstOrR || '';
        document.getElementById('rule-unit').textContent =
            (indicadores().find(i=>i.id=== (firstOrR)) || list[0] || {unidade:'—'}).unidade || '—';
        selInd.onchange = function(){
            const it = indicadores().find(i=>i.id===this.value);
            document.getElementById('rule-unit').textContent = it?it.unidade:'—';
        };
    }
    refreshIndicadores();
    selTipo.onchange = refreshIndicadores;

    // valores
    document.getElementById('rule-id').value = r.id;
    document.getElementById('rule-cond').value = r.condicao;
    document.getElementById('rule-threshold').value = r.threshold;
    document.getElementById('rule-dest').value = (r.destinatarios||[]).join(', ');
    document.getElementById('rule-silence').value = r.janelaSilencioMin||30;

    // canais
    document.getElementById('rule-ch-tela').checked  = !!(r.canais?.tela);
    document.getElementById('rule-ch-email').checked = !!(r.canais?.email);
    document.getElementById('rule-ch-sms').checked   = !!(r.canais?.sms);

    // escopo por OAE (chips só dentro do modal de regra — permanece)
    const chips = document.getElementById('rule-oae-chips'); chips.innerHTML='';
    function addChip(name){
        const sp=document.createElement('span'); sp.className='chip';
        sp.innerHTML=`<span>${name}</span><span class="x">&times;</span>`;
        sp.querySelector('.x').onclick=()=>{ sp.remove(); };
        chips.appendChild(sp);
    }
    (r.escopo.oaeNames||[]).forEach(addChip);
    const inOae = document.getElementById('rule-oae-input');
    inOae.onkeydown = function(e){
        if(e.key==='Enter'){ e.preventDefault();
            const v=this.value.trim(); if(v){ addChip(v); this.value=''; }
        }
    };

    // SALVAR
    document.getElementById('btn-save-rule').onclick = function(){
        const idOld = document.getElementById('rule-id').value;

        const isOaeTab = document.querySelector('#escopo-oae').classList.contains('active');
        const oaeNames = Array.from(chips.querySelectorAll('.chip span:first-child')).map(x=>x.textContent);
        const escopo = (isOaeTab && oaeNames.length) ? {oaeIds:[], oaeNames} : {tipoId: selTipo.value};

        const indId = document.getElementById('rule-indicador').value;
        const cond  = document.getElementById('rule-cond').value;
        const thr   = parseFloat(document.getElementById('rule-threshold').value||'0');
        const dest  = document.getElementById('rule-dest').value.trim()
            ? document.getElementById('rule-dest').value.split(',').map(s=>s.trim())
            : ['Operação'];

        const tela  = document.getElementById('rule-ch-tela').checked;
        const email = document.getElementById('rule-ch-email').checked;
        const sms   = document.getElementById('rule-ch-sms').checked;

        const sil   = parseInt(document.getElementById('rule-silence').value,10)||30;

        let arr = regras();
        if(idOld){
            arr = arr.map(x=> x.id===idOld
                ? {...x, escopo, indicadorId:indId, condicao:cond, threshold:thr,
                    destinatarios:dest, canais:{tela,email,sms}, janelaSilencioMin:sil }
                : x);
        }else{
            arr.push({ id:'r_'+Date.now(), escopo, indicadorId:indId, condicao:cond,
                threshold:thr, destinatarios:dest, canais:{tela,email,sms},
                janelaSilencioMin:sil, ativa:true, ultimoDisparoAt:null });
        }
        setRegras(arr); modal.hide(); renderRulesTable();
        showToast({title:'Regra', message:'Regra salva.', variant:'success'});
    };

    modal.show();
}

function simulateRule(id){
    const r = regras().find(x=>x.id===id); if(!r){ return; }
    if(!r.ativa){ showToast({title:'Silenciosa', message:'Regra está inativa.', variant:'secondary'}); return; }
    const now = Date.now();
    if(r.ultimoDisparoAt && (now - r.ultimoDisparoAt) < (r.janelaSilencioMin||30)*60000){
        showToast({title:'Silenciado', message:'Dentro da janela de silêncio.', variant:'secondary'}); return;
    }
    let val = 0, unit = (indicadores().find(i=>i.id===r.indicadorId)?.unidade)||'';
    if(r.indicadorId==='velocidade') val = Math.round( (12 + Math.random()*28) * 10 ) / 10;
    else if(r.indicadorId==='indice_congestionamento') val = Math.round( (10 + Math.random()*70) );
    else if(r.indicadorId==='tempo_travessia' || r.indicadorId==='duracao_lentidao') val = Math.round( (5 + Math.random()*25) );
    else val = Math.round(1 + Math.random()*9);

    const pass =
        (r.condicao==='<') ? (val < r.threshold) :
            (r.condicao==='>') ? (val > r.threshold) :
                (Math.abs(val - r.threshold) < 1e-9);

    const scopeName = r.escopo.tipoId ? (tipos().find(t=>t.id===r.escopo.tipoId)?.nome||'Tipo') : (r.escopo.oaeNames||['OAE']).join(', ');
    if(pass){
        r.ultimoDisparoAt = now; setRegras(regras().map(x=>x.id===r.id? r : x));
        showToast({ title:'Alerta em Tela', variant:'warning',
            message:`${scopeName} • ${(indicadores().find(i=>i.id===r.indicadorId)?.nome||r.indicadorId)} ${r.condicao} ${r.threshold}${unit?(' '+unit):''}. Valor medido: <b>${val}${unit?(' '+unit):''}</b>` });
    }else{
        showToast({ title:'Sem disparo', variant:'info',
            message:`Condição não satisfeita. Valor medido: ${val}${unit?(' '+unit):''}` });
    }
}

/* ===== Inicialização ===== */
window.addEventListener('load', initMap);

/* ===== ALERTAS LOCAIS (PATCH) ===== */
const DATA_ALERTS = 'data/obras-arte-alerts-jams.json';
let __alertsFC = null;

window.addEventListener('load', function(){
    const box = document.getElementById('alerts-summary');
    if (box) box.classList.remove('d-none');
});

function loadLocalAlertsFC(){
    if (__alertsFC) return Promise.resolve(__alertsFC);
    return fetch(DATA_ALERTS).then(r=>r.json()).then(json=>{
        const features = [];
        if (json && json.type && /featurecollection/i.test(json.type) && Array.isArray(json.features)) {
            json.features.forEach(f=>{ if (f && f.geometry && f.geometry.type === 'Point') features.push(f); });
        }
        if (Array.isArray(json.alerts)) {
            json.alerts.forEach(a=>{
                if (a.point && a.point.geometry && a.point.geometry.type==='Point') {
                    features.push({
                        type:'Feature',
                        properties:{
                            name:a.name || (a.point.properties && a.point.properties.name) || '',
                            type:a.type || null,
                            alert_type:a.alert_type || null,
                            street:a.street || null,
                            date:a.date || null,
                            hour:a.hour || null
                        },
                        geometry:a.point.geometry
                    });
                }
            });
        }
        if (Array.isArray(json.jams)) {
            json.jams.forEach(a=>{
                if (a.point && a.point.geometry && a.point.geometry.type==='Point') {
                    features.push({
                        type:'Feature',
                        properties:{
                            name:a.name || (a.point.properties && a.point.properties.name) || '',
                            type:a.type || null,
                            alert_type:'JAM',
                            street:a.street || null,
                            date:a.date || null,
                            hour:a.hour || null
                        },
                        geometry:a.point.geometry
                    });
                }
            });
        }
        __alertsFC = { type:'FeatureCollection', features };
        return __alertsFC;
    });
}
function _catKey(t){
    if(!t) return 'JAM';
    t = String(t).toUpperCase();
    if (t.includes('ACCIDENT')) return 'ACCIDENT';
    if (t.includes('ROAD_CLOSED') || t.includes('ROAD_CLOSURE')) return 'ROAD_CLOSED';
    if (t.includes('HAZARD')) return 'HAZARD';
    if (t.includes('JAM')) return 'JAM';
    return 'JAM';
}
function _glyphIcon(fill,glyph){
    const svg = "<svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 24 24'>"
        + "<circle cx='12' cy='12' r='9' fill='"+fill+"' stroke='#333' stroke-width='1'/>"
        + "<text x='12' y='15' text-anchor='middle' font-size='12' fill='#111' font-family='Segoe UI Emoji, Apple Color Emoji, Noto Color Emoji, Arial, sans-serif'>"+glyph+"</text>"
        + "</svg>";
    return { url:'data:image/svg+xml;charset=UTF-8,'+encodeURIComponent(svg),
        scaledSize:new google.maps.Size(22,22), anchor:new google.maps.Point(11,11) };
}
function _resetMarkers(){
    for (var k in markersByCat){
        (markersByCat[k]||[]).forEach(m=>m.setMap(null));
        markersByCat[k] = [];
    }
    alertMarkers.forEach(m=>m.setMap(null));
    alertMarkers.length = 0;
}
function _renderAlerts(fc){
    if(!fc || !fc.features) return 0;
    _resetMarkers();

    fc.features.forEach(f=>{
        if(!f.geometry || f.geometry.type!=='Point') return;
        const [lng,lat] = f.geometry.coordinates;
        const p = f.properties || {};
        const cat = _catKey(p.alert_type || p.type);
        const sty = (window.CAT_STYLE && CAT_STYLE[cat]) || {fill:'#ffb300', glyph:'•'};
        const icon = _glyphIcon(sty.fill, sty.glyph);

        const m = new google.maps.Marker({
            position:{lat,lng}, icon, zIndex:100, map: layersEnabled.alerts ? map : null
        });
        m.addListener('click', function(){
            const title = (p.alert_type || p.type || 'Alerta').replace(/_/g,' ');
            const when  = [p.date,p.hour].filter(Boolean).join(' ');
            const street= p.street||'';
            info.setContent('<div><b>'+title+'</b>'+(street?'<br>'+street:'')+(when?'<br><small>'+when+'</small>':'')+'</div>');
            info.open(map,m);
        });

        alertMarkers.push(m);
        (markersByCat[cat]|| (markersByCat[cat]=[])).push(m);
    });

    const box = document.getElementById('alerts-summary');
    if (box) {
        box.classList.remove('d-none');
        ['ACCIDENT','HAZARD','JAM','ROAD_CLOSED'].forEach(cat=>{
            const card = box.querySelector('[data-cat="'+cat+'"]');
            if (card) {
                const n = (markersByCat[cat]||[]).length;
                const span = card.querySelector('.count-num');
                if (span) span.textContent = n;
            }
        });
        const sws = box.querySelectorAll('.cat-toggle');
        sws.forEach(sw=>{
            sw.onchange = function(ev){
                const cat = ev.target.closest('[data-cat]')?.getAttribute('data-cat');
                const on  = ev.target.checked && layersEnabled.alerts;
                (markersByCat[cat]||[]).forEach(m=>m.setMap(on?map:null));
            };
        });
    }

    updateAlertsVisibility();
    return alertMarkers.length;
}
function updateAlertsVisibility(){
    const box = document.getElementById('alerts-summary');
    function isOn(cat){
        if (!layersEnabled.alerts) return false;
        const sw = box ? box.querySelector('[data-cat="'+cat+'"] .cat-toggle') : null;
        return (!sw || sw.checked);
    }
    for (var cat in markersByCat){
        (markersByCat[cat]||[]).forEach(m=>m.setMap(isOn(cat)?map:null));
    }
}
function fetchAlertsForSelected(){
    if(!selectedOAEIds.length){ _resetMarkers(); updateAlertsVisibility(); return Promise.resolve(0); }

    const namesSet = {};
    selectedOAEIds.forEach(id=>{
        const pl = getPolylineById(id);
        if (pl && pl.__oaeName) namesSet[pl.__oaeName] = true;
    });
    const names = Object.keys(namesSet);

    return loadLocalAlertsFC().then(fc=>{
        const feats = fc.features.filter(f=>{
            const n = (f.properties && (f.properties.oae_name || f.properties.name || '')) || '';
            return names.some(x => x.toLowerCase().trim() === n.toLowerCase().trim());
        });
        const count = _renderAlerts({type:'FeatureCollection', features:feats});
        setStatus('OAEs selecionadas: '+selectedOAEIds.length+' • Alertas: '+count);
        updateWazeUpdated();
        return count;
    }).catch(err=>{
        console.error('Erro ao ler alertas locais:', err);
        setStatus('Falha ao carregar alertas locais.');
        return 0;
    });
}

/* ===== Cadastrar OAE (modal) ===== */
function openNewOaeModal(){
    const modalEl = document.getElementById('modalNewOAE');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    const sel = document.getElementById('oae-type');
    const listTipos = tipos();
    sel.innerHTML = '';
    listTipos.forEach(t=>{
        const o = document.createElement('option');
        o.value = t.id; o.textContent = t.nome;
        sel.appendChild(o);
    });

    const presetList = document.getElementById('oae-preset-list');
    function renderPreset(){
        const m = tipoIndMap();
        const inds = indicadores();
        const ids = m[sel.value] || [];
        if (!ids.length){ presetList.innerHTML = '<span class="text-muted">Este tipo não possui indicadores pré-definidos.</span>'; return; }
        presetList.innerHTML = '<div class="small mb-1">Indicadores herdados:</div>' +
            ids.map(id => {
                const it = inds.find(x=>x.id===id);
                return `<span class="badge text-bg-light me-1 mb-1">${(it?.nome||id)}${it?` (${it.unidade})`:''}</span>`;
            }).join(' ');
    }
    sel.onchange = renderPreset;
    renderPreset();

    document.getElementById('btn-save-oae').onclick = function(){
        const name = (document.getElementById('oae-name').value||'').trim();
        const typeId = sel.value;
        if (!name){ alert('Informe o nome da OAE.'); return; }
        showToast({title:'OAE', message:`"${name}" cadastrada (Tipo: ${listTipos.find(t=>t.id===typeId)?.nome||typeId}).`, variant:'success'});
        modal.hide();
    };

    modal.show();
}

// Inicializa popovers (ícone de informação de "Lentidão por Nível")
window.addEventListener('load', function(){
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el){
        new bootstrap.Popover(el);
    });
});

