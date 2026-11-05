# =====================================================================
# Automated Test Suite for New Features:
# 1. Daily Booking Report
# 2. Monthly and Annual Reports
# 3. Room Allotment During Booking Approval & Conflict Validation
# 4. Invoice Categorisation (Non-GST Stay & Dining)
# 5. Mobile Responsiveness & Drawer Collapse
# =====================================================================

$ErrorActionPreference = 'Continue'
$chromePath = 'C:\Program Files\Google\Chrome\Application\chrome.exe'
$port = 9255
$tempProfile = "$env:TEMP\chrome_qa_new_features_$(Get-Random)"
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

# Start Chrome headless
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
        $segment = [ArraySegment[byte]]::new($bytes)
        $conn.ws.SendAsync($segment, [System.Net.WebSockets.WebSocketMessageType]::Text, $true, [System.Threading.CancellationToken]::None).Wait()

        $buf = [byte[]]::new(65536)
        $ms = [System.IO.MemoryStream]::new()
        do {
            $seg = [ArraySegment[byte]]::new($buf)
            $rcv = $conn.ws.ReceiveAsync($seg, [System.Threading.CancellationToken]::None).Result
            $ms.Write($buf, 0, $rcv.Count)
        } while (-not $rcv.EndOfMessage)

        $jsonStr = [System.Text.Encoding]::UTF8.GetString($ms.ToArray())
        $resp = $jsonStr | ConvertFrom-Json
        return $resp.result.result.value
    }

    $previewPath = 'file:///d:/Website%20Projects/Guest House Management System/preview.html'
    $conn = Get-CDPConnection $previewPath
    Start-Sleep -Seconds 2

    # Login as Estate Admin
    $loginRes = Invoke-Evaluate $conn @'
(() => {
    document.getElementById('estateLoginEmail').value = 'estate.officer@yspuniversity.ac.in';
    document.getElementById('estateLoginPass').value = 'password123';
    handleEstateLogin();
    return {
        role: window.currentRole,
        authRole: window.uhfAuthenticatedRole
    };
})()
'@

    # TEST 1: Daily Booking Report Generation & Statistics
    $dailyRes = Invoke-Evaluate $conn @'
(() => {
    showTab('reports');
    switchReportsTab('daily');
    setDailyReportQuickDate('all');
    renderDailyReport();

    const total = document.getElementById('dailyKpiTotalBookings').innerText;
    const approved = document.getElementById('dailyKpiApproved').innerText;
    const pending = document.getElementById('dailyKpiPending').innerText;
    const checkedIn = document.getElementById('dailyKpiCheckedIn').innerText;
    const gross = document.getElementById('dailyKpiTotalAmount').innerText;
    const rows = document.getElementById('dailyReportTableBody').children.length;

    return {
        total: parseInt(total, 10),
        approved: parseInt(approved, 10),
        pending: parseInt(pending, 10),
        checkedIn: parseInt(checkedIn, 10),
        gross: gross,
        tableRows: rows
    };
})()
'@
    if ($dailyRes.total -gt 0 -and $dailyRes.tableRows -gt 0) {
        Add-TestResult "Reports" "REP-01" "Daily Booking Report Generation & Statistics" "PASS" "Total Bookings: $($dailyRes.total), Approved: $($dailyRes.approved), Pending: $($dailyRes.pending), Checked-In: $($dailyRes.checkedIn), Gross: $($dailyRes.gross), Table Rows: $($dailyRes.tableRows)"
    } else {
        Add-TestResult "Reports" "REP-01" "Daily Booking Report Generation & Statistics" "FAIL" "Daily report empty: $($dailyRes | ConvertTo-Json)"
    }

    # TEST 2: Monthly & Annual Reports with Categorical Non-GST Breakdown
    $moRes = Invoke-Evaluate $conn @'
(() => {
    switchReportsTab('monthly');
    renderMonthlyReport();
    const moTotal = document.getElementById('monthlyKpiTotalBookings').innerText;
    const moOcc = document.getElementById('monthlyKpiOccupancy').innerText;
    const moStay = document.getElementById('monthlyKpiStayAmount').innerText;
    const moDining = document.getElementById('monthlyKpiDiningAmount').innerText;
    const catRows = document.getElementById('monthlyCategoryTableBody').children.length;

    switchReportsTab('annual');
    renderAnnualReport();
    const yrTotal = document.getElementById('annualKpiTotalBookings').innerText;
    const yrMonths = document.getElementById('annualMonthTableBody').children.length;

    return {
        moTotal: parseInt(moTotal, 10),
        moOcc: moOcc,
        moStay: moStay,
        moDining: moDining,
        catRows: catRows,
        yrTotal: parseInt(yrTotal, 10),
        yrMonths: yrMonths
    };
})()
'@
    if ($moRes.moTotal -gt 0 -and $moRes.catRows -gt 0 -and $moRes.yrMonths -eq 12) {
        Add-TestResult "Reports" "REP-02" "Monthly & Annual Reports with Categorical Non-GST Breakdown" "PASS" "Monthly Bookings: $($moRes.moTotal), Occupancy: $($moRes.moOcc), Stay: $($moRes.moStay), Dining: $($moRes.moDining), 12-Month Annual Breakdown: $($moRes.yrMonths) months"
    } else {
        Add-TestResult "Reports" "REP-02" "Monthly & Annual Reports with Categorical Non-GST Breakdown" "FAIL" "Monthly/Annual report error: $($moRes | ConvertTo-Json)"
    }

    # TEST 3: Room Allotment During Booking Approval with Double Booking Prevention
    $allotRes = Invoke-Evaluate $conn @'
(() => {
    window.alert = () => {};
    // Find pending dossier GH-2026-10-0005
    openSanctionAllotmentModal('GH-2026-10-0005');
    const selectEl = document.getElementById('allotmentRoomSelect');
    const optCount = selectEl.options.length;

    // Pick first available room
    let availableOpt = null;
    for (let opt of selectEl.options) {
        if (!opt.disabled) { availableOpt = opt.value; break; }
    }
    if (availableOpt) selectEl.value = availableOpt;
    confirmSanctionAndAllotment();

    const dossier = institutionalDossiers.find(d => d.ref === 'GH-2026-10-0005');
    const allottedRoom = guestHouseInventory.find(r => r.id === dossier.allottedRoomId);

    // Test conflict prevention: attempt to allot the exact same room to another dossier
    const dummyRef = 'GH-2026-CONFLICT-TEST';
    institutionalDossiers.push({
        ref: dummyRef,
        guest: 'Conflict Test Guest',
        dates: dossier.dates,
        duration: '3 Nights',
        status: 'PENDING_APPROVAL',
        roomClass: 'vip',
        isVvip: false
    });
    openSanctionAllotmentModal(dummyRef);
    selectEl.value = allottedRoom.id;
    let conflictBlocked = false;
    window.alert = (msg) => {
        if (msg.includes('DOUBLE BOOKING CONFLICT DETECTED') || msg.includes('already allotted')) {
            conflictBlocked = true;
        }
    };
    confirmSanctionAndAllotment();

    // Clean up dummy
    const dummyIdx = institutionalDossiers.findIndex(d => d.ref === dummyRef);
    if (dummyIdx !== -1) institutionalDossiers.splice(dummyIdx, 1);

    return {
        dossierStatus: dossier ? dossier.status : null,
        allottedRoomName: dossier ? dossier.room : null,
        roomStatus: allottedRoom ? allottedRoom.status : null,
        roomGuest: allottedRoom ? allottedRoom.guest : null,
        conflictBlocked: conflictBlocked,
        optCount: optCount
    };
})()
'@
    if ($allotRes.dossierStatus -eq 'APPROVED' -and $allotRes.roomStatus -eq 'RESERVED' -and $allotRes.conflictBlocked -eq $true) {
        Add-TestResult "Room Allotment" "ROOM-01" "Room Allotment During Booking Approval & Conflict Validation" "PASS" "Dossier Status: APPROVED, Room Allotted: $($allotRes.allottedRoomName), Room State: RESERVED for $($allotRes.roomGuest), Overlap Conflict Blocked: $($allotRes.conflictBlocked)"
    } else {
        Add-TestResult "Room Allotment" "ROOM-01" "Room Allotment During Booking Approval & Conflict Validation" "FAIL" "Allotment failed: $($allotRes | ConvertTo-Json)"
    }

    # TEST 4: Separate Stay Charges and Dining Charges Non-GST Invoicing
    $invRes = Invoke-Evaluate $conn @'
(() => {
    showTab('billing');
    renderBillingTable();
    const rows = document.getElementById('billingTableBody').children.length;

    // Check Stay Invoice Modal content
    showReceiptModal('GH-2026-10-0001', 'stay');
    const stayHtml = document.getElementById('printableReceipt').innerHTML;
    const hasStayHeader = stayHtml.includes('STAY CHARGES INVOICE (NON-GST)');
    const hasCheckedInGuest = stayHtml.includes('Dr. A. K. Verma');
    const hasStayZeroGst = stayHtml.includes('GST @ 0%');

    // Check Dining Invoice Modal content
    switchInvoiceType('dining');
    const diningHtml = document.getElementById('printableReceipt').innerHTML;
    const hasDiningHeader = diningHtml.includes('DINING CHARGES INVOICE (NON-GST)');

    // Check Consolidated Invoice Modal content
    switchInvoiceType('both');
    const bothHtml = document.getElementById('printableReceipt').innerHTML;
    const hasBothHeader = bothHtml.includes('CONSOLIDATED GUEST HOUSE INVOICE');

    return {
        billingRows: rows,
        hasStayHeader: hasStayHeader,
        hasCheckedInGuest: hasCheckedInGuest,
        hasStayZeroGst: hasStayZeroGst,
        hasDiningHeader: hasDiningHeader,
        hasBothHeader: hasBothHeader
    };
})()
'@
    if ($invRes.billingRows -gt 0 -and $invRes.hasStayHeader -and $invRes.hasCheckedInGuest -and $invRes.hasStayZeroGst -and $invRes.hasDiningHeader -and $invRes.hasBothHeader) {
        Add-TestResult "Invoicing" "INV-01" "Separate Stay Charges & Dining Charges Non-GST Invoice Generation" "PASS" "Verified Stay Invoice (Billed to Checked-in Resident, GST 0%), Dining Invoice (Mess Charges, Non-GST), Consolidated Invoice, and Billing Ledger ($($invRes.billingRows) invoices)"
    } else {
        Add-TestResult "Invoicing" "INV-01" "Separate Stay Charges & Dining Charges Non-GST Invoice Generation" "FAIL" "Invoice validation failed: $($invRes | ConvertTo-Json)"
    }

    # TEST 5: Mobile Responsiveness, Viewport & Drawer Auto-Close
    $mobRes = Invoke-Evaluate $conn @'
(() => {
    const hamburger = document.querySelector('button[data-bs-target="#sidebarMenu"]');
    const sidebar = document.getElementById('sidebarMenu');
    const hasHamburger = !!hamburger;
    const hasSidebar = !!sidebar;

    // Simulate mobile viewport tab click auto-closing sidebar
    const origWidth = window.innerWidth;
    // Set width to mobile
    Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 500 });
    sidebar.classList.add('show');
    showTab('dashboard');
    const sidebarClosed = !sidebar.classList.contains('show');
    Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: origWidth });

    return {
        hasHamburger: hasHamburger,
        hasSidebar: hasSidebar,
        sidebarClosedOnMobileTabClick: sidebarClosed
    };
})()
'@
    if ($mobRes.hasHamburger -and $mobRes.hasSidebar -and $mobRes.sidebarClosedOnMobileTabClick) {
        Add-TestResult "Mobile" "MOB-01" "Responsive Hamburger & Mobile Drawer Auto-Close" "PASS" "Mobile hamburger toggle present, sidebar menu responsive, auto-closes drawer on mobile tab selection"
    } else {
        Add-TestResult "Mobile" "MOB-01" "Responsive Hamburger & Mobile Drawer Auto-Close" "FAIL" "Mobile responsiveness failed: $($mobRes | ConvertTo-Json)"
    }

} finally {
    Stop-Process -Id $proc.Id -Force -ErrorAction SilentlyContinue
    Remove-Item -Path $tempProfile -Recurse -Force -ErrorAction SilentlyContinue
}

Write-Output ""
Write-Output "======================================================="
Write-Output "SUMMARY OF NEW FEATURES TEST SUITE"
Write-Output "======================================================="
$script:testResults | Format-Table -AutoSize

