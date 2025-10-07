<?php /* SIIM • OAEs + Alertas + Gerenciar Tipos/Indicadores + Monitoramento */ ?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIIM</title>

    <!-- CSS de terceiros -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Seu CSS -->
    <link rel="stylesheet" href="assets/css/siim.css?v=1">
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
                    <label class="form-label">OAE</label>
                    <div class="input-group">
                        <input id="oae-input" class="form-control" placeholder="Digite para buscar uma OAE..." list="oaes-list" autocomplete="off">
                        <button id="oae-clear" class="btn btn-outline-secondary" type="button">Limpar</button>
                    </div>
                    <datalist id="oaes-list"></datalist>
                    <div id="oae-picked" class="form-text text-muted mt-1">Nenhuma OAE selecionada.</div>
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
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                            Lentidão por Nível
                            <i
                                    class="bi bi-info-circle-fill text-secondary cursor-help"
                                    role="button"
                                    tabindex="0"
                                    data-bs-toggle="popover"
                                    data-bs-trigger="hover focus"
                                    data-bs-placement="left"
                                    data-bs-html="true"
                                    data-bs-custom-class="traffic-popover"
                                    data-bs-content="
      <div class='text-start'>
        <div class='mb-2 fw-bold'>Como ler os níveis:</div>
        <div class='mb-1'>
          <span class='lvl-pill lvl-leve'>Leve</span>
          Pequeno atraso — <i>61% a 80%</i>. A velocidade média está um pouco abaixo do normal.
        </div>
        <div class='mb-1'>
          <span class='lvl-pill lvl-moderado'>Moderado</span>
          Tráfego moderado — <i>41% a 60%</i>. Lentidão significativa.
        </div>
        <div class='mb-1'>
          <span class='lvl-pill lvl-intenso'>Intenso</span>
          Trânsito pesado — <i>21% a 40%</i>. Congestionamento notável.
        </div>
        <div class='mb-1'>
          <span class='lvl-pill lvl-mintenso'>Muito intenso</span>
          Quase parado — <i>1% a 20%</i>. Veículos se movendo muito lentamente.
        </div>
        <div>
          <span class='lvl-pill lvl-extremo'>Extremo</span>
          Via bloqueada/interditada. Tráfego praticamente parado.
        </div>
      </div>
    "
                            ></i>
                        </div>

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
        <button id="btn-oaes" class="rail-btn" title="OAEs"><i class="bi bi-building"></i></button>
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
                            <!-- Ajuste: datalist para sugestões -->
                            <input id="rule-oae-input" class="form-control"
                                   placeholder="Digite o nome da OAE e selecione"
                                   list="rule-oaes-datalist" autocomplete="off">
                            <datalist id="rule-oaes-datalist"></datalist>

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
                            <option value="<"> menor que</option>
                            <option value=">"> maior que</option>
                            <option value="=">igual a</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Limite</label>
                        <div class="input-group">
                            <input id="rule-threshold" type="number" step="any" class="form-control">
                            <span class="input-group-text" id="rule-unit">—</span>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Destinatários</label>
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
                    <label class="form-label">Silenciar por:</label>
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

<div id="toast-ctr" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;"></div>

<!-- ===== SCRIPTS ===== -->
<!-- Bootstrap JS (bundle com Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Google Maps API (necessário para o mapa e para geometry.* usado no seu JS) -->
<script src="https://maps.google.com/maps/api/js?v=beta&libraries=visualization,drawing,geometry,places&key=AIzaSyCd3zT_keK2xr7T6ujvR3TvLj5c9u0PtsM&callback=Function.prototype"></script>

<!-- Seu JS (depois das libs) -->
<script src="assets/js/siim.js?v=1"></script>

</body>
</html>
