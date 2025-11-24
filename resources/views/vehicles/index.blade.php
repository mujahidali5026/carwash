@extends('layouts.app')

@section('content')
    <div id="vehicles-view" class="view active">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Add New Vehicle</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Registration</label>
                    <input type="text" id="newVehicleReg" placeholder="e.g., TXI123">
                </div>
                <div class="form-group">
                    <label>Driver Name</label>
                    <input type="text" id="newVehicleDriver" placeholder="Driver Name">
                </div>
                <div class="form-group">
                    <label>Company</label>
                    <select id="newVehicleCompany">
                        <option value="">Select Company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-primary" onclick="addVehicle()">+ Add Vehicle</button>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Registered Vehicles</h2>
            <div class="table-container">
                <table id="vehiclesTable">
                    <thead>
                        <tr>
                            <th>Registration</th>
                            <th>Driver Name</th>
                            <th>Company</th>
                        </tr>
                    </thead>

                    <tbody>
                        <div id="vehiclesLoader" class="loader" style="display: none;"></div>

                    </tbody>
                </table>
                <div id="paginationControls" class="pagination-controls">
                    <button class="page-btn" id="prevPage" disabled>Previous</button>
                    <span id="currentPage">1</span> / <span id="totalPages">1</span>
                    <button class="page-btn" id="nextPage" disabled>Next</button>
                </div>

            </div>
        </div>
    </div>
    <div id="vehicleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeVehicleModal()">&times;</span>
            <h2>Edit Vehicle</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Registration</label>
                    <input type="text" id="modalVehicleReg" disabled>
                </div>
                <div class="form-group">
                    <label>Driver Name</label>
                    <input type="text" id="modalVehicleDriver">
                </div>
                <div class="form-group">
                    <label>Company</label>
                    <select id="modalVehicleCompany">
                        <option value="">Select Company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- <div class="form-group">
                    <label>Custom Price (£)</label>
                    <input type="number" id="modalCustomPrice" step="0.50">
                </div>
                <div class="form-group">
                    <label>Override Limit</label>
                    <input type="number" id="modalOverrideLimit">
                </div> --}}
                {{-- <div class="form-group">
                    <label>Banned</label>
                    <input type="checkbox" id="modalBanned">
                </div> --}}
            </div>
            <button class="btn btn-primary" onclick="updateVehicle()">Save Changes</button>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let currentVehicleId = null;

        function openVehicleModal(vehicle) {
            currentVehicleId = vehicle.id;
            document.getElementById('modalVehicleReg').value = vehicle.registration;
            document.getElementById('modalVehicleDriver').value = vehicle.driver_name;
            document.getElementById('modalVehicleCompany').value = vehicle.company_id || '';
            // document.getElementById('modalCustomPrice').value = vehicle.custom_price || '';
            // document.getElementById('modalOverrideLimit').value = vehicle.override_limit || '';
            // document.getElementById('modalBanned').checked = vehicle.banned || false;

            document.getElementById('vehicleModal').style.display = 'block';
        }

        function closeVehicleModal() {
            document.getElementById('vehicleModal').style.display = 'none';
        }

        function attachEditButtons() {
            document.querySelectorAll('#vehiclesTable tbody tr').forEach((tr, index) => {
                tr.onclick = function() {
                    fetch('{{ url('/vehicles/list') }}')
                        .then(res => res.json())
                        .then(data => {
                            let vehicle = data[index];
                            openVehicleModal(vehicle);
                        });
                };
            });
        }

        function updateVehicle() {
            let driver_name = document.getElementById('modalVehicleDriver').value;
            let company_id = document.getElementById('modalVehicleCompany').value;
            // let custom_price = document.getElementById('modalCustomPrice').value;
            // let override_limit = document.getElementById('modalOverrideLimit').value;
            let banned = document.getElementById('modalBanned').checked;

            fetch(`/vehicles/${currentVehicleId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        driver_name,
                        company_id,
                    })
                })
                // banned
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        closeVehicleModal();
                        loadVehicles();
                    } else {
                        alert('Error updating vehicle');
                    }
                });
        }
        let allVehiclesData = [];
        let currentPage = 1;
        let rowsPerPage = 10;
        let totalPages = 1;

        function renderVehiclePage(page) {
            let tbody = document.querySelector('#vehiclesTable tbody');
            tbody.innerHTML = '';

            totalPages = Math.ceil(allVehiclesData.length / rowsPerPage);
            document.getElementById('currentPage').innerText = page;
            document.getElementById('totalPages').innerText = totalPages;

            let start = (page - 1) * rowsPerPage;
            let end = start + rowsPerPage;
            let pageData = allVehiclesData.slice(start, end);

            pageData.forEach(v => {
                let tr = document.createElement('tr');
                tr.innerHTML = `
            <td>${v.registration}</td>
            <td>${v.driver_name}</td>
            <td>${v.company_name}</td>
        `;
                tr.onclick = () => openVehicleModal(v);
                tbody.appendChild(tr);
            });

            document.getElementById('prevPage').disabled = page === 1;
            document.getElementById('nextPage').disabled = page === totalPages;
        }

        function loadVehicles() {
            const loader = document.getElementById('vehiclesLoader');
            loader.style.display = 'block';

            fetch('{{ route('vehicles.list') }}')
                .then(res => res.json())
                .then(data => {
                    allVehiclesData = data;
                    currentPage = 1;
                    renderVehiclePage(currentPage);
                })
                .finally(() => {
                    loader.style.display = 'none';
                });
        }

        // Pagination buttons
        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage > 1) renderVehiclePage(--currentPage);
        });
        document.getElementById('nextPage').addEventListener('click', () => {
            if (currentPage < totalPages) renderVehiclePage(++currentPage);
        });

        function addVehicle() {
            let registration = document.getElementById('newVehicleReg').value;
            let driver_name = document.getElementById('newVehicleDriver').value;
            let company_id = document.getElementById('newVehicleCompany').value;

            fetch('{{ route('vehicles.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        registration,
                        driver_name,
                        company_id
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        document.getElementById('newVehicleReg').value = '';
                        document.getElementById('newVehicleDriver').value = '';
                        document.getElementById('newVehicleCompany').value = '';
                        loadVehicles();
                    } else {
                        alert('Error adding vehicle');
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadVehicles();
        });
    </script>
@endsection
