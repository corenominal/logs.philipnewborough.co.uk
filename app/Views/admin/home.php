<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            
            <div class="border-bottom border-1 mb-4 pb-4 d-flex align-items-center justify-content-between gap-3">
                <h2 class="mb-0">Admin Home</h2>
                <div class="" role="group" aria-label="Page actions">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" id="btn-level-filter" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-funnel-fill"></i><span class="d-none d-lg-inline"> <span id="level-filter-label">All Levels</span></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btn-level-filter">
                        <li><a class="dropdown-item active" href="#" data-level="">All Levels</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" data-level="0"><i class="bi bi-info-circle-fill me-1 text-info"></i> Info</a></li>
                        <li><a class="dropdown-item" href="#" data-level="1"><i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i> Warning</a></li>
                        <li><a class="dropdown-item" href="#" data-level="2"><i class="bi bi-exclamation-octagon-fill me-1 text-danger"></i> Error</a></li>
                        <li><a class="dropdown-item" href="#" data-level="3"><i class="bi bi-exclamation-octagon-fill me-1 text-danger"></i> Critical</a></li>
                        <li><a class="dropdown-item" href="#" data-level="4"><i class="bi bi-bug-fill me-1 text-primary"></i> Debug</a></li>
                    </ul>
                    <button type="button" class="btn btn-outline-primary" id="btn-datatable-refresh"><i class="bi bi-arrow-clockwise"></i><span class="d-none d-lg-inline"> Refresh</span></button>
                    <button type="button" class="btn btn-outline-danger" id="btn-delete" disabled><i class="bi bi-trash-fill"></i><span class="d-none d-lg-inline"> Delete<span class="selected-count"></span></span></button>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="text-decoration-none stat-filter-link" data-level="">
                        <div class="card h-100 text-center border-secondary">
                            <div class="card-body py-3">
                                <div class="fs-3 fw-bold"><?= esc($stats['total']) ?></div>
                                <div class="small text-secondary">Total</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="text-decoration-none stat-filter-link" data-level="0">
                        <div class="card h-100 text-center border-info">
                            <div class="card-body py-3">
                                <div class="fs-3 fw-bold text-info"><?= esc($stats['info']) ?></div>
                                <div class="small text-secondary"><i class="bi bi-info-circle-fill me-1 text-info"></i>Info</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="text-decoration-none stat-filter-link" data-level="1">
                        <div class="card h-100 text-center border-warning">
                            <div class="card-body py-3">
                                <div class="fs-3 fw-bold text-warning"><?= esc($stats['warning']) ?></div>
                                <div class="small text-secondary"><i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i>Warning</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="text-decoration-none stat-filter-link" data-level="2">
                        <div class="card h-100 text-center border-danger">
                            <div class="card-body py-3">
                                <div class="fs-3 fw-bold text-danger"><?= esc($stats['error']) ?></div>
                                <div class="small text-secondary"><i class="bi bi-exclamation-octagon-fill me-1 text-danger"></i>Error</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="text-decoration-none stat-filter-link" data-level="3">
                        <div class="card h-100 text-center border-danger">
                            <div class="card-body py-3">
                                <div class="fs-3 fw-bold text-danger"><?= esc($stats['critical']) ?></div>
                                <div class="small text-secondary"><i class="bi bi-exclamation-octagon-fill me-1 text-danger"></i>Critical</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="text-decoration-none stat-filter-link" data-level="4">
                        <div class="card h-100 text-center border-primary">
                            <div class="card-body py-3">
                                <div class="fs-3 fw-bold text-primary"><?= esc($stats['debug']) ?></div>
                                <div class="small text-secondary"><i class="bi bi-bug-fill me-1 text-primary"></i>Debug</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="logs-table" class="table table-bordered table-striped table-hover align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:1%"><input type="checkbox" class="form-check-input" id="checkbox-select-all"></th>
                            <th>Level</th>
                            <th>Message</th>
                            <th>Domain</th>
                            <th>Date / Time</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>

</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="modal-delete-confirm" tabindex="-1" aria-labelledby="modal-delete-confirm-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-delete-confirm-label"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete <strong><span id="delete-count"></span> log entry/entries</strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-delete-confirm"><i class="bi bi-trash-fill me-1"></i>Delete</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>