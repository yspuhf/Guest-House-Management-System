# =====================================================================
# Complete End-to-End Automated Testing Harness
# Dr. Y.S. Parmar UHF Nauni - Central Guest House Management System
# =====================================================================

$ErrorActionPreference = 'Continue'
$chromePath = 'C:\Program Files\Google\Chrome\Application\chrome.exe'
$port = 9253
$tempProfile = "$env:TEMP\chrome_qa_profile_$(Get-Random)"
$testResults = [System.Collections.Generic.List[PSCustomObject]]::new()

function Add-TestResult($category, $testId, $name, $status, $details) {
    $obj = [PSCustomObject]@{
        Category = $category
        TestID   = $testId
        Name     = $name
        Status   = $status
        Details  = $details
    }
    $script:testResults.Add($obj)
    $badge = if ($status -eq 'PASS') { "[PASS]" } else { "[FAIL]" }
    Write-Output "$badge $testId - $name : $details"
}

# Start Chrome in headless mode with remote debugging
$proc = Start-Process -FilePath $chromePath -ArgumentList `
    "--headless=new", `
    "--remote-debugging-port=$port", `
    "--user-data-dir=$tempProfile", `
    "--disable-gpu", `
    "--no-first-run", `
    "--no-default-browser-check", `
    "--disable-extensions", `
    "about:blank" -PassThru

Start-Sleep -Seconds 2

try {
    function Get-CDPConnection($url) {
        $navUrl = "http://127.0.0.1:$port/json/new?$url"
        $tab = Invoke-RestMethod -Uri $navUrl -Method Put
        $wsUri = [Uri]$tab.webSocketDebuggerUrl
        $ws = New-Object System.Net.WebSockets.ClientWebSocket
        $cts = New-Object System.Threading.CancellationTokenSource
        $ws.ConnectAsync($wsUri, $cts.Token).Wait()
        return @{ ws = $ws; tab = $tab; id = 100 }
    }

    function Invoke-Evaluate($conn, $expression) {
        $conn.id++
        $reqId = $conn.id
        $payload = @{
            id = $reqId
            method = "Runtime.evaluate"
            params = @{
                expression = $expression
                returnByValue = $true
                awaitPromise = $true
            }
        } | ConvertTo-Json -Compress

        $bytes = [System.Text.Encoding]::UTF8.GetBytes($payload)
        $segment = [System.ArraySegment[byte]]::new($bytes)
        $conn.ws.SendAsync($segment, [System.Net.WebSockets.WebSocketMessageType]::Text, $true, [System.Threading.CancellationToken]::None).Wait()

        while ($true) {
            $buffer = New-Object byte[] 262144
            $rcvSegment = [System.ArraySegment[byte]]::new($buffer)
            $ms = New-Object System.IO.MemoryStream
            do {
                $rcvResult = $conn.ws.ReceiveAsync($rcvSegment, [System.Threading.CancellationToken]::None).Result
                $ms.Write($buffer, 0, $rcvResult.Count)
            } while (-not $rcvResult.EndOfMessage)

            $jsonStr = [System.Text.Encoding]::UTF8.GetString($ms.ToArray())
            $resp = $jsonStr | ConvertFrom-Json
            if ($resp.PSObject.Properties['id'] -and $resp.id -eq $reqId) {
                if ($resp.result.exceptionDetails) {
                    return @{ error = "Uncaught"; details = $resp.result.exceptionDetails }
                }
                if ($resp.result.result.PSObject.Properties['value']) {
                    return $resp.result.result.value
                }
                return $resp.result.result
            }
        }
    }

    # =========================================================================
    # SUITE 1: Visitor Booking Portal (index.html)
    # =========================================================================
    $indexPath = 'file:///d:/Website%20Projects/Guest%20House%20Management%20System/index.html'
    $indexConn = Get-CDPConnection $indexPath
    Start-Sleep -Seconds 2

    # Test 1.1: DOM & Core Elements
    $res = Invoke-Evaluate $indexConn @'
(() => {
    return {
        hasTitle: document.title.includes("Dr. Y.S. Parmar"),
        hasBookingModal: !!document.getElementById('bookingModal'),
        hasTrackInput: !!document.getElementById('trackInputCode'),
        hasNotifModal: !!document.getElementById('notificationsModal'),
        storedCount: (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length
    };
})()
'@
    if ($res.hasTitle -and $res.hasBookingModal -and $res.hasTrackInput) {
        Add-TestResult "Frontend" "FE-01" "Public Portal DOM & Modals Check" "PASS" "Public portal loaded with all core modals & stored count: $($res.storedCount)"
    } else {
        Add-TestResult "Frontend" "FE-01" "Public Portal DOM & Modals Check" "FAIL" "Missing core elements: $($res | ConvertTo-Json)"
    }

    # Test 1.2: Normal Valid Booking Requisition
    $res = Invoke-Evaluate $indexConn @'
(() => {
    const inDate = new Date();
    inDate.setDate(inDate.getDate() + 1);
    const outDate = new Date();
    outDate.setDate(outDate.getDate() + 3);
    const pad = n => String(n).padStart(2, '0');
    const inStr = pad(inDate.getDate()) + '/' + pad(inDate.getMonth()+1) + '/' + inDate.getFullYear();
    const outStr = pad(outDate.getDate()) + '/' + pad(outDate.getMonth()+1) + '/' + outDate.getFullYear();

    document.getElementById('modalCheckIn').value = inStr;
    document.getElementById('modalCheckOut').value = outStr;
    document.getElementById('modalGuestName').value = 'Dr. Vikram Sharma';
    if (document.getElementById('modalMobile')) document.getElementById('modalMobile').value = '9816012345';
    if (document.getElementById('modalSponsoringDept')) document.getElementById('modalSponsoringDept').value = 'Department of Fruit Science';
    if (document.getElementById('modalPurpose')) document.getElementById('modalPurpose').value = 'Academic Workshop Delegate';

    window.alert = () => {};
    handleModalSubmit({ preventDefault: () => {} });

    const dossiers = JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]');
    const latest = dossiers[0];
    return {
        success: latest && latest.guest === 'Dr. Vikram Sharma',
        ref: latest ? latest.ref : null,
        status: latest ? latest.status : null,
        wing: latest ? latest.wing : null,
        tariff: latest ? latest.tariff : null
    };
})()
'@
    if ($res.success -and $res.ref -and $res.status) {
        Add-TestResult "Request Submission" "SUB-01" "Normal Valid Booking Submission" "PASS" "Requisition created: $($res.ref), status: $($res.status)"
    } else {
        Add-TestResult "Request Submission" "SUB-01" "Normal Valid Booking Submission" "FAIL" "Failed to store dossier: $($res | ConvertTo-Json)"
    }

    # Test 1.3: Empty Guest Name Validation
    $res = Invoke-Evaluate $indexConn @'
(() => {
    document.getElementById('modalGuestName').value = '';
    let alertFired = false;
    let alertMsg = '';
    window.alert = msg => { alertFired = true; alertMsg = msg; };
    const countBefore = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    handleModalSubmit({ preventDefault: () => {} });
    const countAfter = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    return {
        alertFired: alertFired,
        alertMsg: alertMsg,
        created: countAfter > countBefore
    };
})()
'@
    if ($res.alertFired -and -not $res.created) {
        Add-TestResult "Request Submission" "SUB-02" "Empty Request Validation" "PASS" "Rejected empty submission as expected: $($res.alertMsg)"
    } else {
        Add-TestResult "Request Submission" "SUB-02" "Empty Request Validation" "FAIL" "Empty guest name was accepted without error: $($res | ConvertTo-Json)"
    }

    # Test 1.4: Spaces-Only Guest Name Validation
    $res = Invoke-Evaluate $indexConn @'
(() => {
    document.getElementById('modalGuestName').value = '     ';
    let alertFired = false;
    let alertMsg = '';
    window.alert = msg => { alertFired = true; alertMsg = msg; };
    const countBefore = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    handleModalSubmit({ preventDefault: () => {} });
    const countAfter = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    return {
        alertFired: alertFired,
        alertMsg: alertMsg,
        created: countAfter > countBefore
    };
})()
'@
    if ($res.alertFired -and -not $res.created) {
        Add-TestResult "Request Submission" "SUB-03" "Whitespace-Only Request Validation" "PASS" "Blocked whitespace-only guest name: $($res.alertMsg)"
    } else {
        Add-TestResult "Request Submission" "SUB-03" "Whitespace-Only Request Validation" "FAIL" "Whitespace-only guest name was allowed: $($res | ConvertTo-Json)"
    }

    # Test 1.5: Very Short Guest Name (< 3 Chars)
    $res = Invoke-Evaluate $indexConn @'
(() => {
    document.getElementById('modalGuestName').value = 'Dr';
    let alertFired = false;
    let alertMsg = '';
    window.alert = msg => { alertFired = true; alertMsg = msg; };
    const countBefore = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    handleModalSubmit({ preventDefault: () => {} });
    const countAfter = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    return {
        alertFired: alertFired,
        alertMsg: alertMsg,
        created: countAfter > countBefore
    };
})()
'@
    if ($res.alertFired -and -not $res.created) {
        Add-TestResult "Request Submission" "SUB-03B" "Short Input Validation (< 3 chars)" "PASS" "Blocked short guest name: $($res.alertMsg)"
    } else {
        Add-TestResult "Request Submission" "SUB-03B" "Short Input Validation (< 3 chars)" "FAIL" "Short name was accepted: $($res | ConvertTo-Json)"
    }

    # Test 1.6: Past Check-In Date
    $res = Invoke-Evaluate $indexConn @'
(() => {
    document.getElementById('modalCheckIn').value = '01/01/2020';
    document.getElementById('modalCheckOut').value = '03/01/2020';
    document.getElementById('modalGuestName').value = 'Dr. Past Traveler';
    let alertMsg = '';
    window.alert = msg => { alertMsg = msg; };
    const countBefore = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    handleModalSubmit({ preventDefault: () => {} });
    const countAfter = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    return {
        alertMsg: alertMsg,
        created: countAfter > countBefore
    };
})()
'@
    if ($res.alertMsg -and $res.alertMsg.Contains('cannot be in the past') -and -not $res.created) {
        Add-TestResult "Request Submission" "SUB-04" "Past Date Submission Validation" "PASS" "Past check-in date blocked: $($res.alertMsg)"
    } else {
        Add-TestResult "Request Submission" "SUB-04" "Past Date Submission Validation" "FAIL" "Past date was allowed: $($res | ConvertTo-Json)"
    }

    # Test 1.7: Zero-Night Stay (Same In & Out)
    $res = Invoke-Evaluate $indexConn @'
(() => {
    const future = new Date();
    future.setDate(future.getDate() + 10);
    const pad = n => String(n).padStart(2, '0');
    const dStr = pad(future.getDate()) + '/' + pad(future.getMonth()+1) + '/' + future.getFullYear();

    document.getElementById('modalCheckIn').value = dStr;
    document.getElementById('modalCheckOut').value = dStr;
    document.getElementById('modalGuestName').value = 'Dr. Same Day Visitor';
    let alertMsg = '';
    window.alert = msg => { alertMsg = msg; };
    const countBefore = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    handleModalSubmit({ preventDefault: () => {} });
    const countAfter = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    return {
        alertMsg: alertMsg,
        created: countAfter > countBefore
    };
})()
'@
    if ($res.alertMsg -and $res.alertMsg.Contains('must be at least one day after') -and -not $res.created) {
        Add-TestResult "Request Submission" "SUB-05" "Zero-Night Date Range Validation" "PASS" "Identical check-in and check-out blocked: $($res.alertMsg)"
    } else {
        Add-TestResult "Request Submission" "SUB-05" "Zero-Night Date Range Validation" "FAIL" "Zero-night stay was allowed: $($res | ConvertTo-Json)"
    }

    # Test 1.8: Special Characters & Emojis
    $res = Invoke-Evaluate $indexConn @'
(() => {
    const inDate = new Date();
    inDate.setDate(inDate.getDate() + 5);
    const outDate = new Date();
    outDate.setDate(outDate.getDate() + 7);
    const pad = n => String(n).padStart(2, '0');
    const inStr = pad(inDate.getDate()) + '/' + pad(inDate.getMonth()+1) + '/' + inDate.getFullYear();
    const outStr = pad(outDate.getDate()) + '/' + pad(outDate.getMonth()+1) + '/' + outDate.getFullYear();

    document.getElementById('modalCheckIn').value = inStr;
    document.getElementById('modalCheckOut').value = outStr;
    const unicodeName = "Prof. O'Connor & Dr. Sharma (Solan) 🎓 🌲";
    document.getElementById('modalGuestName').value = unicodeName;
    if (document.getElementById('modalMobile')) document.getElementById('modalMobile').value = '+91-98765-43210';
    if (document.getElementById('modalPurpose')) document.getElementById('modalPurpose').value = 'Himalayan Ecosystem Symposium';
    
    window.alert = () => {};
    handleModalSubmit({ preventDefault: () => {} });

    const dossiers = JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]');
    const latest = dossiers[0];
    return {
        match: latest && latest.guest === unicodeName,
        retrievedGuest: latest ? latest.guest : null
    };
})()
'@
    if ($res.match) {
        Add-TestResult "Request Submission" "SUB-06" "Special Characters & Emojis Handling" "PASS" "Stored and retrieved: $($res.retrievedGuest)"
    } else {
        Add-TestResult "Request Submission" "SUB-06" "Special Characters & Emojis Handling" "FAIL" "Failed: $($res | ConvertTo-Json)"
    }

    # Test 1.9: Multilingual / Hindi Unicode Input
    $res = Invoke-Evaluate $indexConn @'
(() => {
    const inDate = new Date();
    inDate.setDate(inDate.getDate() + 8);
    const outDate = new Date();
    outDate.setDate(outDate.getDate() + 10);
    const pad = n => String(n).padStart(2, '0');
    const inStr = pad(inDate.getDate()) + '/' + pad(inDate.getMonth()+1) + '/' + inDate.getFullYear();
    const outStr = pad(outDate.getDate()) + '/' + pad(outDate.getMonth()+1) + '/' + outDate.getFullYear();

    document.getElementById('modalCheckIn').value = inStr;
    document.getElementById('modalCheckOut').value = outStr;
    const hindiName = "डॉ. कुलदीप सिंह चौहान (निदेशक अनुसंधान)";
    document.getElementById('modalGuestName').value = hindiName;
    if (document.getElementById('modalMobile')) document.getElementById('modalMobile').value = '9816099999';
    if (document.getElementById('modalPurpose')) document.getElementById('modalPurpose').value = 'अनुसंधान समीक्षा बैठक';
    
    window.alert = () => {};
    handleModalSubmit({ preventDefault: () => {} });

    const dossiers = JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]');
    const latest = dossiers[0];
    return {
        match: latest && latest.guest === hindiName,
        retrievedGuest: latest ? latest.guest : null
    };
})()
'@
    if ($res.match) {
        Add-TestResult "Request Submission" "SUB-07" "Multilingual Unicode Hindi Input" "PASS" "Stored and retrieved: $($res.retrievedGuest)"
    } else {
        Add-TestResult "Request Submission" "SUB-07" "Multilingual Unicode Hindi Input" "FAIL" "Failed: $($res | ConvertTo-Json)"
    }

    # Test 1.10: SQL-like Input Defense
    $res = Invoke-Evaluate $indexConn @'
(() => {
    const inDate = new Date();
    inDate.setDate(inDate.getDate() + 11);
    const outDate = new Date();
    outDate.setDate(outDate.getDate() + 13);
    const pad = n => String(n).padStart(2, '0');
    const inStr = pad(inDate.getDate()) + '/' + pad(inDate.getMonth()+1) + '/' + inDate.getFullYear();
    const outStr = pad(outDate.getDate()) + '/' + pad(outDate.getMonth()+1) + '/' + outDate.getFullYear();

    document.getElementById('modalCheckIn').value = inStr;
    document.getElementById('modalCheckOut').value = outStr;
    const sqliInput = "' OR '1'='1' -- DROP TABLE bookings;";
    document.getElementById('modalGuestName').value = sqliInput;
    if (document.getElementById('modalMobile')) document.getElementById('modalMobile').value = '9816088888';
    
    window.alert = () => {};
    handleModalSubmit({ preventDefault: () => {} });

    const dossiers = JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]');
    const latest = dossiers[0];
    return {
        match: latest && latest.guest === sqliInput,
        retrievedGuest: latest ? latest.guest : null
    };
})()
'@
    if ($res.match) {
        Add-TestResult "Security" "SEC-SQLI" "SQL-Like Input Neutralization" "PASS" "Treated safely as text literal without execution or corruption"
    } else {
        Add-TestResult "Security" "SEC-SQLI" "SQL-Like Input Neutralization" "FAIL" "Failed: $($res | ConvertTo-Json)"
    }

    # Test 1.11: XSS Injection Defense in Public Portal
    $res = Invoke-Evaluate $indexConn @'
(() => {
    const inDate = new Date();
    inDate.setDate(inDate.getDate() + 14);
    const outDate = new Date();
    outDate.setDate(outDate.getDate() + 16);
    const pad = n => String(n).padStart(2, '0');
    const inStr = pad(inDate.getDate()) + '/' + pad(inDate.getMonth()+1) + '/' + inDate.getFullYear();
    const outStr = pad(outDate.getDate()) + '/' + pad(outDate.getMonth()+1) + '/' + outDate.getFullYear();

    document.getElementById('modalCheckIn').value = inStr;
    document.getElementById('modalCheckOut').value = outStr;
    const xssPayload = '<img src=x onerror="window.__xssTriggered=true">';
    document.getElementById('modalGuestName').value = xssPayload;
    window.alert = () => {};
    handleModalSubmit({ preventDefault: () => {} });

    executeTracking();
    return {
        xssTriggered: !!window.__xssTriggered,
        trackGuestContent: document.getElementById('trackGuest') ? document.getElementById('trackGuest').innerHTML : ''
    };
})()
'@
    if (-not $res.xssTriggered) {
        Add-TestResult "Security" "SEC-01" "Public Booking XSS Injection Defense" "PASS" "XSS execution prevented in public portal"
    } else {
        Add-TestResult "Security" "SEC-01" "Public Booking XSS Injection Defense" "FAIL" "XSS was executed during tracking render: $($res | ConvertTo-Json)"
    }

    # Test 1.12: Public Tracking by Reference Code
    $res = Invoke-Evaluate $indexConn @'
(() => {
    const dossiers = JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]');
    if (dossiers.length === 0) return { error: 'No dossiers in storage' };
    const targetRef = dossiers[0].ref;
    document.getElementById('trackInputCode').value = targetRef;
    window.alert = () => {};
    executeTracking();
    const output = document.getElementById('trackingOutput');
    const titleEl = document.getElementById('trackTitle');
    const roomEl = document.getElementById('trackRoom');
    const badgeEl = document.getElementById('trackBadge');
    return {
        visible: output && output.style.display !== 'none',
        trackTitle: titleEl ? (titleEl.innerText || titleEl.textContent || '').trim() : '',
        trackRoom: roomEl ? (roomEl.innerText || roomEl.textContent || '').trim() : '',
        badge: badgeEl ? (badgeEl.innerText || badgeEl.textContent || '').trim() : ''
    };
})()
'@
    if ($res.visible -and $res.trackTitle.Contains('Booking #')) {
        Add-TestResult "Response Delivery" "RES-01" "Public Status Tracker by Reference Code" "PASS" "Tracked: $($res.trackTitle), Badge: $($res.badge)"
    } else {
        Add-TestResult "Response Delivery" "RES-01" "Public Status Tracker by Reference Code" "FAIL" "Failed to track dossier: $($res | ConvertTo-Json)"
    }

    # Test 1.13: Tariff Calculation Engine (Notification 1615-40 Rules)
    $res = Invoke-Evaluate $indexConn @'
(() => {
    if (typeof EstateTariffManager === 'undefined') return { error: 'EstateTariffManager missing' };
    const cat1 = EstateTariffManager.getCategoryById('cat_1_official');
    const cat1p = EstateTariffManager.getCategoryById('cat_1_private');
    const cat6 = EstateTariffManager.getCategoryById('cat_6_foreign_private');
    const calc1 = EstateTariffManager.calculateTariff('cat_1_official', 'vip', 3);
    const calcSurcharge = EstateTariffManager.calculateTariff('cat_1_official', 'vip', 10);

    return {
        cat1Vip: cat1 ? cat1.vipRate : null,
        cat1pVip: cat1p ? cat1p.vipRate : null,
        cat6Vip: cat6 ? cat6.vipRate : null,
        calc1Total: calc1.total,
        calc1Rate: calc1.baseNightlyRate,
        calcSurchargeTotal: calcSurcharge.total,
        surchargeApplied: calcSurcharge.hasSurcharge
    };
})()
'@
    if ($res.cat1Vip -eq 1500 -and $res.cat1pVip -eq 2000 -and $res.cat6Vip -eq 5000 -and $res.calc1Total -eq 4500 -and $res.surchargeApplied -eq $true) {
        Add-TestResult "Backend Processing" "TAR-01" "Statutory Tariff Engine Notification 1615-40 Rates" "PASS" "Rates validated: Cat 1 Official Rs $($res.cat1Vip), Cat 1 Private Rs $($res.cat1pVip), Cat 6 Foreign Rs $($res.cat6Vip), 3 nights total Rs $($res.calc1Total), 10 nights surcharge total Rs $($res.calcSurchargeTotal)"
    } else {
        Add-TestResult "Backend Processing" "TAR-01" "Statutory Tariff Engine Notification 1615-40 Rates" "FAIL" "Rate mismatch: $($res | ConvertTo-Json)"
    }

    # =========================================================================
    # SUITE 2: Institutional Console Authentication & Authorization (preview.html)
    # =========================================================================
    $previewPath = 'file:///d:/Website%20Projects/Guest%20House%20Management%20System/preview.html'
    $prevConn = Get-CDPConnection $previewPath
    Start-Sleep -Seconds 2

    # Test 2.1: Entry Security - Login Gateway Default
    $res = Invoke-Evaluate $prevConn @'
(() => {
    return {
        authRole: window.uhfAuthenticatedRole,
        loginDisplay: document.getElementById('institutionalLoginScreen').style.display,
        appDisplay: document.getElementById('appContainer').style.display
    };
})()
'@
    if ($res.authRole -eq $null -and $res.loginDisplay -eq 'block' -and $res.appDisplay -eq 'none') {
        Add-TestResult "Authentication" "AUTH-01" "Unauthenticated Entry Enforces Login Gateway" "PASS" "Portal securely requires authentication before showing management UI"
    } else {
        Add-TestResult "Authentication" "AUTH-01" "Unauthenticated Entry Enforces Login Gateway" "FAIL" "Login screen not enforcing barrier: $($res | ConvertTo-Json)"
    }

    # Test 2.2: Estate Officer Login & Jurisdiction Inspection
    $res = Invoke-Evaluate $prevConn @'
(() => {
    document.getElementById('estateLoginEmail').value = 'estate.office@yspuniversity.ac.in';
    document.getElementById('estateLoginPass').value = 'password123';
    handleEstateLogin();
    return {
        role: window.currentRole,
        authRole: window.uhfAuthenticatedRole,
        loginDisplay: document.getElementById('institutionalLoginScreen').style.display,
        appDisplay: document.getElementById('appContainer').style.display,
        switchRoleExists: !!document.getElementById('btnQuickSwitchRole'),
        headerEstateAdmin: document.getElementById('header-estate-admin').style.display,
        navTariffs: document.getElementById('nav-item-tariffs').style.display,
        headerOps: document.getElementById('header-ops').style.display
    };
})()
'@
    if ($res.authRole -eq 'estate_admin' -and $res.appDisplay -eq 'block' -and -not $res.switchRoleExists -and $res.headerEstateAdmin -eq 'block') {
        Add-TestResult "Authentication" "AUTH-02" "Estate Officer Authentication & Jurisdiction" "PASS" "Signed in as estate_admin. Full Estate Administration & Front Desk visible. Switch role button absent."
    } else {
        Add-TestResult "Authentication" "AUTH-02" "Estate Officer Authentication & Jurisdiction" "FAIL" "Failed Estate login: $($res | ConvertTo-Json)"
    }

    # Test 2.3: Security Audit - Stored XSS Rendering in Active Dossiers
    $res = Invoke-Evaluate $prevConn @'
(() => {
    const tbody = document.getElementById('bookingsTableBody');
    const hasUnescapedScript = tbody.innerHTML.includes('<img src=x onerror=');
    return {
        hasUnescapedScript: hasUnescapedScript,
        xssTriggered: !!window.__xssTriggered
    };
})()
'@
    if (-not $res.xssTriggered -and -not $res.hasUnescapedScript) {
        Add-TestResult "Security" "SEC-02" "Active Dossiers Stored XSS Sanitization" "PASS" "HTML special characters safely escaped in management table"
    } else {
        Add-TestResult "Security" "SEC-02" "Active Dossiers Stored XSS Sanitization" "FAIL" "Vulnerability detected: Unsanitized HTML/XSS rendered directly in DOM innerHTML"
    }

    # Test 2.4: Sign Out
    $res = Invoke-Evaluate $prevConn @'
(() => {
    logoutAccount();
    return {
        authRole: window.uhfAuthenticatedRole,
        loginDisplay: document.getElementById('institutionalLoginScreen').style.display,
        appDisplay: document.getElementById('appContainer').style.display
    };
})()
'@
    if ($res.authRole -eq $null -and $res.loginDisplay -eq 'block' -and $res.appDisplay -eq 'none') {
        Add-TestResult "Authentication" "AUTH-03" "Session Termination & Secure Logout" "PASS" "Account signed out cleanly and state cleared"
    } else {
        Add-TestResult "Authentication" "AUTH-03" "Session Termination & Secure Logout" "FAIL" "Failed to logout cleanly: $($res | ConvertTo-Json)"
    }

    # Test 2.5: VC Secretariat Login & Dynamic Decluttering
    $res = Invoke-Evaluate $prevConn @'
(() => {
    document.getElementById('vcLoginEmail').value = 'vc.secretariat@yspuniversity.ac.in';
    document.getElementById('vcLoginPass').value = 'password123';
    handleVCLogin();
    return {
        role: window.currentRole,
        authRole: window.uhfAuthenticatedRole,
        headerEstateAdmin: document.getElementById('header-estate-admin').style.display,
        navTariffs: document.getElementById('nav-item-tariffs').style.display,
        headerOps: document.getElementById('header-ops').style.display,
        navCalendar: document.getElementById('nav-item-calendar').style.display,
        navBilling: document.getElementById('nav-item-billing').style.display,
        headerFinance: document.getElementById('header-finance').style.display
    };
})()
'@
    $vcHiddenSuccess = ($res.authRole -eq 'vc_secretariat' -and 
                        $res.headerEstateAdmin -eq 'none' -and 
                        $res.navTariffs -eq 'none' -and 
                        $res.headerOps -eq 'none' -and 
                        $res.navCalendar -eq 'none' -and 
                        $res.navBilling -eq 'none' -and 
                        $res.headerFinance -eq 'none')
    if ($vcHiddenSuccess) {
        Add-TestResult "Authentication" "AUTH-04" "VC Secretariat Role & Clean Console View" "PASS" "VC Console completely isolated from routine estate/front desk/accounts tasks"
    } else {
        Add-TestResult "Authentication" "AUTH-04" "VC Secretariat Role & Clean Console View" "FAIL" "Non-jurisdictional sections visible on VC Dashboard: $($res | ConvertTo-Json)"
    }

    # Test 2.6: Strict URL Navigation Guard for VC Secretariat
    $res = Invoke-Evaluate $prevConn @'
(() => {
    // Attempt unauthorized navigation to Tariffs view as VC
    showTab('tariffs');
    const tariffsDisplay = document.getElementById('view-tariffs').style.display;
    const dashboardDisplay = document.getElementById('view-dashboard').style.display;
    return {
        tariffsDisplay: tariffsDisplay,
        dashboardDisplay: dashboardDisplay
    };
})()
'@
    if ($res.tariffsDisplay -eq 'none' -and $res.dashboardDisplay -ne 'none') {
        Add-TestResult "Security" "SEC-03" "VC Role Navigation Enforcement Guard" "PASS" "Direct navigation to restricted tariffs/calendar blocked and redirected to dashboard"
    } else {
        Add-TestResult "Security" "SEC-03" "VC Role Navigation Enforcement Guard" "FAIL" "Bypassed navigation barrier: $($res | ConvertTo-Json)"
    }

    # =========================================================================
    # SUITE 3: Performance & Load Testing
    # =========================================================================
    $res = Invoke-Evaluate $indexConn @'
(() => {
    const startTime = performance.now();
    const initialCount = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;
    
    const pad = n => String(n).padStart(2, '0');
    const inDate = new Date();
    inDate.setDate(inDate.getDate() + 20);
    const outDate = new Date();
    outDate.setDate(outDate.getDate() + 22);
    const inStr = pad(inDate.getDate()) + '/' + pad(inDate.getMonth()+1) + '/' + inDate.getFullYear();
    const outStr = pad(outDate.getDate()) + '/' + pad(outDate.getMonth()+1) + '/' + outDate.getFullYear();

    window.alert = () => {};
    let successCount = 0;

    for (let i = 0; i < 50; i++) {
        document.getElementById('modalCheckIn').value = inStr;
        document.getElementById('modalCheckOut').value = outStr;
        document.getElementById('modalGuestName').value = 'Stress Test Delegate #' + (i+1);
        if (document.getElementById('modalMobile')) document.getElementById('modalMobile').value = '98000' + String(10000 + i);
        handleModalSubmit({ preventDefault: () => {} });
        successCount++;
    }

    const endTime = performance.now();
    const finalCount = (JSON.parse(localStorage.getItem('uhf_institutional_dossiers') || '[]')).length;

    return {
        successCount: successCount,
        delta: finalCount - initialCount,
        totalDurationMs: Math.round(endTime - startTime),
        avgPerReqMs: Math.round((endTime - startTime) / 50)
    };
})()
'@
    if ($res.delta -eq 50) {
        Add-TestResult "Performance" "PERF-01" "50 Consecutive Requisition Submissions" "PASS" "50 requisitions processed in $($res.totalDurationMs) ms (avg $($res.avgPerReqMs) ms/req). Zero data loss."
    } else {
        Add-TestResult "Performance" "PERF-01" "50 Consecutive Requisition Submissions" "FAIL" "Expected 50 new dossiers, got $($res.delta)"
    }

    # Clean up CDP tabs
    $indexConn.ws.CloseOutputAsync([System.Net.WebSockets.WebSocketCloseStatus]::NormalClosure, "done", [System.Threading.CancellationToken]::None).Wait()
    $prevConn.ws.CloseOutputAsync([System.Net.WebSockets.WebSocketCloseStatus]::NormalClosure, "done", [System.Threading.CancellationToken]::None).Wait()

} catch {
    Add-TestResult "System" "SYS-ERR" "Test Harness Execution Error" "FAIL" $_.Exception.ToString()
} finally {
    if ($proc -and -not $proc.HasExited) {
        $proc.Kill()
    }
    Remove-Item -Path $tempProfile -Recurse -Force -ErrorAction SilentlyContinue
}

Write-Output ""
Write-Output "======================================================="
Write-Output "SUMMARY OF AUTOMATED TEST EXECUTION"
Write-Output "======================================================="
$testResults | Format-Table -AutoSize
