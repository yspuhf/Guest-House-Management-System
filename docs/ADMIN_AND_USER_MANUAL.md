# University Guest House Management System (UGHMS)
## Administrator & End-User Operating Manual

**Institution:** Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni, Solan (H.P.)  
**Application:** Guest House Booking, Room Allocation, Institutional Workflow & Stay-Management System

---

### Table of Contents
1. [User Roles and Permissions Matrix](#1-user-roles-and-permissions-matrix)
2. [End-User / Faculty Operating Guide](#2-end-user--faculty-operating-guide)
   - [Submitting a New Accommodation Requisition](#submitting-a-new-accommodation-requisition)
   - [Tracking Requisition Status](#tracking-requisition-status)
   - [Printing Accommodation Vouchers](#printing-accommodation-vouchers)
   - [Revising Requests (Returned for Correction)](#revising-requests-returned-for-correction)
   - [Self-Cancellation](#self-cancellation)
3. [Verifier Operating Guide](#3-verifier-operating-guide)
   - [Scrutinizing Requisitions & Documents](#scrutinizing-requisitions--documents)
   - [Forwarding, Returning, or Rejecting](#forwarding-returning-or-rejecting)
4. [Approving Authority Operating Guide](#4-approving-authority-operating-guide)
   - [Sanctioning Official Accommodations](#sanctioning-official-accommodations)
   - [Mandatory Remarks on Rejection](#mandatory-remarks-on-rejection)
5. [Guest House Incharge Front-Desk Guide](#5-guest-house-incharge-front-desk-guide)
   - [Availability Calendar Matrix](#availability-calendar-matrix)
   - [Allocating Physical Rooms with Lock Safety](#allocating-physical-rooms-with-lock-safety)
   - [Guest Check-in & Identity Scrutiny](#guest-check-in--identity-scrutiny)
   - [Guest Check-out, Room Inspection & Departure](#guest-check-out-room-inspection--departure)
   - [Processing Stay Extensions](#processing-stay-extensions)
   - [Maintenance & Temporary Hold Blocks](#maintenance--temporary-hold-blocks)
6. [Accounts & Finance Operating Guide](#6-accounts--finance-operating-guide)
   - [Invoices & Tax Breakdowns (CGST / SGST)](#invoices--tax-breakdowns-cgst--sgst)
   - [Accepting Payments (Cash, UPI/QR, Card, Account Transfer)](#accepting-payments-cash-upiqr-card-account-transfer)
   - [Issuing Cashier Receipts](#issuing-cashier-receipts)
   - [Processing Authorized Refunds and Waivers](#processing-authorized-refunds-and-waivers)
7. [Administrator Master Configuration Guide](#7-administrator-master-configuration-guide)
   - [Configuring Guest Houses](#configuring-guest-houses)
   - [Room Types and Physical Rooms](#room-types-and-physical-rooms)
   - [Category-Wise Tariff Matrix](#category-wise-tariff-matrix)
   - [Taxes, Expenditure Heads, and System Settings](#taxes-expenditure-heads-and-system-settings)
   - [Managing User Accounts & RBAC](#managing-user-accounts--rbac)
8. [Auditor Guide: Immutable Trail & Delivery Logs](#8-auditor-guide-immutable-trail--delivery-logs)

---

### 1. User Roles and Permissions Matrix

The system enforces least privilege across 8 standard institutional roles:

```
[Faculty / Applicant] ---> [Verifier] ---> [Approving Authority] ---> [Guest House Incharge] ---> [Finance]
        |                       |                   |                         |                      |
 Submits Request        Checks Documents     Grants Sanction           Assigns Physical Rooms    Issues Bill & Receipt
 Tracks Status           & Eligibility       Mandatory Remarks         Performs Check-in/out     Records Collections
```

---

### 2. End-User / Faculty Operating Guide

#### Submitting a New Accommodation Requisition
1. Log in to the portal using your institutional credentials.
2. Click **"New Reservation"** from the top header or sidebar.
3. Fill in the required details:
   - **Primary Guest:** Full name, designation, 10-digit mobile number, email, and ID document number.
   - **Guest Classification:** Category A (Official Examiner), Category B (Govt Official), Category C (Conference Delegate), or Category D (Personal Guest).
   - **Accommodation:** Select the target Guest House (VIP, Faculty Guest House, Kisan Bhawan) and preferred room category.
   - **Dates:** Arrival date/time and departure date/time. The system calculates billable nights automatically as $[Arrival, Departure)$.
   - **Rooms & Guests:** Number of rooms and total guest count.
   - **Budget Head / Sanction Document:** Select the university expenditure head if official; otherwise select "Self-Paid by Guest". Attach duty invitation or approval memo if available (Max 5MB).
4. Click **"Submit Request for Verification"** to enter the approval pipeline, or **"Save as Draft"** to finish later.

#### Tracking Requisition Status
- In the portal, navigate to **"Booking Register"** to view your submitted requisitions.
- Or visit the public tracker at `/track` and enter your unique reference (e.g., `UGH-2026-0001`).

#### Printing Accommodation Vouchers
- Click **"Print Voucher"** on your booking page to open a university confirmation slip complete with campus seal, allotted room numbers, rules, and guest signature blocks.

#### Revising Requests (Returned for Correction)
- If a verifier or approver flags deficiencies, the status changes to `Returned for Correction` and you receive an alert with their remarks.
- Click **"Edit Requisition"**, update the requested information or attach the required letter, and click **"Submit for Scrutiny"**.

#### Self-Cancellation
- If a guest's visit is cancelled before check-in, click **"Cancel Booking"**, input the mandatory reason, and submit. The system recalculates room availability immediately and releases any held rooms.

---

### 3. Verifier Operating Guide
- Verifiers access the **"Pending Verification / Approval"** queue.
- Click **"Review Dossier"** on incoming requisitions.
- Inspect applicant department, guest eligibility, and attached sanction orders.
- Actions:
  - **Verify & Forward:** Advances the request to the Approving Authority.
  - **Return for Correction:** Returns the request to the applicant with specific remarks.
  - **Reject:** Closes the request. (Mandatory remarks required).

---

### 4. Approving Authority Operating Guide
- Deans and designated statutory officers view requests in the **"Authority Sanction"** tab.
- Click **"Inspect & Sanction"**.
- Actions:
  - **Sanction / Approve:** Confirms institutional authorization. The request routes to the Guest House Incharge for room allotment.
  - **Reject Request:** Rejection requires mandatory explanation stored permanently in the audit trail.

---

### 5. Guest House Incharge Front-Desk Guide

#### Availability Calendar Matrix
- Open **"Availability Calendar"** to view a live 14-day grid of all physical rooms with standardized legends:
  - `Available` (Green)
  - `Booked/Occupied` (Blue - in-house guest)
  - `Pending/Attention` (Yellow - allocated incoming reservation)
  - `Unavailable/Maintenance` (Red - repair or cleaning block)

#### Allocating Physical Rooms with Lock Safety
- Open **"Room Allocation Desk"** to view approved bookings.
- Click **"Allocate Rooms"**.
- The system presents only rooms that are operational, published, and strictly free during $[Arrival, Departure)$.
- Check the box for the room(s) and click **"Confirm Room Allocation"**.
- *Protection:* Concurrency locking prevents two front-desk users from assigning the same room simultaneously.

#### Guest Check-in & Identity Scrutiny
- On arrival day, open **"Check-in Desk"**.
- Verify the guest's physical government ID card against the dossier.
- Check the declaration box, inspect room condition, and click **"Confirm Check-in"**.
- Room status changes to `Occupied`.

#### Guest Check-out, Room Inspection & Departure
- In **"Active Occupancy"**, click **"Check-out"**.
- Verify keys are returned and fixtures are intact.
- Enter any incidental dining charges if applicable.
- The system generates an itemized invoice and releases the room.

#### Processing Stay Extensions
- If an in-house guest requests extra nights, open the booking and click **"Request Extension"**.
- The system checks future calendar availability for the assigned rooms before approval to prevent double-booking.

#### Maintenance & Temporary Hold Blocks
- Navigate to **"Maintenance Blocks"** to place rooms out of service for plumbing, painting, or VIP executive holds. Blocked rooms are excluded from applicant forms.

---

### 6. Accounts & Finance Operating Guide
- Open **"Invoices & Receipts"** to view billing registers.
- Inspect itemized invoices featuring room tariff $\times$ nights, taxes (CGST 6% + SGST 6%), and dining incidentals.
- Click **"Record Payment"** to accept payments via Cash, UPI QR code, Card, or Book Transfer.
- Click **"Print Receipt"** to issue an official university cash receipt (`REC-YYYY-XXXX`).

---

### 7. Administrator Master Configuration Guide
Administrators configure:
- **Guest Houses:** Create properties, publish/unpublish, assign caretakers.
- **Room Types & Rooms:** Manage categories, default capacities, and individual room numbers.
- **Tariff Matrix:** Define rates per night for each (Guest Category $\times$ Room Type) pair.
- **Taxes:** Configure GST rates and exemption thresholds.
- **Expenditure Heads:** Manage institutional project codes and budget lines.
- **System Settings:** Configure whether payment is collected at `approval`, `check_in`, or `check_out`.
- **User Accounts:** Provision accounts, assign roles, and toggle status.

---

### 8. Auditor Guide: Immutable Trail & Delivery Logs
- **Audit Logs:** View immutable timeline of every database change with actor name, role, IP address, and before/after JSON diffs.
- **Notification Logs:** Inspect email delivery status and transaction records.

