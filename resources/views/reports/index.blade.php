@extends('layouts.app')

@section('content')
<div id="reports-view" class="view active">

    {{-- =======================
        FILTERS + EXPORT
    ======================== --}}
    <div class="card">
        <div class="report-header">
            <div>
                <h2>Monthly Report</h2>
            </div>
            <button class="btn btn-success" onclick="exportCSV()">📥 Export CSV</button>
        </div>
 
        <div class="filter-panel">
 
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Date Range</label>
                    <div class="btn-group" id="dateRangeGroup">
                        <button class="range-btn active" data-range="this_month">This Month</button>
                        <button class="range-btn" data-range="last_3">Last 3 Months</button>
                        <button class="range-btn" data-range="last_6">Last 6 Months</button>
                        <button class="range-btn" data-range="custom">Custom</button>
                    </div>
                </div>
 
                <div class="filter-group">
                    <label class="filter-label">Report Type</label>
                    <select id="reportType" class="select-control">
                        <option value="all">All Washes</option>
                        <option value="company">Company Accounts Only</option>
                        <option value="cash">Cash Only</option>
                        <option value="vehicle">By Vehicle / Registration</option>
                    </select>
                </div>
            </div>
 
            <div class="filter-row" id="monthYearRow">
                <div class="filter-group">
                    <label class="filter-label">Month</label>
                    <select id="reportMonth" class="select-control">
                        @foreach(['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'] as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Year</label>
                    <select id="reportYear" class="select-control">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
 
            <div class="filter-row" id="customDateRow" style="display:none;">
                <div class="filter-group">
                    <label class="filter-label">From</label>
                    <input type="date" id="dateFrom" class="select-control" />
                </div>
                <div class="filter-group">
                    <label class="filter-label">To</label>
                    <input type="date" id="dateTo" class="select-control" />
                </div>
            </div>
 
            <div class="filter-row" id="companyFilterRow" style="display:none;">
                <div class="filter-group">
                    <label class="filter-label">Company</label>
                    <select id="companyFilter" class="select-control">
                        <option value="">All Companies</option>
                    </select>
                </div>
            </div>

            <div class="filter-row" id="vehicleFilterRow" style="display:none;">
                <div class="filter-group">
                    <label class="filter-label">Registration</label>
                    <input type="text" id="vehicleFilter" class="select-control" placeholder="e.g. AB12 CDE" style="text-transform:uppercase;" />
                </div>
            </div>
 
            <div class="filter-row" style="align-items:center; margin-top: 4px;">
                <button class="btn btn-secondary" onclick="fetchReportData()">🔍 Load Report</button>
                <p id="reportMonthLabel" style="color: #94a3b8; margin: 0;"></p>
            </div>
        </div>

        <div id="reportLoader" class="loader" style="display:none;"></div>
 
        <div class="stats-grid">
            <div class="stat-card teal">
                <div class="stat-label">Company Accounts</div>
                <div class="stat-value" id="reportCompanyTotal">£0.00</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Cash Takings</div>
                <div class="stat-value" id="reportCashTotal">£0.00</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Washes</div>
                <div class="stat-value" id="reportTotalWashes">0</div>
            </div>
        </div>
    </div>

    {{-- =======================
        COMPANY SUMMARY TABLE 
    ======================== --}}
    <div class="card" id="companySummaryCard">
        <h3 style="margin-bottom: 20px;">Company Account Summary</h3>
        <div class="table-container">
            <table id="companySummaryTable">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Washes</th>
                        <th>£/Wash</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- =======================
        ALL WASHES TABLE
    ======================== --}}
    <div class="card">
        <h3 style="margin-bottom: 20px;" id="allWashesTitle">All Washes</h3>

        <div class="table-container">
            <table id="allWashesTable">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Registration</th>
                        <th>Driver</th>
                        <th>Company/Cash</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="washPagination" class="pagination-controls">
            <button class="page-btn" id="prevWashPage" disabled>Previous</button>
            <span id="currentWashPage">1</span> / <span id="totalWashPages">1</span>
            <button class="page-btn" id="nextWashPage" disabled>Next</button>
        </div>
    </div>

</div>
@endsection



@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ──────────────────────────────────────────
    // STATE
    // ──────────────────────────────────────────
    let allWashData    = [];
    let currentWashPage = 1;
    let rowsPerPageWash = 15;
    let totalWashPages  = 1;
    let activeRange     = 'this_month';

    // ──────────────────────────────────────────
    // INIT: set defaults
    // ──────────────────────────────────────────
    const now = new Date();
    document.getElementById('reportMonth').value = ("0" + (now.getMonth() + 1)).slice(-2);
    document.getElementById('reportYear').value  = now.getFullYear();
 
    const todayStr      = now.toISOString().split('T')[0];
    const firstOfMonth  = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    document.getElementById('dateFrom').value = firstOfMonth;
    document.getElementById('dateTo').value   = todayStr;
 
    fetch("{{ route('reports.companies') }}")
        .then(r => r.json())
        .then(companies => {
            const sel = document.getElementById('companyFilter');
            companies.forEach(c => {
                sel.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
        })
        .catch(() => {});  

    // ──────────────────────────────────────────
    // DATE RANGE TOGGLE BUTTONS
    // ──────────────────────────────────────────
    document.querySelectorAll('.range-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.range-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeRange = this.dataset.range;

            const isCustom = activeRange === 'custom';
            document.getElementById('customDateRow').style.display  = isCustom ? 'flex' : 'none';
            document.getElementById('monthYearRow').style.display   = isCustom ? 'none' : 'flex';
        });
    });

    // ──────────────────────────────────────────
    // REPORT TYPE → show/hide sub-filters
    // ──────────────────────────────────────────
    document.getElementById('reportType').addEventListener('change', function () {
        const val = this.value;

        document.getElementById('companyFilterRow').style.display = (val === 'company') ? 'flex' : 'none';
        document.getElementById('vehicleFilterRow').style.display  = (val === 'vehicle')  ? 'flex' : 'none';
        document.getElementById('companySummaryCard').style.display = (val === 'cash' || val === 'vehicle') ? 'none' : '';

        const titles = {
            all: 'All Washes',
            company: 'Company Account Washes',
            cash: 'Cash Washes',
            vehicle: 'Vehicle Washes',
        };
        document.getElementById('allWashesTitle').innerText = titles[val] || 'All Washes';
    });

    // ──────────────────────────────────────────
    // BUILD QUERY URL from current filters
    // ──────────────────────────────────────────
    function buildUrl() {
        const params = new URLSearchParams();
        const type   = document.getElementById('reportType').value;

        params.set('type', type);

        if (activeRange === 'custom') {
            params.set('from', document.getElementById('dateFrom').value);
            params.set('to',   document.getElementById('dateTo').value);
        } else if (activeRange === 'last_3') {
            const d = new Date(); d.setMonth(d.getMonth() - 3);
            params.set('from', d.toISOString().split('T')[0]);
            params.set('to',   new Date().toISOString().split('T')[0]);
        } else if (activeRange === 'last_6') {
            const d = new Date(); d.setMonth(d.getMonth() - 6);
            params.set('from', d.toISOString().split('T')[0]);
            params.set('to',   new Date().toISOString().split('T')[0]);
        } else {
            // this_month → use month/year selects
            const month = document.getElementById('reportMonth').value;
            const year  = document.getElementById('reportYear').value;
            params.set('month', year + '-' + month);
        }

        if (type === 'company') {
            const co = document.getElementById('companyFilter').value;
            if (co) params.set('company_id', co);
        }

        if (type === 'vehicle') {
            const reg = document.getElementById('vehicleFilter').value.trim();
            if (reg) params.set('registration', reg);
        }

        return "{{ route('reports.data') }}?" + params.toString();
    }

    // ──────────────────────────────────────────
    // LABEL HELPER
    // ──────────────────────────────────────────
    function buildLabel() {
        if (activeRange === 'custom') {
            return `${document.getElementById('dateFrom').value}  →  ${document.getElementById('dateTo').value}`;
        }
        if (activeRange === 'last_3') return 'Last 3 Months';
        if (activeRange === 'last_6') return 'Last 6 Months';
        const m = document.getElementById('reportMonth').value;
        const y = document.getElementById('reportYear').value;
        return `${m}/${y}`;
    }

    // ──────────────────────────────────────────
    // FETCH
    // ──────────────────────────────────────────
    window.fetchReportData = function () {
        const loader = document.getElementById('reportLoader');
        loader.style.display = 'block';

        fetch(buildUrl())
            .then(res => res.json())
            .then(data => {

                document.getElementById('reportMonthLabel').innerText = buildLabel();

                document.getElementById('reportCompanyTotal').innerText =
                    '£' + parseFloat(data.company_total).toFixed(2);
                document.getElementById('reportCashTotal').innerText =
                    '£' + parseFloat(data.cash_total).toFixed(2);
                document.getElementById('reportTotalWashes').innerText = data.total_washes;
 
                const tbody1 = document.querySelector('#companySummaryTable tbody');
                tbody1.innerHTML = '';
                (data.company_summary || []).forEach(c => {
                    tbody1.innerHTML += `
                        <tr>
                            <td>${c.company}</td>
                            <td>${c.total_washes}</td>
                            <td>£${parseFloat(c.rate).toFixed(2)}</td>
                            <td>£${parseFloat(c.total_amount).toFixed(2)}</td>
                        </tr>`;
                });
 
                allWashData = (data.all_washes || []).sort((a, b) =>
                    new Date(b.datetime) - new Date(a.datetime)
                );

                currentWashPage = 1;
                renderWashPage(currentWashPage);
            })
            .catch(err => console.error(err))
            .finally(() => { loader.style.display = 'none'; });
    };

    // ──────────────────────────────────────────
    // RENDER PAGINATED TABLE
    // ──────────────────────────────────────────
    function renderWashPage(page) {
        const tbody2 = document.querySelector('#allWashesTable tbody');
        tbody2.innerHTML = '';

        totalWashPages = Math.max(1, Math.ceil(allWashData.length / rowsPerPageWash));

        document.getElementById('currentWashPage').innerText = page;
        document.getElementById('totalWashPages').innerText  = totalWashPages;

        const start    = (page - 1) * rowsPerPageWash;
        const pageData = allWashData.slice(start, start + rowsPerPageWash);

        if (pageData.length === 0) {
            tbody2.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;">No records found.</td></tr>';
        } else {
            pageData.forEach(w => {
                tbody2.innerHTML += `
                    <tr>
                        <td>${w.datetime}</td>
                        <td>${w.registration}</td>
                        <td>${w.driver ?? '-'}</td>
                        <td>${w.company}</td>
                        <td>£${parseFloat(w.amount).toFixed(2)}</td>
                    </tr>`;
            });
        }

        document.getElementById('prevWashPage').disabled = page === 1;
        document.getElementById('nextWashPage').disabled = page === totalWashPages;
    }

    document.getElementById('prevWashPage').addEventListener('click', () => {
        if (currentWashPage > 1) renderWashPage(--currentWashPage);
    });
    document.getElementById('nextWashPage').addEventListener('click', () => {
        if (currentWashPage < totalWashPages) renderWashPage(++currentWashPage);
    });

    // ──────────────────────────────────────────
    // EXPORT CSV – exports ALL pages, not just visible page
    // ──────────────────────────────────────────
    window.exportCSV = function () {
        const rows = [['Date/Time','Registration','Driver','Company/Cash','Amount']];

        allWashData.forEach(w => {
            rows.push([
                w.datetime,
                w.registration,
                w.driver ?? '-',
                w.company,
                '£' + parseFloat(w.amount).toFixed(2),
            ]);
        });

        const csv  = 'data:text/csv;charset=utf-8,' + rows.map(r => r.join(',')).join('\n');
        const link = document.createElement('a');
        link.href  = encodeURI(csv);
        link.download = 'report_' + buildLabel().replace(/[\s/→]+/g, '_') + '.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
    };

    // Auto-load
    fetchReportData();
});
</script>

<style>
/* ── Filter Panel ───────────────────────────── */
.filter-panel {
    background: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.filter-row {
    display: flex;
    gap: 14px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
}

.select-control {
    padding: 8px 12px;
    border: 1px solid #334155;
    border-radius: 6px;
    background: #1e293b;
    color: #e2e8f0;
    cursor: pointer;
    font-size: 14px;
    min-width: 140px;
}

.select-control:focus {
    outline: none;
    border-color: #0d9488;
}

/* ── Range Toggle Buttons ───────────────────── */
.btn-group {
    display: flex;
    gap: 0;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid #334155;
}

.range-btn {
    background: #1e293b;
    border: none;
    color: #94a3b8;
    padding: 8px 14px;
    cursor: pointer;
    font-size: 13px;
    border-right: 1px solid #334155;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}

.range-btn:last-child { border-right: none; }

.range-btn:hover { background: #334155; color: #e2e8f0; }

.range-btn.active {
    background: #0d9488;
    color: white;
    font-weight: 600;
}

/* ── Misc ───────────────────────────────────── */
.select-control { padding: 8px 12px; border: 1px solid #334155; border-radius: 6px; background: #1e293b; color: #e2e8f0; cursor: pointer; }
.report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
.stat-card { background: #1e293b; padding: 20px; border-radius: 10px; color: white; }
.stat-card.teal { background: #0d9488; }
.stat-card.green { background: #10b981; }
.stat-label { font-size: 14px; opacity: 0.8; }
.stat-value { margin-top: 5px; font-size: 22px; font-weight: bold; }
.table-container { overflow-x: auto; }

.pagination-controls {
    display: flex; justify-content: center; align-items: center; gap: 12px; padding-top: 10px;
}
.page-btn { background: #334155; padding: 8px 14px; border-radius: 6px; border: none; color: white; cursor: pointer; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.loader {
    width: 26px; height: 26px; border: 4px solid #94a3b8;
    border-top-color: transparent; border-radius: 50%; margin: 10px auto;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

input[type="date"].select-control { min-width: 150px; }
</style>
@endsection
