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
        .table-sm td, .table-sm th{ vertical-align:middle; }


        /* ===== Regras (tabela mais legível e responsiva) ===== */
        #panel-alerts .rules-wrap{ position:relative; }

        #panel-alerts table.rules-table{
            border-collapse:separate; border-spacing:0; width:100%;
        }
        #panel-alerts table.rules-table thead th{
            font-size:.8rem; text-transform:uppercase; letter-spacing:.02em;
            color:#6b7280; background:#f8f9fb; position:sticky; top:0; z-index:1;
        }
        #panel-alerts table.rules-table td,
        #panel-alerts table.rules-table th{
            vertical-align:middle; padding:.65rem .75rem;
        }
        #panel-alerts table.rules-table tbody tr{
            background:#fff;
        }
        #panel-alerts table.rules-table tbody tr+tr td{
            border-top:1px solid rgba(0,0,0,.06);
        }

        /* colunas com largura mínima para não “quebrar” o layout */
        #panel-alerts .col-id{ width:56px; }
        #panel-alerts .col-scope{ min-width:220px; }
        #panel-alerts .col-ind{ min-width:220px; }
        #panel-alerts .col-cond{ width:110px; text-align:center; }
        #panel-alerts .col-thr{ width:150px; }
        #panel-alerts .col-dest{ min-width:160px; }
        #panel-alerts .col-ch{ width:170px; }
        #panel-alerts .col-sil{ width:110px; }
        #panel-alerts .col-st{ width:92px; text-align:center; }
        #panel-alerts .col-act{ width:170px; }

        /* texto multi-linha sem colidir */
        #panel-alerts .cell-wrap{ white-space:normal; line-height:1.25; }
        #panel-alerts .small-muted{ color:#6b7280; font-size:.85rem; }

        /* grupo de botões/ícones com tamanho padronizado */
        .btn-ico{
            --size:34px;
            width:var(--size); height:var(--size);
            display:inline-flex; align-items:center; justify-content:center;
            padding:0; border-radius:.55rem;
        }
        .btn-ico i{ font-size:1rem; }

        /* badges “pill” para canais/destinatários */
        .badge-pill{
            border-radius:999px; padding:.35rem .6rem; font-weight:600;
        }

        /* switch mais firme */
        .form-switch .form-check-input{ transform:scale(1.05); cursor:pointer; }

        /* ===== Mobile: vira cartões (<= 768px) ===== */
        @media (max-width: 768px){
            #panel-alerts table.rules-table{ display:none; }
            #panel-alerts .rules-cards{ display:grid; gap:.65rem; }
            #panel-alerts .rule-card{
                background:#fff; border:1px solid rgba(0,0,0,.08);
                border-radius:.75rem; padding:.75rem; box-shadow:0 2px 8px rgba(0,0,0,.05);
            }
            #panel-alerts .rule-card .line{
                display:flex; justify-content:space-between; align-items:center; gap:.75rem;
                padding:.25rem 0;
            }
            #panel-alerts .rule-card .line+.line{ border-top:1px dashed rgba(0,0,0,.08); }
            #panel-alerts .rule-card .title{ font-weight:700; }
            #panel-alerts .rule-card .meta{ color:#6b7280; font-size:.9rem; }
            #panel-alerts .rule-card .actions{ display:flex; gap:.4rem; }
        }

        /* ===== Regras em cards ===== */
        .rules-cards{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
            gap:.85rem;
        }
        .rule-card{
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:.8rem;
            padding:.9rem .95rem;
            box-shadow:0 2px 10px rgba(0,0,0,.06);
        }
        .rule-head{ display:flex; align-items:center; justify-content:space-between; gap:.5rem; }
        .rule-scope{ font-weight:600; line-height:1.2; }
        .rule-ind{ color:#4b5563; margin:.15rem 0 .5rem; }
        .rule-metas{ display:flex; flex-wrap:wrap; gap:.35rem; margin-bottom:.5rem; }
        .rule-metas .badge{ font-weight:600; }
        .rule-channels{ display:flex; gap:.35rem; flex-wrap:wrap; }
        .rule-actions{ margin-top:.75rem; }
        .rule-actions .btn{ width:100%; }

        /* ===== Detalhe da condição (cards) ===== */
        .rule-condition{
            background:#f8fafc;
            border:1px solid #e5e7eb;
            padding:.55rem .65rem;
            border-radius:.6rem;
            font-size:.95rem;
            line-height:1.2;
            margin:.4rem 0 .6rem;
        }
        .rule-condition .lab{ color:#475569; margin-right:.35rem; font-weight:600; }
        .rule-condition .ind{ font-weight:700; }
        .rule-condition .op{ font-weight:700; }
        .rule-condition .thr{ font-weight:700; }
        .rule-condition .unit{ color:#64748b; margin-left:.2rem; }

        /* Esconde o cabeçalho da tabela no painel de Regras (cards) */
        #panel-alerts table thead{ display:none !important; }


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
                <div id="alerts-summary" class="mb-3">
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

            <!-- ===== Painel: GERENCIAR ALERTAS DE OAEs (CRUD) ===== -->
            <div id="panel-alerts" class="d-none">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">Regras de alerta por indicador</div>
                    <button id="btn-new-rule" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Nova regra</button>
                </div>
                <div class="d-flex gap-2 mb-2">
                    <input id="rule-search" class="form-control form-control-sm" placeholder="Buscar por OAE, Tipo ou Indicador…">
                    <select id="rule-filter-status" class="form-select form-select-sm" style="max-width:160px">
                        <option value="all">Todas</option>
                        <option value="on">Ativas</option>
                        <option value="off">Inativas</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Escopo</th>
                            <th>Indicador</th>
                            <th>Condição</th>
                            <th>Limite</th>
                            <th>Destinatários</th>
                            <th>Canais</th>
                            <th>Silêncio</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                        </thead>
                        <tbody id="rules-body">
                        <tr><td colspan="10" class="text-muted">Nenhuma regra cadastrada.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== Painel Gerenciar Tipos & Indicadores ===== -->
            <div id="panel-ind" class="d-none">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">Cadastro e herança de indicadores</div>
                    <button id="btn-new-oae" class="btn btn-sm btn-primary">Cadastrar OAE</button>
                </div>
                <div class="small text-muted mb-2">Associe os tipos, indicadores e cadastre OAEs com auto-preenchimento.</div>

                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <button id="btn-new-type" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i> Novo tipo</button>
                    <input id="type-search" class="form-control form-control-sm" style="max-width:220px" placeholder="Buscar tipo…">
                </div>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                        <tr>
                            <th>#</th><th>Tipo</th><th>Descrição</th><th>Indicadores</th><th class="text-end">Ações</th>
                        </tr>
                        </thead>
                        <tbody id="types-body"><tr><td colspan="5" class="text-muted">Carregando…</td></tr></tbody>
                    </table>
                </div>
                <div class="panel-pad"></div>
            </div>
        </div>
    </aside>

    <div class="rail">
        <button id="btn-toggle" class="rail-btn" title="Abrir/fechar painel"><i class="bi bi-chevron-left"></i></button>
        <button id="btn-oaes" class="rail-btn primary" title="OAEs"><i class="bi bi-building"></i></button>
        <button id="btn-bell" class="rail-btn" title="Gerenciar Alertas de OAEs"><i class="bi bi-bell-fill"></i></button>
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

<!-- === Modal: Tipo (novo/editar) === -->
<div class="modal fade" id="modalTipo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tipo de OAE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tipo-id">
                <div class="mb-2">
                    <label class="form-label">Nome</label>
                    <input id="tipo-nome" class="form-control">
                </div>
                <div>
                    <label class="form-label">Descrição</label>
                    <textarea id="tipo-desc" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-save-tipo" class="btn btn-primary">Salvar</button>
            </div>
        </div></div>
</div>

<!-- === Offcanvas: Indicadores por Tipo === -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="ocIndicadores">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Indicadores do Tipo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div id="oc-indicadores-body">Carregando…</div>
        <div class="mt-3 text-end">
            <button class="btn btn-secondary" data-bs-dismiss="offcanvas">Fechar</button>
            <button id="btn-oc-ind-save" class="btn btn-primary">Salvar</button>
        </div>
    </div>
</div>

<!-- === Modal: Regra (novo/editar) === -->
<div class="modal fade" id="modalRegra" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Regra de Alerta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rule-id">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#escopo-tipo" type="button">Por Tipo</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#escopo-oae" type="button">Por OAE</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="escopo-tipo">
                        <div class="mb-2">
                            <label class="form-label">Tipo</label>
                            <select id="rule-tipoId" class="form-select"></select>
                            <div class="form-text">Indicadores listados abaixo respeitam os vinculados ao Tipo.</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="escopo-oae">
                        <div class="mb-2">
                            <label class="form-label">OAEs</label>
                            <input id="rule-oae-input" class="form-control" placeholder="Digite o nome da OAE e pressione Enter">
                            <div id="rule-oae-chips" class="mt-2 d-flex flex-wrap gap-1"></div>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label">Indicador</label>
                        <select id="rule-indicador" class="form-select"></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Condição</label>
                        <select id="rule-cond" class="form-select">
                            <option value="<">&lt; menor que</option>
                            <option value=">">&gt; maior que</option>
                            <option value="=">= igual a</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Threshold</label>
                        <div class="input-group">
                            <input id="rule-threshold" type="number" step="any" class="form-control">
                            <span class="input-group-text" id="rule-unit">—</span>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Destinatários (placeholder)</label>
                        <input id="rule-dest" class="form-control" placeholder="Ex.: Equipe de Operação">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Canais</label>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rule-ch-tela" checked>
                                <label class="form-check-label" for="rule-ch-tela">Alerta em Tela</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rule-ch-email">
                                <label class="form-check-label" for="rule-ch-email">E-mail</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rule-ch-sms">
                                <label class="form-check-label" for="rule-ch-sms">SMS</label>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    <label class="form-label">Janela de silêncio (anti-spam)</label>
                    <select id="rule-silence" class="form-select">
                        <option value="5">5 min</option>
                        <option value="15">15 min</option>
                        <option value="30" selected>30 min</option>
                        <option value="60">60 min</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-save-rule" class="btn btn-primary">Salvar</button>
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
    var selectedOAEIds = [];
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

        // chips OAEs
        var input = document.getElementById('oae-input');
        input.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); tryAddOAE(input.value); }});
        input.addEventListener('change', function(){ tryAddOAE(input.value); });
        input.addEventListener('focus', openPanel);

        fillTrafficSummary();
        updateWazeUpdated();
        fetchOAEs();

        // Tipos & Indicadores (seed + render)
        ensureSeeds();
        renderTypesTable();

        // Regras
        document.getElementById('btn-new-rule').onclick = function(){ openRuleModal(); };
        document.getElementById('rule-search').oninput = renderRulesTable;
        document.getElementById('rule-filter-status').onchange = renderRulesTable;

        // Tipos CRUD
        document.getElementById('btn-new-type').onclick = function(){ openTipoModal(); };
        document.getElementById('type-search').oninput = renderTypesTable;

        // Cadastrar OAE (exibe herança)
        document.getElementById('btn-new-oae').onclick = openNewOaeModal;
    }

    const CLICK_TOLERANCE_M = 8;

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
        setStatus('OAEs selecionadas: ' + selectedOAEIds.length);
    }
    function removeOAEById(id){
        selectedOAEIds = selectedOAEIds.filter(x=>x!==id);
        renderChips();
        if (oaeAreaRectsById[id]) { oaeAreaRectsById[id].setMap(null); delete oaeAreaRectsById[id]; }
        var pl = getPolylineById(id); if (pl) setSelectedStyle(pl, false);
        fitToSelectedOAEs({ maxZoom: 15 });
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
        setStatus('Filtro limpo. Selecione OAEs para ver alertas.');
    }

    /* ===== Util ===== */
    function clearAll(){
        oaeLayers.forEach(function(l){ l.setMap(null); }); oaeLayers.length=0;
        for(var k in typePolylines){ if(typePolylines.hasOwnProperty(k)) delete typePolylines[k]; }
        for (var id in oaeAreaRectsById){ if (oaeAreaRectsById[id]) oaeAreaRectsById[id].setMap(null); }
        oaeAreaRectsById = {};
        clearOaeFilter();
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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
</script>

<script>
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
</script>

<script>
    /* ======= Tipos & Indicadores (PoC 1) ======= */
    function renderTypesTable(){
        const body = document.getElementById('types-body'); if(!body) return;
        const q = (document.getElementById('type-search')?.value || '').toLowerCase();
        const list = tipos().filter(t => !q || t.nome.toLowerCase().includes(q));

        if(!list.length){ body.innerHTML='<tr><td colspan="5" class="text-muted">Nenhum tipo.</td></tr>'; return; }

        const map = tipoIndMap(); const indsById = Object.fromEntries(indicadores().map(i=>[i.id,i]));
        body.innerHTML='';
        list.forEach((t,idx)=>{
            const inds = (map[t.id]||[]).map(id=>indsById[id]?.nome||id);
            const tr = document.createElement('tr');
            tr.innerHTML = `
      <td>${idx+1}</td>
      <td>${t.nome}</td>
      <td class="text-muted small">${t.desc||''}</td>
      <td><span class="badge text-bg-secondary">${(map[t.id]||[]).length}</span></td>
      <td class="text-end">
        <button class="btn btn-sm btn-outline-primary me-1" data-act="ind" data-id="${t.id}"><i class="bi bi-sliders"></i> Indicadores</button>
        <button class="btn btn-sm btn-outline-secondary me-1" data-act="edit" data-id="${t.id}"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" data-act="del" data-id="${t.id}"><i class="bi bi-trash"></i></button>
      </td>`;
            body.appendChild(tr);
        });
        body.onclick = function(ev){
            const btn = ev.target.closest('button[data-act]'); if(!btn) return;
            const id = btn.getAttribute('data-id'), act = btn.getAttribute('data-act');
            if(act==='edit') openTipoModal(id);
            if(act==='del') { const arr=tipos().filter(t=>t.id!==id); lsSet(LS_TIPOS, arr); renderTypesTable(); showToast({title:'Excluído',variant:'secondary',message:'Tipo removido.'}); }
            if(act==='ind') openIndicadoresCanvas(id);
        }
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
        const map = tipoIndMap(); const selected = new Set(map[tipoId]||[]);
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

    /* ======= Regras (PoC 2) ======= */
    const UNIT_BY_IND = Object.fromEntries(indicadores().map(i=>[i.id,i.unidade]));
    function renderRulesTable(){
        const tbody = document.getElementById('rules-body'); if(!tbody) return;

        // ===== filtros / dados base
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

        // ===== vazio
        if (!list.length){
            tbody.innerHTML = '<tr><td colspan="10" class="text-muted py-4 text-center">Nenhuma regra cadastrada.</td></tr>';
            return;
        }

        // ===== cards dentro do tbody (1 linha, 1 célula ocupando a tabela toda)
        tbody.innerHTML = '';
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 10;
        td.innerHTML = '<div class="rules-cards"></div>';
        tr.appendChild(td);
        tbody.appendChild(tr);

        const grid = td.firstElementChild;

        // helper p/ badge de canal
        const channelBadge = (on, label) =>
            `<span class="badge ${on ? 'text-bg-primary' : 'text-bg-secondary'}">${label}</span>`;

        list.forEach((r) => {
            const scopeTxt = r.escopo?.tipoId
                ? `Tipo: ${tps[r.escopo.tipoId]?.nome || r.escopo.tipoId}`
                : `OAEs: ${(r.escopo?.oaeNames||[]).join(', ') || '—'}`;

            const indNome = inds[r.indicadorId]?.nome || r.indicadorId;
            const unit = (window.UNIT_BY_IND ? (UNIT_BY_IND[r.indicadorId]||'') : (inds[r.indicadorId]?.unidade||'')) || '';
            const ch = r.canais || {tela:true,email:false,sms:false};

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
        ${channelBadge(!!ch.tela,  'Tela')}
        ${channelBadge(!!ch.email, 'E-mail')}
        ${channelBadge(!!ch.sms,   'SMS')}
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

        // ===== delegação de eventos (editar / excluir / testar / toggle)
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
            document.getElementById('rule-unit').textContent =
                (indicadores().find(i=>i.id=== (r.indicadorId||list[0]?.id)) || list[0] || {unidade:'—'}).unidade || '—';
            selInd.onchange = function(){
                const it = indicadores().find(i=>i.id===this.value);
                document.getElementById('rule-unit').textContent = it?it.unidade:'—';
            };
            document.getElementById('rule-indicador').value =
                (r.indicadorId && list.some(i=>i.id===r.indicadorId)) ? r.indicadorId : list[0]?.id;
        }
        selTipo.onchange = refreshIndicadores; refreshIndicadores();

        // valores
        document.getElementById('rule-id').value = r.id;
        document.getElementById('rule-cond').value = r.condicao;
        document.getElementById('rule-threshold').value = r.threshold;
        document.getElementById('rule-dest').value = (r.destinatarios||[]).join(', ');
        document.getElementById('rule-silence').value = r.janelaSilencioMin||30;

        // canais (agora todos habilitados visualmente)
        document.getElementById('rule-ch-tela').checked  = !!(r.canais?.tela);
        document.getElementById('rule-ch-email').checked = !!(r.canais?.email);
        document.getElementById('rule-ch-sms').checked   = !!(r.canais?.sms);

        // escopo por OAE (chips simples)
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

            // escopo
            const isOaeTab = document.querySelector('#escopo-oae').classList.contains('active');
            const oaeNames = Array.from(chips.querySelectorAll('.chip span:first-child')).map(x=>x.textContent);
            const escopo = (isOaeTab && oaeNames.length) ? {oaeIds:[], oaeNames} : {tipoId: selTipo.value};

            const indId = document.getElementById('rule-indicador').value;
            const cond  = document.getElementById('rule-cond').value;
            const thr   = parseFloat(document.getElementById('rule-threshold').value||'0');
            const dest  = document.getElementById('rule-dest').value.trim()
                ? document.getElementById('rule-dest').value.split(',').map(s=>s.trim())
                : ['Operação'];

            // >>> LER OS TRÊS CHECKBOXES <<<
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
        // valor simulado (para PoC). Se indicador for velocidade, gera 12–40 km/h; se índice, 10–80%; senão 1–10.
        let val = 0, unit = UNIT_BY_IND[r.indicadorId]||'';
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
</script>

<script>
    window.addEventListener('load', initMap);
</script>

<script src="https://maps.google.com/maps/api/js?v=beta&libraries=visualization,drawing,geometry,places&key=AIzaSyCd3zT_keK2xr7T6ujvR3TvLj5c9u0PtsM&callback=Function.prototype"></script>

<script>
    // === PATCH: fazer os alertas aparecerem novamente usando o arquivo local ===

    // caminho do arquivo local com alertas/jams
    const DATA_ALERTS = 'data/obras-arte-alerts-jams.json';

    // cache em memória para não reler toda hora
    let __alertsFC = null;

    // garante que o painel de alertas não fique escondido
    window.addEventListener('load', function(){
        const box = document.getElementById('alerts-summary');
        if (box) box.classList.remove('d-none');
    });

    // ---- loader/normalizador do arquivo local
    function loadLocalAlertsFC(){
        if (__alertsFC) return Promise.resolve(__alertsFC);
        return fetch(DATA_ALERTS).then(r=>r.json()).then(json=>{
            const features = [];

            // Caso 1: já venha como FeatureCollection
            if (json && json.type && /featurecollection/i.test(json.type) && Array.isArray(json.features)) {
                json.features.forEach(f=>{
                    if (f && f.geometry && f.geometry.type === 'Point') features.push(f);
                });
            }

            // Caso 2: estrutura { alerts:[], jams:[] } (igual ao mock antigo)
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

    // ---- helpers de categoria/ícone (usa CAT_STYLE já existente)
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

    // ---- estado de marcadores por categoria (reutiliza arrays globais já existentes)
    function _resetMarkers(){
        for (var k in markersByCat){
            (markersByCat[k]||[]).forEach(m=>m.setMap(null));
            markersByCat[k] = [];
        }
        alertMarkers.forEach(m=>m.setMap(null));
        alertMarkers.length = 0;
    }

    // ---- render no mapa + contadores
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

        // atualiza contadores
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
            // binds dos switches
            const sws = box.querySelectorAll('.cat-toggle');
            sws.forEach(sw=>{
                sw.onchange = function(ev){
                    const cat = ev.target.closest('[data-cat]')?.getAttribute('data-cat');
                    const on  = ev.target.checked && layersEnabled.alerts;
                    (markersByCat[cat]||[]).forEach(m=>m.setMap(on?map:null));
                };
            });
        }

        // aplica visibilidade atual
        (function applyVisibility(){
            const b = document.getElementById('alerts-summary');
            function isOn(cat){
                const sw = b ? b.querySelector('[data-cat="'+cat+'"] .cat-toggle') : null;
                return layersEnabled.alerts && (!sw || sw.checked);
            }
            for (var cat in markersByCat){
                (markersByCat[cat]||[]).forEach(m=>m.setMap(isOn(cat)?map:null));
            }
        })();

        return alertMarkers.length;
    }

    // ---- função que filtra pelo(s) nome(s) das OAEs selecionadas e renderiza
    function fetchAlertsForSelected(){
        if(!selectedOAEIds.length){ _resetMarkers(); return Promise.resolve(0); }

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

    // ---- sobrescreve duas funções para acionar os alertas
    (function overrideSelectionFns(){
        // guarda referências antigas (se precisar)
        const _oldAdd = addOAEByPolyline;
        const _oldClear = clearOaeFilter;

        // substitui addOAEByPolyline para também buscar alertas
        addOAEByPolyline = function(pl, zoom){
            // reaproveita lógica original:
            if (selectedOAEIds.indexOf(pl.__id) !== -1) return;
            selectedOAEIds.push(pl.__id);
            renderChips();
            if (typeState[pl.__oaeType] === false) {
                typeState[pl.__oaeType] = true; updateOAEsVisibility();
                var id = 't_' + btoa(pl.__oaeType).replace(/=/g,''); var cb = document.getElementById(id); if (cb) cb.checked = true;
            }
            setSelectedStyle(pl, true);
            drawAreaForPolyline(pl, 500);
            if (zoom) fitToSelectedOAEs({ maxZoom: 15 });
            setStatus('OAEs selecionadas: ' + selectedOAEIds.length);

            // NOVO: carrega e mostra alertas relativos às OAEs selecionadas
            fetchAlertsForSelected();
        };

        // substitui clearOaeFilter para também limpar alertas
        clearOaeFilter = function(){
            selectedOAEIds.slice().forEach(function(id){
                selectedOAEIds = selectedOAEIds.filter(x=>x!==id);
                if (oaeAreaRectsById[id]) { oaeAreaRectsById[id].setMap(null); delete oaeAreaRectsById[id]; }
                var pl = getPolylineById(id); if (pl) setSelectedStyle(pl, false);
            });
            renderChips();
            // limpa marcadores de alerta
            _resetMarkers();
            setStatus('Filtro limpo. Selecione OAEs para ver alertas.');
        };
    })();
</script>


<div id="toast-ctr" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;"></div>

</body>
</html>
