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

                <div class="filter-row" style="margin-top: 10px;">
                    <select id="reportMonth" class="select-control">
                        @foreach(['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'] as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>

                    <select id="reportYear" class="select-control">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>

                    <button class="btn btn-secondary" onclick="fetchReportData()">Load</button>
                </div>

                <p id="reportMonthLabel" style="color: #94a3b8; margin-top: 8px;"></p>
            </div>

            <button class="btn btn-success" onclick="exportCSV()">📥 Export CSV</button>
        </div>

        <div id="reportLoader" class="loader" style="display:none;"></div>

        {{-- STATS --}}
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
    <div class="card">
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
        <h3 style="margin-bottom: 20px;">All Washes This Month</h3>
        
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

        {{-- PAGINATION --}}
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

    // Default month/year
    let now = new Date();
    document.getElementById('reportMonth').value = ("0" + (now.getMonth() + 1)).slice(-2);
    document.getElementById('reportYear').value = now.getFullYear();

    // Pagination vars
    let allWashData = [];
    let currentWashPage = 1;
    let rowsPerPageWash = 15;
    let totalWashPages = 1;

    function renderWashPage(page) {
        let tbody2 = document.querySelector("#allWashesTable tbody");
        tbody2.innerHTML = "";

        totalWashPages = Math.ceil(allWashData.length / rowsPerPageWash);

        document.getElementById("currentWashPage").innerText = page;
        document.getElementById("totalWashPages").innerText = totalWashPages;

        let start = (page - 1) * rowsPerPageWash;
        let end = start + rowsPerPageWash;

        let pageData = allWashData.slice(start, end);

        pageData.forEach(w => {
            tbody2.innerHTML += `
                <tr>
                    <td>${w.datetime}</td>
                    <td>${w.registration}</td>
                    <td>${w.driver ?? '-'}</td>
                    <td>${w.company}</td>
                    <td>£${parseFloat(w.amount).toFixed(2)}</td>
                </tr>
            `;
        });

        document.getElementById("prevWashPage").disabled = page === 1;
        document.getElementById("nextWashPage").disabled = page === totalWashPages;
    }

    window.fetchReportData = function () {
        let loader = document.getElementById("reportLoader");
        loader.style.display = "block";

        let month = document.getElementById('reportMonth').value;
        let year = document.getElementById('reportYear').value;
        let url = "{{ route('reports.data') }}?month=" + year + "-" + month;

        fetch(url)
            .then(res => res.json())
            .then(data => {

                document.getElementById("reportMonthLabel").innerText = `${month}/${year}`;

                document.getElementById("reportCompanyTotal").innerText =
                    "£" + parseFloat(data.company_total).toFixed(2);

                document.getElementById("reportCashTotal").innerText =
                    "£" + parseFloat(data.cash_total).toFixed(2);

                document.getElementById("reportTotalWashes").innerText = data.total_washes;


                // COMPANY SUMMARY
                let tbody1 = document.querySelector("#companySummaryTable tbody");
                tbody1.innerHTML = "";

                data.company_summary.forEach(c => {
                    tbody1.innerHTML += `
                        <tr>
                            <td>${c.company}</td>
                            <td>${c.total_washes}</td>
                            <td>£${parseFloat(c.rate).toFixed(2)}</td>
                            <td>£${parseFloat(c.total_amount).toFixed(2)}</td>
                        </tr>
                    `;
                });

                // SORT WASHES (newest first)
                allWashData = data.all_washes.sort((a, b) => {
                    return new Date(b.datetime) - new Date(a.datetime);
                });

                currentWashPage = 1;
                renderWashPage(currentWashPage);
            })
            .finally(() => {
                loader.style.display = "none";
            })
            .catch(err => console.error(err));
    };


    // Pagination actions
    document.getElementById("prevWashPage").addEventListener("click", () => {
        if (currentWashPage > 1) renderWashPage(--currentWashPage);
    });

    document.getElementById("nextWashPage").addEventListener("click", () => {
        if (currentWashPage < totalWashPages) renderWashPage(++currentWashPage);
    });


    // Export CSV
    window.exportCSV = function () {
        let rows = [['Date/Time','Registration','Driver','Company/Cash','Amount']];

        document.querySelectorAll('#allWashesTable tbody tr').forEach(tr => {
            rows.push([...tr.children].map(td => td.textContent.trim()));
        });

        let csv = "data:text/csv;charset=utf-8," + rows.map(e => e.join(",")).join("\n");

        let link = document.createElement("a");
        link.href = encodeURI(csv);
        link.download = "monthly_report.csv";
        document.body.appendChild(link);
        link.click();
        link.remove();
    };

    // Auto-load initial data
    fetchReportData();

});
</script>

<style>
/* Basic styles – adjust as needed */
.select-control { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; cursor: pointer; }
.filter-row { display: flex; gap: 10px; margin-bottom: 5px; }
.report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
.stat-card { background: #1e293b; padding: 20px; border-radius: 10px; color: white; }
.stat-card.teal { background: #0d9488; }
.stat-card.green { background: #10b981; }
.stat-label { font-size: 14px; opacity: 0.8; }
.stat-value { margin-top: 5px; font-size: 22px; font-weight: bold; }
.table-container { overflow-x: auto; }
/* table { width: 100%; border-collapse: collapse; background: #0f172a; color: white; }
table th { background: #1e293b; padding: 12px; text-align: left; font-weight: 600; }
table td { padding: 10px; border-bottom: 1px solid #1e293b; }
table tr:hover td { background: #1e293b; } */

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    padding-top: 10px;
}

.page-btn {
    background: #334155;
    padding: 8px 14px;
    border-radius: 6px;
    border: none;
    color: white;
    cursor: pointer;
}
.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.loader {
    width: 26px;
    height: 26px;
    border: 4px solid #94a3b8;
    border-top-color: transparent;
    border-radius: 50%;
    margin: 10px auto;
    animation: spin 0.7s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
@endsection
