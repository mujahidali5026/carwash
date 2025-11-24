
        let companies = [
            { id: 1, name: 'City Cabs', dailyLimit: 1, pricePerWash: 6.00 },
            { id: 2, name: 'Metro Taxis', dailyLimit: 1, pricePerWash: 5.50 },
            { id: 3, name: 'Express Rides', dailyLimit: 2, pricePerWash: 7.00 }
        ];

        let vehicles = [
            { id: 1, registration: 'TXI123', driverName: 'Jane Driver', companyId: 1 },
            { id: 2, registration: 'CAB222', driverName: 'Mark Roberts', companyId: 2 },
            { id: 3, registration: 'TXI567', driverName: 'Imran Khan', companyId: 1 },
            { id: 4, registration: 'EXP999', driverName: 'Sarah Jones', companyId: 3 }
        ];

        let washes = [
            { id: 1, timestamp: new Date('2025-10-25T09:15:00'), registration: 'TXI123', driverName: 'Jane Driver', companyId: 1, amount: 6.00, isCash: false },
            { id: 2, timestamp: new Date('2025-10-25T09:52:00'), registration: 'ABC987', driverName: 'Walk-in Customer', companyId: null, amount: 10.00, isCash: true },
            { id: 3, timestamp: new Date('2025-10-25T10:21:00'), registration: 'TXI567', driverName: 'Imran Khan', companyId: 1, amount: 6.00, isCash: false }
        ];

        let currentVehicle = null;
        let currentCanvas = null;
        let isDrawing = false;
        let hasSignature = false;

        // Mobile Menu Toggle
        function toggleMenu() {
            const nav = document.getElementById('mainNav');
            const hamburger = document.querySelector('.hamburger');
            nav.classList.toggle('active');
            hamburger.classList.toggle('active');
        }

        // Navigation
        function showView(viewName) {
            document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
            
            document.getElementById(viewName + '-view').classList.add('active');
            event.target.classList.add('active');

            // Close mobile menu after selection
            const nav = document.getElementById('mainNav');
            const hamburger = document.querySelector('.hamburger');
            nav.classList.remove('active');
            hamburger.classList.remove('active');

            if (viewName === 'dashboard') updateDashboard();
            if (viewName === 'vehicles') updateVehiclesList();
            if (viewName === 'companies') updateCompaniesList();
            if (viewName === 'reports') updateReports();
        }

        // Signature Pad Setup
        function setupSignaturePad(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            
            currentCanvas = canvas;
            const ctx = canvas.getContext('2d');
            
            // Set canvas size
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            
            ctx.strokeStyle = '#1e40af';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            canvas.addEventListener('touchstart', startDrawing);
            canvas.addEventListener('touchmove', draw);
            canvas.addEventListener('touchend', stopDrawing);
        }

        function startDrawing(e) {
            e.preventDefault();
            isDrawing = true;
            hasSignature = true;
            const canvas = currentCanvas;
            const ctx = canvas.getContext('2d');
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const canvas = currentCanvas;
            const ctx = canvas.getContext('2d');
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            ctx.lineTo(x, y);
            ctx.stroke();
        }

        function stopDrawing(e) {
            e.preventDefault();
            isDrawing = false;
        }

        function clearSignature() {
            if (!currentCanvas) return;
            const ctx = currentCanvas.getContext('2d');
            ctx.clearRect(0, 0, currentCanvas.width, currentCanvas.height);
            hasSignature = false;
        }

        // Driver Interface Functions
        function lookupVehicle() {
            const reg = document.getElementById('regInput').value.toUpperCase().trim();
            
            if (!reg) {
                alert('Please enter a registration number');
                return;
            }

            const vehicle = vehicles.find(v => v.registration.toUpperCase() === reg);
            
            if (vehicle) {
                // Found registered vehicle
                const company = companies.find(c => c.id === vehicle.companyId);
                const today = new Date().toDateString();
                const todayWashes = washes.filter(w => 
                    w.registration.toUpperCase() === reg && 
                    new Date(w.timestamp).toDateString() === today
                );
                
                currentVehicle = {
                    ...vehicle,
                    company: company,
                    washesToday: todayWashes.length,
                    canWash: company ? todayWashes.length < company.dailyLimit : true
                };

                showVehicleDetails();
            } else {
                // Unknown vehicle - cash customer
                currentVehicle = { registration: reg, unknown: true };
                showCashCustomer();
            }
        }

        function showVehicleDetails() {
            document.getElementById('driver-input').style.display = 'none';
            document.getElementById('cash-customer').style.display = 'none';
            document.getElementById('success-screen').style.display = 'none';
            document.getElementById('vehicle-details').style.display = 'block';

            document.getElementById('detailReg').textContent = currentVehicle.registration;
            document.getElementById('detailDriver').textContent = currentVehicle.driverName;
            document.getElementById('detailCompany').textContent = currentVehicle.company?.name || '-';
            document.getElementById('detailPrice').textContent = '£' + (currentVehicle.company?.pricePerWash || 0).toFixed(2);
            document.getElementById('detailToday').textContent = currentVehicle.washesToday;
            document.getElementById('detailLimit').textContent = currentVehicle.company?.dailyLimit || 1;

            const statusBadge = document.getElementById('statusBadge');
            const limitAlert = document.getElementById('limitAlert');
            const recordBtn = document.getElementById('recordBtn');

            if (currentVehicle.canWash) {
                statusBadge.textContent = '✓ Approved';
                statusBadge.className = 'status-badge status-approved';
                limitAlert.style.display = 'none';
                recordBtn.disabled = false;
            } else {
                statusBadge.textContent = '✗ Limit Reached';
                statusBadge.className = 'status-badge status-denied';
                limitAlert.style.display = 'block';
                recordBtn.disabled = true;
            }

            setupSignaturePad('signatureCanvas');
        }

        function showCashCustomer() {
            document.getElementById('driver-input').style.display = 'none';
            document.getElementById('vehicle-details').style.display = 'none';
            document.getElementById('success-screen').style.display = 'none';
            document.getElementById('cash-customer').style.display = 'block';

            document.getElementById('cashReg').textContent = currentVehicle.registration;
            setupSignaturePad('signatureCanvas2');
        }

        function recordWash() {
            if (!hasSignature) {
                alert('Please provide a signature');
                return;
            }

            if (!currentVehicle.canWash) {
                alert('This vehicle has exceeded its daily limit');
                return;
            }

            const newWash = {
                id: washes.length + 1,
                timestamp: new Date(),
                registration: currentVehicle.registration,
                driverName: currentVehicle.driverName,
                companyId: currentVehicle.company?.id,
                amount: currentVehicle.company?.pricePerWash || 0,
                isCash: false
            };

            washes.push(newWash);
            showSuccess();
        }

        function recordCashWash() {
            if (!hasSignature) {
                alert('Please provide a signature');
                return;
            }

            const amount = parseFloat(document.getElementById('cashAmount').value);
            if (!amount || amount <= 0) {
                alert('Please enter a valid cash amount');
                return;
            }

            const newWash = {
                id: washes.length + 1,
                timestamp: new Date(),
                registration: currentVehicle.registration,
                driverName: 'Cash Customer',
                companyId: null,
                amount: amount,
                isCash: true
            };

            washes.push(newWash);
            showSuccess();
        }

        function showSuccess() {
            document.getElementById('driver-input').style.display = 'none';
            document.getElementById('vehicle-details').style.display = 'none';
            document.getElementById('cash-customer').style.display = 'none';
            document.getElementById('success-screen').style.display = 'block';

            setTimeout(() => {
                resetDriver();
            }, 3000);
        }

        function resetDriver() {
            document.getElementById('driver-input').style.display = 'block';
            document.getElementById('vehicle-details').style.display = 'none';
            document.getElementById('cash-customer').style.display = 'none';
            document.getElementById('success-screen').style.display = 'none';

            document.getElementById('regInput').value = '';
            document.getElementById('cashAmount').value = '';
            currentVehicle = null;
            hasSignature = false;
        }

        // Dashboard Functions
        function updateDashboard() {
            const today = new Date().toDateString();
            const todayWashes = washes.filter(w => new Date(w.timestamp).toDateString() === today);
            const totalTakings = todayWashes.reduce((sum, w) => sum + w.amount, 0);
            const accountWashes = todayWashes.filter(w => !w.isCash).length;
            const cashWashes = todayWashes.filter(w => w.isCash).length;

            document.getElementById('statTotalWashes').textContent = todayWashes.length;
            document.getElementById('statTotalTakings').textContent = '£' + totalTakings.toFixed(2);
            document.getElementById('statAccountWashes').textContent = accountWashes;
            document.getElementById('statCashWashes').textContent = cashWashes;

            const todayDate = new Date().toLocaleDateString('en-GB', { 
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
            });
            document.getElementById('todayDate').textContent = todayDate;

            const tbody = document.querySelector('#todayWashesTable tbody');
            tbody.innerHTML = '';
            
            todayWashes.forEach(wash => {
                const company = companies.find(c => c.id === wash.companyId);
                const time = new Date(wash.timestamp).toLocaleTimeString('en-GB', { 
                    hour: '2-digit', minute: '2-digit' 
                });
                
                tbody.innerHTML += `
                    <tr>
                        <td>${time}</td>
                        <td><span class="badge badge-reg">${wash.registration}</span></td>
                        <td>${wash.driverName}</td>
                        <td>${wash.isCash ? '<span class="badge badge-cash">Cash</span>' : company?.name || '-'}</td>
                        <td><span class="badge badge-success">£${wash.amount.toFixed(2)}</span></td>
                    </tr>
                `;
            });
        }

        // Vehicles Management
        function updateVehiclesList() {
            // Update company dropdown
            const select = document.getElementById('newVehicleCompany');
            select.innerHTML = '<option value="">— Select Company —</option>';
            companies.forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });

            // Update vehicles table
            const tbody = document.querySelector('#vehiclesTable tbody');
            tbody.innerHTML = '';
            
            vehicles.forEach(vehicle => {
                const company = companies.find(c => c.id === vehicle.companyId);
                tbody.innerHTML += `
                    <tr>
                        <td><span class="badge badge-reg">${vehicle.registration}</span></td>
                        <td>${vehicle.driverName}</td>
                        <td>${company?.name || '—'}</td>
                    </tr>
                `;
            });
        }

        function addVehicle() {
            const reg = document.getElementById('newVehicleReg').value.toUpperCase().trim();
            const driver = document.getElementById('newVehicleDriver').value.trim();
            const companyId = document.getElementById('newVehicleCompany').value;

            if (!reg || !driver) {
                alert('Please fill in registration and driver name');
                return;
            }

            const newVehicle = {
                id: vehicles.length + 1,
                registration: reg,
                driverName: driver,
                companyId: companyId ? parseInt(companyId) : null
            };

            vehicles.push(newVehicle);
            
            document.getElementById('newVehicleReg').value = '';
            document.getElementById('newVehicleDriver').value = '';
            document.getElementById('newVehicleCompany').value = '';
            
            updateVehiclesList();
            alert('Vehicle added successfully!');
        }

        // Companies Management
        function updateCompaniesList() {
            const tbody = document.querySelector('#companiesTable tbody');
            tbody.innerHTML = '';
            
            companies.forEach(company => {
                tbody.innerHTML += `
                    <tr>
                        <td>${company.name}</td>
                        <td><span class="badge badge-reg">${company.dailyLimit} per day</span></td>
                        <td><span class="badge badge-success">£${company.pricePerWash.toFixed(2)}</span></td>
                    </tr>
                `;
            });
        }

        function addCompany() {
            const name = document.getElementById('newCompanyName').value.trim();
            const limit = parseInt(document.getElementById('newCompanyLimit').value);
            const price = parseFloat(document.getElementById('newCompanyPrice').value);

            if (!name) {
                alert('Please enter a company name');
                return;
            }

            const newCompany = {
                id: companies.length + 1,
                name: name,
                dailyLimit: limit || 1,
                pricePerWash: price || 0
            };

            companies.push(newCompany);
            
            document.getElementById('newCompanyName').value = '';
            document.getElementById('newCompanyLimit').value = '1';
            document.getElementById('newCompanyPrice').value = '6.00';
            
            updateCompaniesList();
            alert('Company added successfully!');
        }

        // Reports
        function updateReports() {
            const companyTotal = washes.filter(w => !w.isCash).reduce((sum, w) => sum + w.amount, 0);
            const cashTotal = washes.filter(w => w.isCash).reduce((sum, w) => sum + w.amount, 0);

            document.getElementById('reportCompanyTotal').textContent = '£' + companyTotal.toFixed(2);
            document.getElementById('reportCashTotal').textContent = '£' + cashTotal.toFixed(2);
            document.getElementById('reportTotalWashes').textContent = washes.length;

            // Company Summary
            const companySummary = companies.map(company => {
                const companyWashes = washes.filter(w => w.companyId === company.id);
                const total = companyWashes.reduce((sum, w) => sum + w.amount, 0);
                return {
                    company: company,
                    count: companyWashes.length,
                    total: total
                };
            });

            const tbody1 = document.querySelector('#companySummaryTable tbody');
            tbody1.innerHTML = '';
            companySummary.forEach(item => {
                tbody1.innerHTML += `
                    <tr>
                        <td>${item.company.name}</td>
                        <td><span class="badge badge-reg">${item.count}</span></td>
                        <td>£${item.company.pricePerWash.toFixed(2)}</td>
                        <td><span class="badge badge-success">£${item.total.toFixed(2)}</span></td>
                    </tr>
                `;
            });

            // All Washes
            const tbody2 = document.querySelector('#allWashesTable tbody');
            tbody2.innerHTML = '';
            washes.forEach(wash => {
                const company = companies.find(c => c.id === wash.companyId);
                const datetime = new Date(wash.timestamp).toLocaleString('en-GB', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                tbody2.innerHTML += `
                    <tr>
                        <td>${datetime}</td>
                        <td><span class="badge badge-reg">${wash.registration}</span></td>
                        <td>${wash.driverName}</td>
                        <td>${wash.isCash ? '<span class="badge badge-cash">Cash</span>' : company?.name || '-'}</td>
                        <td><span class="badge badge-success">£${wash.amount.toFixed(2)}</span></td>
                    </tr>
                `;
            });
        }

        function exportCSV() {
            let csv = 'Date/Time,Registration,Driver,Company/Cash,Amount\n';
            
            washes.forEach(wash => {
                const company = companies.find(c => c.id === wash.companyId);
                const datetime = new Date(wash.timestamp).toLocaleString('en-GB');
                const companyName = wash.isCash ? 'Cash' : (company?.name || '-');
                
                csv += `${datetime},${wash.registration},${wash.driverName},${companyName},£${wash.amount.toFixed(2)}\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'carwash-report-' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
        }

        // Initialize on page load
        window.addEventListener('load', function() {
            updateDashboard();
        }); 