@extends('layouts.app')

@section('content')
    <div id="driver-view" class="view active">
        <div id="driver-input" class="card driver-welcome">
            <div class="icon-circle">🚗</div>
            <h2>Welcome to the Wash</h2>
            <p>Enter your vehicle registration to begin</p>

            <div class="form-group">
                <label>Vehicle Registration</label>
                <input type="text" id="regInput" class="reg-input" placeholder="e.g., AB12 CDE">
            </div>

            <button class="btn btn-primary btn-block" onclick="lookupVehicle()">
                Continue →
            </button>
        </div>

        <div id="vehicle-details" class="card vehicle-details" style="display: none;">
            <div class="vehicle-header">
                <div class="vehicle-info">
                    <h3 id="detailReg">-</h3>
                    <p id="detailDriver">-</p>
                    <p id="detailCompany">-</p>
                </div>
                <div class="status-badge" id="statusBadge">Approved</div>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <div class="info-value" id="detailPrice">£0.00</div>
                    <div class="info-label">Price</div>
                </div>
                <div class="info-box">
                    <div class="info-value" id="detailToday">0</div>
                    <div class="info-label">Today</div>
                </div>
                <div class="info-box">
                    <div class="info-value" id="detailLimit">1</div>
                    <div class="info-label">Limit</div>
                </div>
            </div>

            <div id="limitAlert" class="alert" style="display: none;">
                ⚠️ This vehicle has reached its daily wash limit. Contact admin to override.
            </div>

            <div class="form-group">
                <div class="signature-header">
                    <label>Driver Signature</label>
                    <button class="clear-sig" onclick="clearSignature()">Clear</button>
                </div>
                <canvas id="signatureCanvas" class="signature-pad"></canvas>
            </div>

            <div class="btn-group">
                <button class="btn btn-secondary" onclick="resetDriver()">Cancel</button>
                <button class="btn btn-success" id="recordBtn" onclick="recordWash()">Record Wash</button>
            </div>
        </div>

        <div id="cash-customer" class="card vehicle-details" style="display: none;">
            <div class="vehicle-header">
                <div class="vehicle-info">
                    <h3>Vehicle Not Registered</h3>
                    <p>Registration: <span id="cashReg">-</span></p>
                </div>
            </div>

            <div class="form-group">
                <label>Cash Amount (£)</label>
                <input type="number" id="cashAmount" step="0.50" placeholder="10.00">
            </div>

            <div class="form-group">
                <div class="signature-header">
                    <label>Driver Signature</label>
                    <button class="clear-sig" onclick="clearSignature()">Clear</button>
                </div>
                <canvas id="signatureCanvas2" class="signature-pad"></canvas>
            </div>

            <div class="btn-group">
                <button class="btn btn-secondary" onclick="resetDriver()">Cancel</button>
                <button class="btn btn-success" onclick="recordCashWash()">Record Cash Wash</button>
            </div>
        </div>

        <div id="success-screen" class="card" style="display: none;">
            <div class="success-screen">
                <div class="success-icon">✓</div>
                <h2>Wash Recorded!</h2>
                <p style="font-size: 20px; color: #94a3b8; margin-bottom: 10px;">Enjoy your clean car 🚗✨</p>
                <p style="font-size: 14px; color: #94a3b8;">Starting new session...</p>
            </div>
        </div>
    </div>
@endsection

{{-- @section('scripts')
    <script>
        function initSignaturePad(canvasId) {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            let drawing = false;

            // Set canvas internal width/height to match CSS size
            function resizeCanvas() {
                const style = getComputedStyle(canvas);
                canvas.width = parseInt(style.width);
                canvas.height = parseInt(style.height);
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas(); // initial

            function start(e) {
                drawing = true;
                ctx.beginPath();
                ctx.moveTo(getX(e), getY(e));
                e.preventDefault();
            }

            function move(e) {
                if (!drawing) return;
                ctx.lineTo(getX(e), getY(e));
                ctx.strokeStyle = "#0f172a";
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.stroke();
                e.preventDefault();
            }

            function end(e) {
                if (!drawing) return;
                drawing = false;
                ctx.closePath();
                e.preventDefault();
            }

            function getX(e) {
                if (e.touches && e.touches[0]) return e.touches[0].clientX - canvas.getBoundingClientRect().left;
                return e.clientX - canvas.getBoundingClientRect().left;
            }

            function getY(e) {
                if (e.touches && e.touches[0]) return e.touches[0].clientY - canvas.getBoundingClientRect().top;
                return e.clientY - canvas.getBoundingClientRect().top;
            }

            // Mouse events
            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', move);
            canvas.addEventListener('mouseup', end);
            canvas.addEventListener('mouseout', end);

            // Touch events
            canvas.addEventListener('touchstart', start);
            canvas.addEventListener('touchmove', move);
            canvas.addEventListener('touchend', end);

            return canvas;
        }

        // Initialize both signature pads after DOM loaded
        window.addEventListener('DOMContentLoaded', () => {
            window.sigPad1 = initSignaturePad('signatureCanvas');
            window.sigPad2 = initSignaturePad('signatureCanvas2');
        });

        // Clear signature
        function clearSignature(id = 'signatureCanvas') {
            const canvas = document.getElementById(id);
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }







        // Lookup vehicle
        function lookupVehicle() {
            let reg = document.getElementById('regInput').value.toUpperCase().trim();
            if (!reg) return alert('Enter registration');

            fetch('{{ route('washes.lookup') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        registration: reg
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.exists) {
                        document.getElementById('driver-input').style.display = 'none';
                        document.getElementById('vehicle-details').style.display = 'block';

                        let v = res.vehicle;
                        document.getElementById('detailReg').innerText = v.registration;
                        document.getElementById('detailDriver').innerText = v.driver_name;
                        document.getElementById('detailCompany').innerText = v.company_name;
                        document.getElementById('detailPrice').innerText = '£' + parseFloat(v.price).toFixed(2);
                        document.getElementById('detailToday').innerText = v.today;
                        document.getElementById('detailLimit').innerText = v.limit;

                        if (v.today >= v.limit) {
                            document.getElementById('limitAlert').style.display = 'block';
                        } else {
                            document.getElementById('limitAlert').style.display = 'none';
                        }

                        window.currentVehicleId = v.id;

                    } else {
                        document.getElementById('driver-input').style.display = 'none';
                        document.getElementById('cash-customer').style.display = 'block';
                        document.getElementById('cashReg').innerText = reg;
                    }
                });
        }

        // For registered vehicle
        function recordWash() {
            let signature = window.sigPad1.toDataURL(); // Get Base64

            fetch('{{ route('washes.record') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        vehicle_id: window.currentVehicleId,
                        signature
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        showSuccessScreen();
                    } else if (res.status === 'limit_reached') {
                        alert(res.message + " You can override if admin permits.");
                    } else {
                        alert('Error recording wash');
                    }
                });
        }

        // For cash wash
        function recordCashWash() {
            let signature = window.sigPad2.toDataURL();
            let reg = document.getElementById('cashReg').innerText;
            let amount = parseFloat(document.getElementById('cashAmount').value);

            if (!amount) return alert('Enter cash amount');

            fetch('{{ route('washes.cash') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        registration: reg,
                        amount,
                        signature
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        showSuccessScreen();
                    } else {
                        alert('Error recording cash wash');
                    }
                });
        }

        // Show success screen
        function showSuccessScreen() {
            document.getElementById('vehicle-details').style.display = 'none';
            document.getElementById('cash-customer').style.display = 'none';
            document.getElementById('success-screen').style.display = 'block';
            setTimeout(() => location.reload(), 3000);
        }

        // Reset driver input
        function resetDriver() {
            document.getElementById('driver-input').style.display = 'block';
            document.getElementById('vehicle-details').style.display = 'none';
            document.getElementById('cash-customer').style.display = 'none';
        }
    </script>
@endsection --}}
@section('scripts')
<script>
    // ================================
    //  INIT SIGNATURE PAD FUNCTION
    // ================================
    function initSignaturePad(canvasId) {
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext('2d');
        let drawing = false;

        // Draw functions
        function start(e) {
            drawing = true;
            ctx.beginPath();
            ctx.moveTo(getX(e), getY(e));
            e.preventDefault();
        }

        function move(e) {
            if (!drawing) return;
            ctx.lineTo(getX(e), getY(e));
            ctx.strokeStyle = "#0f172a";
            ctx.lineWidth = 2;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";
            ctx.stroke();
            e.preventDefault();
        }

        function end(e) {
            drawing = false;
            ctx.closePath();
            e.preventDefault();
        }

        // Coordinate helpers
        function getX(e) {
            if (e.touches?.[0]) return e.touches[0].clientX - canvas.getBoundingClientRect().left;
            return e.clientX - canvas.getBoundingClientRect().left;
        }

        function getY(e) {
            if (e.touches?.[0]) return e.touches[0].clientY - canvas.getBoundingClientRect().top;
            return e.clientY - canvas.getBoundingClientRect().top;
        }

        // Mouse events
        canvas.addEventListener("mousedown", start);
        canvas.addEventListener("mousemove", move);
        canvas.addEventListener("mouseup", end);
        canvas.addEventListener("mouseout", end);

        // Touch events
        canvas.addEventListener("touchstart", start);
        canvas.addEventListener("touchmove", move);
        canvas.addEventListener("touchend", end);

        return canvas;
    }

    // ================================
    //  RESIZE CANVAS WHEN SHOWN
    // ================================
    function resizeSignaturePads() {
        setTimeout(() => {
            if (window.sigPad1) {
                let canvas = document.getElementById('signatureCanvas');
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            }

            if (window.sigPad2) {
                let canvas = document.getElementById('signatureCanvas2');
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            }
        }, 150);
    }

    // ================================
    // INIT ON PAGE LOAD
    // ================================
    window.addEventListener('DOMContentLoaded', () => {
        window.sigPad1 = initSignaturePad("signatureCanvas");
        window.sigPad2 = initSignaturePad("signatureCanvas2");
        resizeSignaturePads(); // initial for safety
    });

    // ================================
    // CLEAR SIGNATURE
    // ================================
    function clearSignature(id = 'signatureCanvas') {
        const canvas = document.getElementById(id);
        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }


    // ================================
    //  LOOKUP VEHICLE
    // ================================
    function lookupVehicle() {
        let reg = document.getElementById('regInput').value.toUpperCase().trim();
        if (!reg) return alert("Enter registration");

        fetch('{{ route('washes.lookup') }}', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ registration: reg })
        })
        .then(res => res.json())
        .then(res => {
            if (res.exists) {
                document.getElementById('driver-input').style.display = "none";
                document.getElementById('vehicle-details').style.display = "block";

                let v = res.vehicle;
                document.getElementById('detailReg').innerText = v.registration;
                document.getElementById('detailDriver').innerText = v.driver_name;
                document.getElementById('detailCompany').innerText = v.company_name;
                document.getElementById('detailPrice').innerText = "£" + parseFloat(v.price).toFixed(2);
                document.getElementById('detailToday').innerText = v.today;
                document.getElementById('detailLimit').innerText = v.limit;

                if (v.today >= v.limit) {
                    document.getElementById('limitAlert').style.display = "block";
                } else {
                    document.getElementById('limitAlert').style.display = "none";
                }

                window.currentVehicleId = v.id;

                resizeSignaturePads(); // FIX signature
            } else {
                document.getElementById('driver-input').style.display = "none";
                document.getElementById('cash-customer').style.display = "block";
                document.getElementById('cashReg').innerText = reg;

                resizeSignaturePads(); // FIX signature
            }
        });
    }

    // ================================
    // RECORD WASH (REGISTERED VEHICLE)
    // ================================
    function recordWash() {
        let signature = document.getElementById('signatureCanvas').toDataURL();

        fetch('{{ route('washes.record') }}', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                vehicle_id: window.currentVehicleId,
                signature
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === "success") {
                showSuccessScreen();
            } else if (res.status === "limit_reached") {
                alert(res.message + " You can override if admin permits.");
            } else {
                alert("Error recording wash");
            }
        });
    }

    // ================================
    // RECORD CASH WASH
    // ================================
    function recordCashWash() {
        let signature = document.getElementById('signatureCanvas2').toDataURL();
        let reg = document.getElementById('cashReg').innerText;
        let amount = parseFloat(document.getElementById('cashAmount').value);

        if (!amount) return alert("Enter cash amount");

        fetch('{{ route('washes.cash') }}', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ registration: reg, amount, signature })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === "success") {
                showSuccessScreen();
            } else {
                alert("Error recording cash wash");
            }
        });
    }

    // ================================
    // SUCCESS SCREEN
    // ================================
    function showSuccessScreen() {
        document.getElementById('vehicle-details').style.display = 'none';
        document.getElementById('cash-customer').style.display = 'none';
        document.getElementById('success-screen').style.display = 'block';

        setTimeout(() => location.reload(), 3000);
    }

    // ================================
    // RESET
    // ================================
    function resetDriver() {
        document.getElementById('driver-input').style.display = "block";
        document.getElementById('vehicle-details').style.display = "none";
        document.getElementById('cash-customer').style.display = "none";
    }
</script>
@endsection
