document.addEventListener("DOMContentLoaded", function() {
    const sidebarLinks = document.querySelectorAll("#sidebar .nav-link");
    sidebarLinks.forEach(link => {
        if (link.getAttribute("href") === "/admin") {
            link.classList.remove("text-white-50");
            link.classList.add("active");
        }
    });

    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const params = new URLSearchParams(window.location.search);
    const initialSearch = params.get("search") ?? "";
    let activeLevel = params.get("level") ?? "";

    const selectedIds = new Set();
    const btnDelete = document.getElementById("btn-delete");
    const levelLabels = { "": "All Levels", "0": "Info", "1": "Warning", "2": "Error", "3": "Critical", "4": "Debug" };

    function updateDeleteButton() {
        btnDelete.disabled = selectedIds.size === 0;
        btnDelete.querySelector(".selected-count").textContent =
            selectedIds.size > 0 ? " (" + selectedIds.size + ")" : "";
    }

    function syncLevelUI(level) {
        document.getElementById("level-filter-label").textContent = levelLabels[level] ?? "All Levels";
        document.querySelectorAll("[data-level]").forEach(el => {
            el.classList.toggle("active", el.dataset.level === level);
        });
    }

    function updateSelectAllCheckbox() {
        const checkboxes = document.querySelectorAll("#logs-table tbody .row-checkbox");
        const selectAll = document.getElementById("checkbox-select-all");
        if (!selectAll) return;
        const checkedCount = [...checkboxes].filter(c => c.checked).length;
        selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }

    syncLevelUI(activeLevel);

    let table = $("#logs-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/admin/datatable?timezone=" + encodeURIComponent(timezone),
            type: "GET",
            data: function(d) {
                if (activeLevel !== "") {
                    d.level = activeLevel;
                }
            }
        },
        columns: [
            { data: null, orderable: false, searchable: false, className: "text-center", defaultContent: '<input type="checkbox" class="form-check-input row-checkbox">' },
            { data: "level" },
            { data: "message" },
            { data: "domain" },
            { data: "created_at" }
        ],
        order: [[4, "desc"]],
        pageLength: 25,
        language: {},
        search: { search: initialSearch },
        drawCallback: function() {
            const api = this.api();
            api.rows().every(function() {
                const id = this.node().id;
                const checkbox = this.node().querySelector(".row-checkbox");
                const isSelected = selectedIds.has(id);
                if (checkbox) checkbox.checked = isSelected;
                $(this.node()).toggleClass("table-active", isSelected);
            });
            updateSelectAllCheckbox();
        }
    });

    $("#logs-table").on("search.dt", function() {
        const searchVal = table.search();
        const url = new URL(window.location);
        if (searchVal) {
            url.searchParams.set("search", searchVal);
        } else {
            url.searchParams.delete("search");
        }
        window.history.replaceState(null, "", url);
    });

    $("#logs-table tbody").on("click", "tr", function() {
        const id = this.id;
        if (!id) return;
        const checkbox = this.querySelector(".row-checkbox");
        if (selectedIds.has(id)) {
            selectedIds.delete(id);
            if (checkbox) checkbox.checked = false;
            $(this).removeClass("table-active");
        } else {
            selectedIds.add(id);
            if (checkbox) checkbox.checked = true;
            $(this).addClass("table-active");
        }
        updateDeleteButton();
        updateSelectAllCheckbox();
    });

    document.getElementById("checkbox-select-all").addEventListener("change", function() {
        const checked = this.checked;
        table.rows({ page: "current" }).every(function() {
            const id = this.node().id;
            const checkbox = this.node().querySelector(".row-checkbox");
            if (checked) {
                selectedIds.add(id);
                if (checkbox) checkbox.checked = true;
                $(this.node()).addClass("table-active");
            } else {
                selectedIds.delete(id);
                if (checkbox) checkbox.checked = false;
                $(this.node()).removeClass("table-active");
            }
        });
        updateDeleteButton();
    });

    document.querySelectorAll("[data-level]").forEach(function(el) {
        el.addEventListener("click", function(e) {
            e.preventDefault();
            activeLevel = this.dataset.level;
            syncLevelUI(activeLevel);
            syncStatCards(activeLevel);
            const url = new URL(window.location);
            if (activeLevel !== "") {
                url.searchParams.set("level", activeLevel);
            } else {
                url.searchParams.delete("level");
            }
            window.history.replaceState(null, "", url);
            table.ajax.reload();
        });
    });

    function syncStatCards(level) {
        document.querySelectorAll(".stat-filter-link").forEach(el => {
            el.querySelector(".card").classList.toggle("opacity-50", el.dataset.level !== level);
        });
    }

    function refreshStats() {
        fetch("/admin/stats")
            .then(res => res.json())
            .then(data => {
                const map = { "": "total", "0": "info", "1": "warning", "2": "error", "3": "critical", "4": "debug" };
                document.querySelectorAll(".stat-filter-link").forEach(el => {
                    const key = map[el.dataset.level];
                    if (key !== undefined) {
                        el.querySelector(".fs-3").textContent = data[key] ?? 0;
                    }
                });
            })
            .catch(err => console.error("Stats refresh failed:", err));
    }

    syncStatCards(activeLevel);

    const deleteModal = document.getElementById("modal-delete-confirm");
    deleteModal.addEventListener("hide.bs.modal", function() {
        document.activeElement?.blur();
    });

    btnDelete.addEventListener("click", function() {
        document.getElementById("delete-count").textContent = selectedIds.size;
        new bootstrap.Modal(deleteModal).show();
    });

    document.getElementById("btn-delete-confirm").addEventListener("click", function() {
        const ids = [...selectedIds];
        fetch("/admin/logs/delete", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ ids })
        })
        .then(res => {
            if (!res.ok) throw new Error("Delete failed");
            return res.json();
        })
        .then(() => {
            bootstrap.Modal.getInstance(deleteModal).hide();
            selectedIds.clear();
            updateDeleteButton();
            table.ajax.reload();
            refreshStats();
        })
        .catch(err => {
            console.error(err);
            bootstrap.Modal.getInstance(deleteModal).hide();
        });
    });

    document.getElementById("btn-datatable-refresh").addEventListener("click", function() {
        table.ajax.reload();
        refreshStats();
    });
});