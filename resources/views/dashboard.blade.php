@extends('layouts.app')

@section('content')
    <div id="dashboard-view" class="view active">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Today's Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Washes Today</div>
                    <div class="stat-value" id="statTotalWashes">0</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-label">Total Takings</div>
                    <div class="stat-value" id="statTotalTakings">£0.00</div>
                </div>
                <div class="stat-card teal">
                    <div class="stat-label">Account Washes</div>
                    <div class="stat-value" id="statAccountWashes">0</div>
                </div>
                <div class="stat-card indigo">
                    <div class="stat-label">Cash Washes</div>
                    <div class="stat-value" id="statCashWashes">0</div>
                </div>
            </div>

            <h3 style="margin: 30px 0 15px;">Today's Washes - <span id="todayDate" style="color: #94a3b8;"></span></h3>
            <div class="filter-form" style="margin-bottom: 20px;">
                <label>From: <input type="date" id="fromDate"></label>
                <label>To: <input type="date" id="toDate"></label>
                <button class="btn btn-secondary" onclick="fetchDashboardData()">Filter</button>
                <button class="btn btn-primary" onclick="exportCSV()">Export CSV</button>
            </div>
            <div class="table-container">
                <div id="tableLoader" class="loader" style="display:none;"></div>

                <table id="todayWashesTable">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Registration</th>
                            <th>Driver</th>
                            <th>Company/Cash</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div id="paginationControls" class="pagination-controls" style="margin-top: 10px; text-align: center;">
                    <button onclick="prevPage()" class="page-btn" id="prevBtn" disabled>Prev</button>
                    <span id="pageInfo">Page 1</span>
                    <button onclick="nextPage()" class="page-btn" id="nextBtn" disabled>Next</button>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let currentPage = 1;
            const rowsPerPage = 10;
            let tableData = [];

            window.fetchDashboardData = function() {
                let from = document.getElementById('fromDate').value;
                let to = document.getElementById('toDate').value;

                let url = '{{ route('dashboard.data') }}';
                if (from || to) {
                    url += '?';
                    if (from) url += 'from=' + from + '&';
                    if (to) url += 'to=' + to;
                }

                // Show loader
                document.getElementById('tableLoader').style.display = 'block';
                document.querySelector('#todayWashesTable tbody').innerHTML = '';

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        // Stats UI
                        document.getElementById('statTotalWashes').innerText = data.stats.totalWashes;
                        document.getElementById('statAccountWashes').innerText = data.stats.accountWashes;
                        document.getElementById('statCashWashes').innerText = data.stats.cashWashes;
                        document.getElementById('statTotalTakings').innerText = "£" + data.stats
                            .totalTakings;

                        // Save table data for pagination
                        tableData = data.washes;
                        currentPage = 1;
                        renderTablePage();
                    })
                    .finally(() => {
                        // Hide loader
                        document.getElementById('tableLoader').style.display = 'none';
                    });
            }

            function renderTablePage() {
                let tbody = document.querySelector('#todayWashesTable tbody');
                tbody.innerHTML = '';

                let start = (currentPage - 1) * rowsPerPage;
                let end = start + rowsPerPage;
                let pageData = tableData.slice(start, end);

                pageData.forEach(wash => {
                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                <td>${wash.time}</td>
                <td>${wash.registration}</td>
                <td>${wash.driver}</td>
                <td>${wash.company}</td>
                <td>£${wash.amount}</td>
            `;
                    tbody.appendChild(tr);
                });

                // Update pagination buttons
                document.getElementById('prevBtn').disabled = currentPage === 1;
                document.getElementById('nextBtn').disabled = end >= tableData.length;
                document.getElementById('pageInfo').innerText = `Page ${currentPage}`;
            }

            window.prevPage = function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderTablePage();
                }
            }

            window.nextPage = function() {
                if (currentPage * rowsPerPage < tableData.length) {
                    currentPage++;
                    renderTablePage();
                }
            }

            // CSV Export remains the same
            window.exportCSV = function() {
                let rows = [
                    ['Time', 'Registration', 'Driver', 'Company/Cash', 'Amount']
                ];
                tableData.forEach(w => {
                    rows.push([w.time, w.registration, w.driver, w.company, w.amount]);
                });

                let csvContent = "data:text/csv;charset=utf-8," +
                    rows.map(e => e.join(",")).join("\n");

                let encodedUri = encodeURI(csvContent);
                let link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "washes.csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // Load default data on page load
            fetchDashboardData();
        });
    </script>
@endsection
