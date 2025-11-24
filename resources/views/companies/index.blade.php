@extends('layouts.app')

@section('content')
    <div id="companies-view" class="view active">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Add New Company</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" id="newCompanyName" placeholder="e.g., City Cabs">
                </div>
                <div class="form-group">
                    <label>Daily Limit</label>
                    <input type="number" id="newCompanyLimit" value="1">
                </div>
                <div class="form-group">
                    <label>Price per Wash (£)</label>
                    <input type="number" id="newCompanyPrice" step="0.50" value="6.00">
                </div>
            </div>
            <button class="btn btn-primary" onclick="addCompany()">+ Add Company</button>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Registered Companies</h2>
            <div class="table-container">
                <table id="companiesTable">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Daily Limit</th>
                            <th>Price per Wash</th>
                            <th>Total Vichele</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Company Modal -->
    <div id="editCompanyModal" class="modal"
        style="display:none; position: fixed; inset:0; background: rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:999;">
        <div class="card" style="max-width:400px; width:100%; padding:30px; position:relative;">
            <h2>Edit Company</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" id="editCompanyName">
                </div>
                <div class="form-group">
                    <label>Daily Limit</label>
                    <input type="number" id="editCompanyLimit">
                </div>
                <div class="form-group">
                    <label>Price per Wash (£)</label>
                    <input type="number" id="editCompanyPrice" step="0.50">
                </div>
            </div>
            <div class="btn-group">
                <button class="btn btn-primary" onclick="saveCompany()">Save</button>
                <button class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
            </div>
            <input type="hidden" id="editCompanyId">
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadCompanies();
        });

        function addCompany() {
            let name = document.getElementById('newCompanyName').value;
            let daily_limit = document.getElementById('newCompanyLimit').value;
            let price_per_wash = document.getElementById('newCompanyPrice').value;

            fetch("{{ route('companies.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        name,
                        daily_limit,
                        price_per_wash
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('newCompanyName').value = '';
                        document.getElementById('newCompanyLimit').value = 1;
                        document.getElementById('newCompanyPrice').value = 6.00;
                        loadCompanies();
                    } else {
                        alert('Error adding company.');
                    }
                })
                .catch(err => console.error(err));
        }

        function loadCompanies() {
            fetch("{{ route('companies.list') }}")
                .then(res => res.json())
                .then(data => {
                    let tbody = document.querySelector("#companiesTable tbody");
                    tbody.innerHTML = ""; 
                    data.forEach(company => {
                        let tr = document.createElement('tr');
                        tr.innerHTML = `
                <td>${company.name}</td>
                <td>
                    <span class="badge badge-reg">${company.daily_limit} per day</span>
                </td>
                <td>
                    <span class="badge badge-success">£${parseFloat(company.price_per_wash).toFixed(2)}</span>
                 </td>
                 <td>
                    <span class="badge badge-info">${company.vehicles_count}</span>
                 </td>
               <td>
    <button class="btn btn-secondary" onclick="editCompany(${company.id}, '${company.name}', ${company.daily_limit}, ${company.price_per_wash})">Edit</button>
    <button class="btn btn-danger" onclick="deleteCompany(${company.id})">Delete</button>
</td>
            `;
                        tbody.appendChild(tr);
                    });
                });
        }

        // Open Edit Modal
        function editCompany(id, name, limit, price) {
            document.getElementById("editCompanyId").value = id;
            document.getElementById("editCompanyName").value = name;
            document.getElementById("editCompanyLimit").value = limit;
            document.getElementById("editCompanyPrice").value = price;

            document.getElementById("editCompanyModal").style.display = "flex";
        }

        // Close Modal
        function closeEditModal() {
            document.getElementById("editCompanyModal").style.display = "none";
        }

        // Save changes via AJAX
        function saveCompany() {
            let id = document.getElementById("editCompanyId").value;
            let name = document.getElementById("editCompanyName").value;
            let limit = document.getElementById("editCompanyLimit").value;
            let price = document.getElementById("editCompanyPrice").value;

            fetch(`/companies/${id}`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        name: name,
                        daily_limit: limit,
                        price_per_wash: price
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadCompanies();
                        closeEditModal();
                    }
                })
                .catch(err => console.error(err));
        }

        // Delete company
        function deleteCompany(id) {
            if (!confirm("Are you sure you want to delete this company?")) return;

            fetch(`/companies/${id}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) loadCompanies();
                })
                .catch(err => console.error(err));
        }
    </script>
@endsection
