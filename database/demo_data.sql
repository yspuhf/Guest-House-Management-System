-- =====================================================================
-- University Guest House Management System (UGHMS)
-- Seed & Demo Data for Dr. Y.S. Parmar University of Horticulture & Forestry
-- Password for all seed users: password123
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Organization Units
INSERT INTO organization_units (id, code, name, type, contact_email, contact_phone, is_active) VALUES
(1, 'COH', 'College of Horticulture', 'College', 'dean.coh@yspuniversity.ac.in', '01792-252233', 1),
(2, 'COF', 'College of Forestry', 'College', 'dean.cof@yspuniversity.ac.in', '01792-252244', 1),
(3, 'DOR', 'Directorate of Research', 'Directorate', 'dr@yspuniversity.ac.in', '01792-252255', 1),
(4, 'DEE', 'Directorate of Extension Education', 'Directorate', 'dee@yspuniversity.ac.in', '01792-252266', 1),
(5, 'REG', 'Registrar Office & Administration', 'Administrative Office', 'registrar@yspuniversity.ac.in', '01792-252211', 1),
(6, 'COMP', 'Comptroller & Finance Division', 'Administrative Office', 'comptroller@yspuniversity.ac.in', '01792-252222', 1);

-- 2. Roles
INSERT INTO roles (id, name, display_name, description) VALUES
(1, 'system_admin', 'System Administrator', 'Full control over system accounts, security, settings and backups'),
(2, 'guest_house_admin', 'Guest House Administrator', 'Configures properties, rooms, tariffs, guest categories and policies'),
(3, 'guest_house_incharge', 'Guest House Incharge', 'Manages room allocation, check-in, check-out, room condition and daily operations'),
(4, 'verifier', 'Booking Verifier', 'First-level scrutinizer for booking requests and uploaded approval letters'),
(5, 'approver', 'Approving Authority', 'Delegated authority (Dean/Director/Registrar) to approve or reject requests'),
(6, 'employee', 'Employee / Applicant', 'University faculty, staff or scholar submitting and tracking guest bookings'),
(7, 'accounts_finance', 'Accounts / Finance Officer', 'Processes payments, issues official receipts, manages refunds and billing registers'),
(8, 'report_auditor', 'Report Viewer / Auditor', 'View-only access to operational, financial and system audit registers');

-- 3. Permissions
INSERT INTO permissions (id, name, module, display_name, description) VALUES
(1, 'users.manage', 'RBAC', 'Manage Users & Roles', 'Create, update and deactivate user accounts'),
(2, 'masters.manage', 'Configuration', 'Manage Master Data', 'Create and modify guest houses, room types, rooms, tariffs and taxes'),
(3, 'bookings.create', 'Bookings', 'Create Bookings', 'Submit booking requests for self or on behalf of guests'),
(4, 'bookings.view_own', 'Bookings', 'View Own Bookings', 'Track own submitted requests and history'),
(5, 'bookings.verify', 'Workflow', 'Verify Bookings', 'Review requests, check documents and forward or return for correction'),
(6, 'bookings.approve', 'Workflow', 'Approve/Reject Bookings', 'Grant final authorization or reject with mandatory remarks'),
(7, 'rooms.allocate', 'Operations', 'Allocate Rooms', 'Assign physical rooms to approved reservations'),
(8, 'operations.checkin_checkout', 'Operations', 'Check-in and Check-out', 'Record guest arrival, departure, condition and ID check'),
(9, 'billing.manage', 'Finance', 'Billing & Cashiering', 'Generate invoices, accept payments and issue official receipts'),
(10, 'reports.view', 'Reporting', 'View Reports', 'Access occupancy, booking register and financial summaries'),
(11, 'audit.view', 'Audit', 'View Audit Trail', 'Inspect immutable system event and transaction logs');

-- Role-Permission mappings
INSERT INTO role_has_permissions (role_id, permission_id) VALUES
-- Admin has all
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11),
-- GH Admin
(2, 2), (2, 4), (2, 7), (2, 10),
-- Incharge
(3, 3), (3, 4), (3, 7), (3, 8), (3, 9), (3, 10),
-- Verifier
(4, 4), (4, 5),
-- Approver
(5, 4), (5, 6),
-- Employee
(6, 3), (6, 4),
-- Finance
(7, 4), (7, 9), (7, 10),
-- Auditor
(8, 10), (8, 11);

-- 4. Users (Password: password123 -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
INSERT INTO users (id, organization_unit_id, employee_id, name, email, password, designation, department, phone, primary_role, is_active) VALUES
(1, 5, 'EMP-001', 'Dr. Rajesh Sharma', 'admin@yspuniversity.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator & Incharge IT', 'Computer Centre', '9816011111', 'system_admin', 1),
(2, 5, 'EMP-002', 'Sh. Suresh Verma', 'ghadmin@yspuniversity.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Estate Officer & GH Admin', 'Estate Office', '9816022222', 'guest_house_admin', 1),
(3, 5, 'EMP-003', 'Sh. Anil Kumar', 'incharge@yspuniversity.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Guest House Supervisor / Incharge', 'VIP Guest House Desk', '9816033333', 'guest_house_incharge', 1),
(4, 1, 'EMP-004', 'Dr. Meenakshi Thakur', 'verifier@yspuniversity.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Associate Professor & Verifier', 'Department of Fruit Science', '9816044444', 'verifier', 1),
(5, 1, 'EMP-005', 'Dr. Sanjeev Chauhan', 'approver@yspuniversity.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dean & Approving Authority', 'College of Horticulture', '9816055555', 'approver', 1),
(6, 2, 'EMP-006', 'Dr. Vikas Sharma', 'employee@yspuniversity.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Assistant Professor / Applicant', 'Department of Silviculture', '9816066666', 'employee', 1),
(7, 6, 'EMP-007', 'Sh. Ramesh Chand', 'finance@yspuniversity.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Section Officer (Cash & Accounts)', 'Comptroller Office', '9816077777', 'accounts_finance', 1),
(8, 6, 'EMP-008', 'Dr. Neena Kumari', 'auditor@yspuniversity.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Internal Auditor', 'Audit Cell', '9816088888', 'report_auditor', 1);

INSERT INTO user_has_roles (user_id, role_id) VALUES
(1, 1), (2, 2), (3, 3), (4, 4), (5, 5), (6, 6), (7, 7), (8, 8);

-- 5. Guest Houses
INSERT INTO guest_houses (id, code, name, description, address, eligibility_rules, assigned_incharge_id, contact_person, contact_phone, contact_email, status, is_active, published_at) VALUES
(1, 'VIP-GH', 'VIP Guest House', 'Main university executive guest house reserved for dignitaries, examiners, BoM members and official guests.', 'Near Vice Chancellor Secretariat, Main Campus, Nauni Solan', 'Vice Chancellor approval or Dean recommendation required. Priority for State Guests and ICAR officials.', 3, 'Sh. Anil Kumar', '01792-252301', 'vip.guesthouse@yspuniversity.ac.in', 'published', 1, '2026-01-01 00:00:00'),
(2, 'FGH', 'Faculty & International Guest House', 'Comfortable lodging for visiting scientists, guest faculty, conference delegates and scholars.', 'East Campus, Adjacent to Forestry Complex, Nauni Solan', 'Open to university faculty, visiting research fellows and sponsored conference delegates.', 3, 'Sh. Anil Kumar', '01792-252302', 'fgh@yspuniversity.ac.in', 'published', 1, '2026-01-01 00:00:00'),
(3, 'KBH', 'Kisan Bhawan / Farmers Hostel', 'Spacious residential facility for farmers participating in extension training programs, workshops and horti-fairs.', 'Lower Campus, Directorate of Extension Education, Nauni Solan', 'Farmers, trainees, extension participants and student groups.', 3, 'Sh. Anil Kumar', '01792-252303', 'kisan.bhawan@yspuniversity.ac.in', 'published', 1, '2026-01-01 00:00:00'),
(4, 'ALUMNI-RH', 'Alumni Rest House (Annex)', 'Proposed annex facility for returning alumni and short-stay visitors.', 'West Ridge, Campus Sector 4', 'Alumni Association registered members.', 3, 'Sh. Anil Kumar', '01792-252304', 'alumni.rh@yspuniversity.ac.in', 'draft', 1, NULL);

-- 6. Room Types
INSERT INTO room_types (id, code, name, description, default_tariff, standard_capacity, is_active) VALUES
(1, 'VIP-SUITE', 'VIP Presidential Suite', 'Air-conditioned luxury suite with master bedroom, attached lounge, TV, mini-fridge and pantry.', 2500.00, 2, 1),
(2, 'DLX-AC', 'Deluxe AC Double Room', 'Spacious air-conditioned room with 2 twin/queen beds, study desk, geyser, Wi-Fi and attached balcony.', 1500.00, 2, 1),
(3, 'STD-NON-AC', 'Standard Non-AC Double Room', 'Comfortable hill-climate double room with attached western bathroom and geyser.', 800.00, 2, 1),
(4, 'STD-SINGLE', 'Standard Single Room', 'Single bed room suited for individual research scholars, viva examiners or staff on duty.', 500.00, 1, 1),
(5, 'DORM-BED', 'Dormitory Bed (Shared)', 'Clean, furnished bed in 6-bed dormitory hall with individual locker and common bath facility.', 200.00, 1, 1);

-- 7. Rooms
INSERT INTO rooms (id, guest_house_id, room_type_id, room_number, floor, tariff, max_occupancy, facilities, is_active, publication_status, maintenance_status, notes) VALUES
-- VIP Guest House (Rooms 101 - 105)
(1, 1, 1, 'VIP-101', 'Ground Floor', 2500.00, 2, '["AC", "Living Lounge", "Smart TV", "Geyser", "Wi-Fi", "Refrigerator"]', 1, 'published', 'operational', 'Governor / VC suite'),
(2, 1, 1, 'VIP-102', 'Ground Floor', 2500.00, 2, '["AC", "Living Lounge", "Smart TV", "Geyser", "Wi-Fi", "Refrigerator"]', 1, 'published', 'operational', 'ICAR DG / Dignitary suite'),
(3, 1, 2, 'VIP-201', 'First Floor', 1600.00, 2, '["AC", "LED TV", "Geyser", "Wi-Fi", "Balcony"]', 1, 'published', 'operational', 'Deluxe room east wing'),
(4, 1, 2, 'VIP-202', 'First Floor', 1600.00, 2, '["AC", "LED TV", "Geyser", "Wi-Fi", "Balcony"]', 1, 'published', 'operational', 'Deluxe room east wing'),
(5, 1, 2, 'VIP-203', 'First Floor', 1600.00, 2, '["AC", "LED TV", "Geyser", "Wi-Fi"]', 1, 'published', 'operational', 'Deluxe room west wing'),
-- Faculty Guest House (Rooms 201 - 206)
(6, 2, 2, 'FGH-101', 'Ground Floor', 1400.00, 2, '["AC", "TV", "Geyser", "Wi-Fi", "Study Desk"]', 1, 'published', 'operational', 'Near reception'),
(7, 2, 2, 'FGH-102', 'Ground Floor', 1400.00, 2, '["AC", "TV", "Geyser", "Wi-Fi", "Study Desk"]', 1, 'published', 'operational', 'Double occupancy'),
(8, 2, 3, 'FGH-201', 'First Floor', 800.00, 2, '["Geyser", "Wi-Fi", "Study Table", "Mountain View"]', 1, 'published', 'operational', 'Sunny side view'),
(9, 2, 3, 'FGH-202', 'First Floor', 800.00, 2, '["Geyser", "Wi-Fi", "Study Table"]', 1, 'published', 'operational', 'Standard double room'),
(10, 2, 4, 'FGH-203', 'First Floor', 500.00, 1, '["Geyser", "Wi-Fi", "Study Table", "Single Bed"]', 1, 'published', 'operational', 'Single examiner room'),
(11, 2, 4, 'FGH-204', 'First Floor', 500.00, 1, '["Geyser", "Wi-Fi", "Study Table", "Single Bed"]', 1, 'published', 'under_maintenance', 'Plumbing repair in progress'),
-- Kisan Bhawan (Rooms 301 - 304)
(12, 3, 3, 'KB-101', 'Ground Floor', 600.00, 2, '["Attached Bath", "Solar Geyser", "Ceiling Fan"]', 1, 'published', 'operational', 'Trainee trainer room'),
(13, 3, 3, 'KB-102', 'Ground Floor', 600.00, 2, '["Attached Bath", "Solar Geyser", "Ceiling Fan"]', 1, 'published', 'operational', 'Trainee trainer room'),
(14, 3, 5, 'KB-DORM-A', 'First Floor', 200.00, 6, '["Solar Water", "Common Bathroom", "Lockers"]', 1, 'published', 'operational', '6-bed farmer trainee hall'),
(15, 3, 5, 'KB-DORM-B', 'First Floor', 200.00, 6, '["Solar Water", "Common Bathroom", "Lockers"]', 1, 'published', 'operational', '6-bed farmer trainee hall');

-- 8. Guest Types
INSERT INTO guest_types (id, code, name, description, priority_order, is_active) VALUES
(1, 'CAT-A', 'Category A: Official University Dignitary & Examiner', 'BoM members, statutory officers, external thesis examiners, UGC/ICAR audit peer teams.', 1, 1),
(2, 'CAT-B', 'Category B: Other University / Govt Official on Duty', 'Officials from State Govt, other state agricultural universities, research collaborators.', 2, 1),
(3, 'CAT-C', 'Category C: Seminar / Training / Workshop Delegate', 'Registered participants attending sponsored conferences, short courses and kisan camps.', 3, 1),
(4, 'CAT-D', 'Category D: University Staff Personal / Private Guest', 'Visiting family and personal guests sponsored by regular university employees.', 4, 1);

-- 9. Category-Wise Tariff Matrix
INSERT INTO guest_type_tariffs (id, guest_type_id, room_type_id, rate_per_night, is_active) VALUES
-- Cat A: Subsidized / Official rates
(1, 1, 1, 1000.00, 1), -- VIP Suite for Official Guest
(2, 1, 2, 600.00, 1),  -- Deluxe AC
(3, 1, 3, 300.00, 1),  -- Standard Non-AC
(4, 1, 4, 200.00, 1),  -- Single
(5, 1, 5, 100.00, 1),  -- Dorm Bed
-- Cat B: Semi-official
(6, 2, 1, 1500.00, 1),
(7, 2, 2, 1000.00, 1),
(8, 2, 3, 500.00, 1),
(9, 2, 4, 350.00, 1),
(10, 2, 5, 150.00, 1),
-- Cat C: Delegate
(11, 3, 1, 2000.00, 1),
(12, 3, 2, 1200.00, 1),
(13, 3, 3, 600.00, 1),
(14, 3, 4, 400.00, 1),
(15, 3, 5, 200.00, 1),
-- Cat D: Personal / Private (Full commercial tariff)
(16, 4, 1, 2500.00, 1),
(17, 4, 2, 1500.00, 1),
(18, 4, 3, 800.00, 1),
(19, 4, 4, 500.00, 1),
(20, 4, 5, 250.00, 1);

-- 10. Taxes & Expenditure Heads
INSERT INTO taxes (id, name, code, calculation_method, rate, description, is_active) VALUES
(1, 'GST Exempt (Official Tariffs <= ₹1000)', 'GST-EXEMPT', 'percentage', 0.00, 'Statutory exemption for educational / official guest stays under threshold', 1),
(2, 'CGST (6%)', 'CGST-6', 'percentage', 6.00, 'Central GST on guest house accommodation above ₹1000/night', 1),
(3, 'SGST (6%)', 'SGST-6', 'percentage', 6.00, 'State GST on guest house accommodation above ₹1000/night', 1);

INSERT INTO expenditure_heads (id, code, name, department, description, requires_approval_document, is_active) VALUES
(1, 'GUF-01', 'General University Administration Fund', 'Registrar Office', 'Main university central budget for statutory meetings and official visits', 0, 1),
(2, 'ICAR-PROJECT-2026', 'ICAR All India Coordinated Research Project (AICRP)', 'Directorate of Research', 'Budget head for AICRP national workshops and monitoring team stays', 1, 1),
(3, 'DEAN-HORT-EXAM', 'College of Horticulture Examination & Viva Head', 'College of Horticulture', 'Defrays TA/DA and lodging for external PhD/MSc thesis evaluators', 1, 1),
(4, 'KISAN-MELA-EXT', 'Directorate of Extension Training & Kisan Mela', 'Directorate of Extension', 'Covers lodging and boarding for progressive farmer batches', 1, 1),
(5, 'SELF-PAID', 'Self-Paid by Guest / Sponsoring Faculty', 'Not Applicable', 'Guest pays directly at check-in / check-out via Cash, UPI or Card', 0, 1);

-- 11. System Settings
INSERT INTO system_settings (id, `key`, value, `group`, label, type, options, description) VALUES
(1, 'institution_name', 'Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni', 'general', 'University / Institution Name', 'text', NULL, 'Official institution title displayed across headers and vouchers'),
(2, 'institution_short', 'YSP UHF Nauni', 'general', 'Short Acronym', 'text', NULL, 'Acronym used in references and badges'),
(3, 'currency_symbol', '₹', 'finance', 'Currency Symbol', 'text', NULL, 'Monetary symbol for invoices and reports'),
(4, 'payment_stage', 'check_out', 'finance', 'Configured Payment Stage', 'select', '["approval", "check_in", "check_out"]', 'When room charges and taxes must be finalized and collected (approval / check-in / check-out)'),
(5, 'check_in_time', '12:00', 'booking', 'Standard Check-In Time', 'time', NULL, 'Standard daily check-in hour'),
(6, 'check_out_time', '11:00', 'booking', 'Standard Check-Out Time', 'time', NULL, 'Standard daily check-out hour (interval is [check-in, check-out))'),
(7, 'cancellation_cutoff_hours', '24', 'booking', 'Cancellation Cut-off Window (Hours)', 'number', NULL, 'Hours before check-in date when free cancellation is permitted'),
(8, 'contact_email', 'guesthouse@yspuniversity.ac.in', 'general', 'Master Guest House Email', 'text', NULL, 'Central contact email address'),
(9, 'allow_same_day_booking', '1', 'booking', 'Allow Same-Day Bookings', 'boolean', NULL, 'Whether emergency bookings on current date are allowed by incharge');

-- 12. Approval Workflows & Steps
INSERT INTO approval_workflows (id, name, service_type, organization_unit_id, allow_return_for_correction, is_active, description) VALUES
(1, 'Standard Academic & Official Workflow', 'Guest House Booking', 1, 1, 1, 'Two-step institutional approval: Verification by Department Nominee, then Approval by Dean/Authority');

INSERT INTO approval_steps (id, approval_workflow_id, step_order, stage_type, role_name, specific_user_id, step_title, can_edit, can_reject, can_return) VALUES
(1, 1, 1, 'verifier', 'verifier', 4, 'Departmental Document & Eligibility Verification', 1, 1, 1),
(2, 1, 2, 'approver', 'approver', 5, 'Dean / Controlling Authority Sanction', 0, 1, 1);

-- 13. Email Templates
INSERT INTO email_templates (id, event_key, title, subject, body_template, is_active) VALUES
(1, 'booking_submitted', 'Booking Request Submitted', 'Booking Request Submitted - Reference: {BOOKING_REF}', 'Dear {APPLICANT_NAME},\n\nYour guest house reservation request for {GUEST_NAME} has been successfully submitted.\nReference Number: {BOOKING_REF}\nGuest House: {GUEST_HOUSE}\nStay Period: {ARRIVAL_DATE} to {DEPARTURE_DATE}\n\nYour request has been routed to the departmental verifier for verification.\n\nRegards,\nGuest House Management System\n{INSTITUTION_NAME}', 1),
(2, 'booking_verified', 'Booking Request Verified', 'Booking Verified - Reference: {BOOKING_REF}', 'Dear {APPLICANT_NAME},\n\nYour booking request {BOOKING_REF} has been verified and forwarded to the approving authority for final sanction.\n\nVerifier Remarks: {REMARKS}\n\nRegards,\n{INSTITUTION_NAME}', 1),
(3, 'booking_approved', 'Booking Request Approved', 'Booking Approved - Reference: {BOOKING_REF}', 'Dear {APPLICANT_NAME},\n\nCongratulations! Your reservation request {BOOKING_REF} has been approved by the designated authority.\nThe Guest House Incharge will now allocate available rooms.\n\nAuthority Remarks: {REMARKS}\n\nRegards,\n{INSTITUTION_NAME}', 1),
(4, 'booking_rejected', 'Booking Request Rejected', 'Booking Request Rejected - Reference: {BOOKING_REF}', 'Dear {APPLICANT_NAME},\n\nYour guest house booking request {BOOKING_REF} could not be approved.\n\nReason / Mandatory Remarks:\n{REMARKS}\n\nIf you have queries, please contact the Guest House Incharge.\n\nRegards,\n{INSTITUTION_NAME}', 1),
(5, 'booking_returned', 'Booking Request Returned for Correction', 'Booking Returned for Correction - Reference: {BOOKING_REF}', 'Dear {APPLICANT_NAME},\n\nYour booking request {BOOKING_REF} requires corrections or additional supporting documents.\n\nRemarks:\n{REMARKS}\n\nPlease log in to the portal, edit your draft and re-submit.\n\nRegards,\n{INSTITUTION_NAME}', 1),
(6, 'room_allocated', 'Rooms Allocated to Booking', 'Room Allocation Confirmed - Reference: {BOOKING_REF}', 'Dear {APPLICANT_NAME},\n\nRooms have been officially assigned for your guest {GUEST_NAME}.\nReference: {BOOKING_REF}\nGuest House: {GUEST_HOUSE}\nAllocated Room(s): {ROOM_NUMBERS}\nDates: {ARRIVAL_DATE} ({CHECKIN_TIME}) to {DEPARTURE_DATE} ({CHECKOUT_TIME})\n\nPlease present a valid photo identity card at the reception during check-in.\n\nRegards,\nGuest House Desk, {INSTITUTION_NAME}', 1),
(7, 'guest_checked_in', 'Guest Checked In', 'Guest Arrival Recorded - Reference: {BOOKING_REF}', 'Dear {APPLICANT_NAME},\n\nGuest {GUEST_NAME} has checked in to {GUEST_HOUSE}, Room(s): {ROOM_NUMBERS}.\nArrival Timestamp: {CHECKIN_TIMESTAMP}.\n\nRegards,\n{INSTITUTION_NAME}', 1),
(8, 'invoice_settled', 'Invoice Settled & Receipt Issued', 'Guest House Bill Settled - Receipt: {RECEIPT_NUM}', 'Dear {APPLICANT_NAME} / {GUEST_NAME},\n\nThank you for staying at {GUEST_HOUSE}.\nYour bill for reservation {BOOKING_REF} has been fully settled.\nReceipt Number: {RECEIPT_NUM}\nAmount Paid: {CURRENCY} {AMOUNT_PAID}\nPayment Method: {PAYMENT_METHOD}\n\nWe hope you had a pleasant stay.\n\nRegards,\nAccounts & Estate Office, {INSTITUTION_NAME}', 1);

-- 14. Demo Bookings
-- Booking 1: Approved & Room Allocated (VIP-101) for ICAR External Examiner
INSERT INTO bookings (
    id, booking_reference, applicant_id, submitted_by_id, guest_house_id, room_type_id, guest_type_id, expenditure_head_id, approval_workflow_id,
    purpose, arrival_date, arrival_time, departure_date, departure_time, billable_nights, rooms_requested, total_guests,
    meal_preference, special_requests, accepted_terms, status, current_step_order,
    applied_tariff_per_night, total_room_charges, total_tax_amount, total_other_charges, grand_total, paid_amount, balance_amount,
    payment_stage_applied, payment_status, created_at, updated_at
) VALUES (
    1, 'UGH-2026-0001', 6, 6, 1, 1, 1, 3, 1,
    'External Examiner for PhD Oral Comprehensive Examination in Fruit Science',
    CURDATE() + INTERVAL 2 DAY, '12:00:00', CURDATE() + INTERVAL 4 DAY, '11:00:00', 2, 1, 1,
    'Breakfast', 'Requires early breakfast on day 2. Senior Professor.', 1, 'Room Allocated', 2,
    1000.00, 2000.00, 0.00, 0.00, 2000.00, 0.00, 2000.00,
    'check_out', 'unpaid', NOW() - INTERVAL 2 DAY, NOW()
);

INSERT INTO booking_guests (booking_id, name, designation, phone, email, id_document_type, id_document_number, is_primary, relationship) VALUES
(1, 'Prof. Arvind K. Joshi', 'Head of Division (Horticulture), IARI New Delhi', '9419100001', 'arvind.joshi@iari.res.in', 'Govt Employee ID', 'IARI-FAC-409', 1, 'Examiner');

INSERT INTO booking_rooms (booking_id, room_id, allocated_by_id, allocated_at, status) VALUES
(1, 1, 3, NOW() - INTERVAL 4 HOUR, 'Allocated');

INSERT INTO booking_status_history (booking_id, from_status, to_status, action, actor_id, remarks, workflow_stage, created_at) VALUES
(1, 'Draft', 'Submitted', 'Submitted', 6, 'Submitted request along with Dean sanction memo', 'Submission', NOW() - INTERVAL 48 HOUR),
(1, 'Submitted', 'Verification Pending', 'Forwarded', 4, 'Documents verified against ICAR examiner panel', 'Verification', NOW() - INTERVAL 36 HOUR),
(1, 'Verification Pending', 'Approval Pending', 'Verified', 4, 'Eligible for Category A official rate under Dean Exam head', 'Verification', NOW() - INTERVAL 24 HOUR),
(1, 'Approval Pending', 'Approved', 'Approved', 5, 'Approved as external examiner', 'Approval', NOW() - INTERVAL 12 HOUR),
(1, 'Approved', 'Room Allocated', 'Room Allocated', 3, 'VIP Suite 101 allocated for 2 nights', 'Allocation', NOW() - INTERVAL 4 HOUR);

-- Booking 2: Active Checked-in Stay in FGH-201
INSERT INTO bookings (
    id, booking_reference, applicant_id, submitted_by_id, guest_house_id, room_type_id, guest_type_id, expenditure_head_id, approval_workflow_id,
    purpose, arrival_date, arrival_time, departure_date, departure_time, billable_nights, rooms_requested, total_guests,
    meal_preference, special_requests, accepted_terms, status, current_step_order,
    applied_tariff_per_night, total_room_charges, total_tax_amount, total_other_charges, grand_total, paid_amount, balance_amount,
    payment_stage_applied, payment_status, created_at, updated_at
) VALUES (
    2, 'UGH-2026-0002', 6, 6, 2, 3, 2, 2, 1,
    'Collaborative research meeting on Agro-forestry satellite imagery models',
    CURDATE(), '11:30:00', CURDATE() + INTERVAL 2 DAY, '11:00:00', 2, 1, 1,
    'All Meals (Breakfast, Lunch, Dinner)', 'Vegetarian meals only', 1, 'Checked In', 2,
    500.00, 1000.00, 0.00, 0.00, 1000.00, 0.00, 1000.00,
    'check_out', 'unpaid', NOW() - INTERVAL 5 DAY, NOW()
);

INSERT INTO booking_guests (booking_id, name, designation, phone, email, id_document_type, id_document_number, is_primary, relationship) VALUES
(2, 'Dr. Sunita Deshmukh', 'Scientist-E, Forest Research Institute (FRI) Dehradun', '9897000002', 'deshmukhs@fri.res.in', 'Aadhaar Card', 'XXXX-XXXX-8921', 1, 'Collaborator');

INSERT INTO booking_rooms (booking_id, room_id, allocated_by_id, allocated_at, checked_in_at, status, room_condition_on_checkin) VALUES
(2, 8, 3, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 2 HOUR, 'Occupied', 'Clean, fresh linen provided, keys handed over.');

INSERT INTO booking_status_history (booking_id, from_status, to_status, action, actor_id, remarks, workflow_stage, created_at) VALUES
(2, 'Draft', 'Submitted', 'Submitted', 6, 'Request for FRI collaborator', 'Submission', NOW() - INTERVAL 5 DAY),
(2, 'Submitted', 'Approved', 'Approved', 5, 'Approved under DOR research collaborative visit', 'Approval', NOW() - INTERVAL 3 DAY),
(2, 'Approved', 'Room Allocated', 'Room Allocated', 3, 'FGH-201 assigned', 'Allocation', NOW() - INTERVAL 1 DAY),
(2, 'Room Allocated', 'Checked In', 'Checked In', 3, 'Guest arrived at 11:30 AM, ID verified.', 'Operations', NOW() - INTERVAL 2 HOUR);

-- Booking 3: Verification Pending
INSERT INTO bookings (
    id, booking_reference, applicant_id, submitted_by_id, guest_house_id, room_type_id, guest_type_id, expenditure_head_id, approval_workflow_id,
    purpose, arrival_date, arrival_time, departure_date, departure_time, billable_nights, rooms_requested, total_guests,
    meal_preference, special_requests, accepted_terms, status, current_step_order,
    applied_tariff_per_night, total_room_charges, total_tax_amount, total_other_charges, grand_total, paid_amount, balance_amount,
    payment_stage_applied, payment_status, created_at, updated_at
) VALUES (
    3, 'UGH-2026-0003', 6, 6, 2, 2, 4, 5, 1,
    'Personal family visit during annual university flower show',
    CURDATE() + INTERVAL 5 DAY, '14:00:00', CURDATE() + INTERVAL 7 DAY, '11:00:00', 2, 1, 2,
    'None', 'Require quiet room, parents are elderly', 1, 'Verification Pending', 1,
    1500.00, 3000.00, 360.00, 0.00, 3360.00, 0.00, 3360.00,
    'check_out', 'unpaid', NOW() - INTERVAL 6 HOUR, NOW()
);

INSERT INTO booking_guests (booking_id, name, designation, phone, email, id_document_type, id_document_number, is_primary, relationship) VALUES
(3, 'Sh. O.P. Sharma', 'Retired Govt Officer (Applicant Father)', '9418012345', 'opsharma@gmail.com', 'Aadhaar Card', 'XXXX-XXXX-1122', 1, 'Parent'),
(4, 'Smt. Kamla Sharma', 'Homemaker (Applicant Mother)', '9418012346', NULL, 'Aadhaar Card', 'XXXX-XXXX-1123', 0, 'Mother');

INSERT INTO booking_status_history (booking_id, from_status, to_status, action, actor_id, remarks, workflow_stage, created_at) VALUES
(3, 'Draft', 'Submitted', 'Submitted', 6, 'Request submitted for Category D stay', 'Submission', NOW() - INTERVAL 6 HOUR),
(3, 'Submitted', 'Verification Pending', 'Routed', 4, 'Awaiting scrutiny by Dr. Meenakshi Thakur', 'Verification', NOW() - INTERVAL 5 HOUR);

-- Booking 4: Checked Out & Fully Settled (Invoice + Payment)
INSERT INTO bookings (
    id, booking_reference, applicant_id, submitted_by_id, guest_house_id, room_type_id, guest_type_id, expenditure_head_id, approval_workflow_id,
    purpose, arrival_date, arrival_time, departure_date, departure_time, billable_nights, rooms_requested, total_guests,
    meal_preference, special_requests, accepted_terms, status, current_step_order,
    applied_tariff_per_night, total_room_charges, total_tax_amount, total_other_charges, grand_total, paid_amount, balance_amount,
    payment_stage_applied, payment_status, created_at, updated_at
) VALUES (
    4, 'UGH-2026-0004', 6, 6, 2, 2, 3, 5, 1,
    'Keynote speaker for National Symposium on Mountain Agro-ecosystems',
    CURDATE() - INTERVAL 3 DAY, '12:00:00', CURDATE() - INTERVAL 1 DAY, '10:30:00', 2, 1, 1,
    'Breakfast & Dinner', 'Symposium guest', 1, 'Closed', 2,
    1200.00, 2400.00, 288.00, 300.00, 2988.00, 2988.00, 0.00,
    'check_out', 'paid', NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 1 DAY
);

INSERT INTO booking_guests (booking_id, name, designation, phone, email, id_document_type, id_document_number, is_primary, relationship) VALUES
(5, 'Dr. R.P. Bhattacharya', 'Professor, GB Pant University Pantnagar', '9412000004', 'rpbhatt@gbpuat.ac.in', 'Passport', 'Z9876543', 1, 'Keynote Speaker');

INSERT INTO booking_rooms (booking_id, room_id, allocated_by_id, allocated_at, checked_in_at, checked_out_at, status, room_condition_on_checkin, room_condition_on_checkout) VALUES
(3, 7, 3, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 1 DAY, 'Vacated', 'Room delivered in good condition', 'Room inspected, no damages, all keys returned');

INSERT INTO invoices (id, invoice_number, booking_id, invoice_date, subtotal, tax_total, additional_charges, discount_or_waiver, grand_total, paid_amount, balance_due, status, is_finalized, generated_by_id) VALUES
(1, 'INV-2026-0001', 4, CURDATE() - INTERVAL 1 DAY, 2400.00, 288.00, 300.00, 0.00, 2988.00, 2988.00, 0.00, 'Paid', 1, 7);

INSERT INTO invoice_items (invoice_id, item_description, item_type, unit_rate, quantity, line_total, tax_id, tax_rate_applied) VALUES
(1, 'Deluxe AC Room (FGH-102) Stay (2 nights @ ₹1200)', 'room_charge', 1200.00, 2, 2400.00, NULL, 0.00),
(1, 'CGST 6% on Room Charge', 'tax', 144.00, 1, 144.00, 2, 6.00),
(1, 'SGST 6% on Room Charge', 'tax', 144.00, 1, 144.00, 3, 6.00),
(1, 'Special Dining / Dinner supplement charges', 'meal', 300.00, 1, 300.00, NULL, 0.00);

INSERT INTO payments (id, receipt_number, invoice_id, booking_id, payment_method, transaction_reference, payment_date, amount, status, notes, collected_by_id) VALUES
(1, 'REC-2026-0001', 1, 4, 'UPI / QR', 'UPI/2026/891238910', CURDATE() - INTERVAL 1 DAY, 2988.00, 'Successful', 'Settled via GooglePay QR at front desk', 7);

INSERT INTO booking_status_history (booking_id, from_status, to_status, action, actor_id, remarks, workflow_stage, created_at) VALUES
(4, 'Draft', 'Submitted', 'Submitted', 6, 'Keynote speaker lodging request', 'Submission', NOW() - INTERVAL 10 DAY),
(4, 'Submitted', 'Approved', 'Approved', 5, 'Approved', 'Approval', NOW() - INTERVAL 7 DAY),
(4, 'Approved', 'Room Allocated', 'Room Allocated', 3, 'FGH-102 allocated', 'Allocation', NOW() - INTERVAL 5 DAY),
(4, 'Room Allocated', 'Checked In', 'Checked In', 3, 'Arrived on schedule', 'Operations', NOW() - INTERVAL 3 DAY),
(4, 'Checked In', 'Checked Out', 'Checked Out', 3, 'Departed, room verified', 'Operations', NOW() - INTERVAL 1 DAY),
(4, 'Checked Out', 'Closed', 'Closed & Paid', 7, 'Full bill ₹2988 received against REC-2026-0001', 'Finance', NOW() - INTERVAL 1 DAY);

-- 15. Initial Audit Logs
INSERT INTO audit_logs (actor_id, actor_name, actor_role, action, module, auditable_type, auditable_id, before_values, after_values, ip_address) VALUES
(1, 'Dr. Rajesh Sharma', 'system_admin', 'Database Seeded', 'System', 'App\\Models\\SystemSetting', 1, NULL, '{"status": "System initialized for Dr. YSP UHF Nauni"}', '127.0.0.1'),
(3, 'Sh. Anil Kumar', 'guest_house_incharge', 'Allocated Room', 'Allocation', 'App\\Models\\BookingRoom', 1, NULL, '{"booking_id": 1, "room": "VIP-101"}', '127.0.0.1'),
(7, 'Sh. Ramesh Chand', 'accounts_finance', 'Payment Collected', 'Finance', 'App\\Models\\Payment', 1, NULL, '{"amount": 2988.00, "receipt": "REC-2026-0001"}', '127.0.0.1');

SET FOREIGN_KEY_CHECKS = 1;

