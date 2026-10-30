# University Guest House Management System (UGHMS)
## Limitations & Institutional Policy Decisions Needing Confirmation

Before deploying the University Guest House Management System to production at Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni, the following institutional rules and policy decisions must be formally approved by the University Authorities:

---

### 1. Tariff & Pricing Policies
- [ ] **Official Guest Exemption Policy:**  
  Current system seed provides a 100% tariff exemption or subsidized rate (₹1000/night for VIP Suites) for Category A (Official University Dignitaries, External Thesis Examiners, BoM members). Confirm whether external examiners are 100% waived against the Examination Budget Head.
- [ ] **Category-Wise Tariff Schedules:**  
  Validate the differential tariff schedule between Category A (Official), Category B (Other University / Govt Official), Category C (Conference Delegates), and Category D (Faculty Personal Guests).
- [ ] **Meal Charges Schedule:**  
  Determine whether dining charges (Breakfast, Lunch, Dinner) are billed at fixed mess rates or actual caterer vouchers.

---

### 2. Tax & GST Applicability
- [ ] **GST Exemption Threshold:**  
  Under current Indian GST regulations, educational institution guest house accommodations with tariff below threshold may be exempt. The system provides configurable tax masters (`GST-EXEMPT`, `CGST-6`, `SGST-6`). Confirm whether GST registration number (GSTIN) should be displayed on official invoices.

---

### 3. Payment Stage & Cashiering
- [ ] **Configured Payment Collection Stage:**  
  System supports three payment stages:
  1. `approval` (Payment required before room allocation)
  2. `check_in` (Payment collected at reception on arrival)
  3. `check_out` (Payment settled on departure — *Default*)  
  Confirm preferred university policy for private vs. official bookings.
- [ ] **Online Payment Gateway Integration:**  
  Currently, payment methods support Cash, UPI / QR, POS Card, and University Book Transfer. If an automated payment gateway (e.g., SBI e-Pay, HDFC, Razorpay) is contracted by the University, gateway credentials and merchant IDs must be supplied.

---

### 4. Delegation of Approval Authority
- [ ] **Approval Workflow Hierarchy:**  
  Default workflow routes:
  `Applicant Faculty` $\rightarrow$ `Departmental Verifier` $\rightarrow$ `Faculty Dean / Director` $\rightarrow$ `Guest House Incharge (Room Allotment)`.  
  Confirm whether Vice-Chancellor secretariat approval is required for VIP Presidential Suite requests.
- [ ] **Emergency Same-Day Reservations:**  
  Confirm whether the Guest House Incharge has delegated authority to override normal workflow for emergency official guests arriving on the same day.

---

### 5. Document & Identity Verification Policies
- [ ] **Mandatory ID Cards for Accompanying Guests:**  
  System requires photo ID proof for the primary guest and names of all accompanying occupants. Confirm if physical ID copy archiving is required for all accompanying visitors.
- [ ] **Private Storage Retention Policy:**  
  Determine document retention schedule for duty orders and uploaded ID scans (e.g. archive after 3 fiscal years).

---

### 6. Cancellation & Refund Rules
- [ ] **Cancellation Cutoff Window:**  
  Default is set to 24 hours prior to scheduled check-in time (12:00 PM). Confirm whether cancellation fee or forfeiture applies for personal Category D stays cancelled within the cutoff window.

