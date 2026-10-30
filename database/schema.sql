-- =====================================================================
-- University Guest House Management System (UGHMS)
-- Database Schema (MySQL / MariaDB Compatible)
-- Institution: Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS notification_logs;
DROP TABLE IF EXISTS email_templates;
DROP TABLE IF EXISTS refunds_or_adjustments;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS booking_documents;
DROP TABLE IF EXISTS booking_extensions;
DROP TABLE IF EXISTS booking_status_history;
DROP TABLE IF EXISTS room_blocks;
DROP TABLE IF EXISTS booking_rooms;
DROP TABLE IF EXISTS booking_guests;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS approval_steps;
DROP TABLE IF EXISTS approval_workflows;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS expenditure_heads;
DROP TABLE IF EXISTS taxes;
DROP TABLE IF EXISTS guest_type_tariffs;
DROP TABLE IF EXISTS guest_types;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS room_types;
DROP TABLE IF EXISTS guest_houses;
DROP TABLE IF EXISTS user_has_roles;
DROP TABLE IF EXISTS role_has_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS organization_units;

-- 1. Organization Units
CREATE TABLE organization_units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    type ENUM('College', 'Directorate', 'Department', 'Administrative Office', 'Other') DEFAULT 'Department',
    contact_email VARCHAR(150) NULL,
    contact_phone VARCHAR(50) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Roles & Permissions
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(50) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_has_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_rhp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rhp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Users
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_unit_id BIGINT UNSIGNED NULL,
    employee_id VARCHAR(50) NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    designation VARCHAR(100) NULL,
    department VARCHAR(100) NULL,
    phone VARCHAR(30) NULL,
    primary_role VARCHAR(50) DEFAULT 'employee',
    is_active TINYINT(1) DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (primary_role),
    INDEX idx_user_active (is_active),
    CONSTRAINT fk_user_org FOREIGN KEY (organization_unit_id) REFERENCES organization_units(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_has_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_uhr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_uhr_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
    email VARCHAR(150) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX idx_sess_user (user_id),
    INDEX idx_sess_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Guest Houses
CREATE TABLE guest_houses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    address TEXT NULL,
    eligibility_rules TEXT NULL,
    assigned_incharge_id BIGINT UNSIGNED NULL,
    contact_person VARCHAR(100) NULL,
    contact_phone VARCHAR(50) NULL,
    contact_email VARCHAR(150) NULL,
    status ENUM('draft', 'published', 'deactivated') DEFAULT 'draft',
    is_active TINYINT(1) DEFAULT 1,
    supporting_document_path VARCHAR(255) NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gh_status (status),
    INDEX idx_gh_active (is_active),
    CONSTRAINT fk_gh_incharge FOREIGN KEY (assigned_incharge_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Room Types
CREATE TABLE room_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    default_tariff DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    standard_capacity INT NOT NULL DEFAULT 2,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rt_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Rooms
CREATE TABLE rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guest_house_id BIGINT UNSIGNED NOT NULL,
    room_type_id BIGINT UNSIGNED NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    floor VARCHAR(50) NULL,
    tariff DECIMAL(10,2) NULL,
    max_occupancy INT NOT NULL DEFAULT 2,
    facilities JSON NULL,
    is_active TINYINT(1) DEFAULT 1,
    publication_status ENUM('draft', 'published') DEFAULT 'published',
    maintenance_status ENUM('operational', 'under_maintenance', 'cleaning_in_progress') DEFAULT 'operational',
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_gh_room (guest_house_id, room_number),
    INDEX idx_room_type (room_type_id),
    INDEX idx_room_status (is_active, publication_status, maintenance_status),
    CONSTRAINT fk_room_gh FOREIGN KEY (guest_house_id) REFERENCES guest_houses(id) ON DELETE CASCADE,
    CONSTRAINT fk_room_type FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Guest Types & Tariffs
CREATE TABLE guest_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    priority_order INT NOT NULL DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gt_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE guest_type_tariffs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guest_type_id BIGINT UNSIGNED NOT NULL,
    room_type_id BIGINT UNSIGNED NOT NULL,
    rate_per_night DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    effective_from DATE NULL,
    effective_to DATE NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_guest_room_tariff (guest_type_id, room_type_id),
    CONSTRAINT fk_gtt_gt FOREIGN KEY (guest_type_id) REFERENCES guest_types(id) ON DELETE CASCADE,
    CONSTRAINT fk_gtt_rt FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Taxes & Expenditure Heads
CREATE TABLE taxes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    calculation_method ENUM('percentage', 'fixed') DEFAULT 'percentage',
    rate DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE expenditure_heads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    department VARCHAR(100) NULL,
    description TEXT NULL,
    requires_approval_document TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. System Settings
CREATE TABLE system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NULL,
    `group` VARCHAR(50) NOT NULL DEFAULT 'general',
    label VARCHAR(150) NOT NULL,
    type VARCHAR(30) NOT NULL DEFAULT 'text',
    options TEXT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Approval Workflows & Steps
CREATE TABLE approval_workflows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    service_type VARCHAR(100) NOT NULL DEFAULT 'Guest House Booking',
    organization_unit_id BIGINT UNSIGNED NULL,
    allow_return_for_correction TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_aw_org FOREIGN KEY (organization_unit_id) REFERENCES organization_units(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE approval_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    approval_workflow_id BIGINT UNSIGNED NOT NULL,
    step_order INT NOT NULL DEFAULT 1,
    stage_type ENUM('verifier', 'approver', 'incharge', 'admin') DEFAULT 'verifier',
    role_name VARCHAR(50) NULL,
    specific_user_id BIGINT UNSIGNED NULL,
    step_title VARCHAR(100) NOT NULL,
    can_edit TINYINT(1) DEFAULT 0,
    can_reject TINYINT(1) DEFAULT 1,
    can_return TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_as_flow_step (approval_workflow_id, step_order),
    CONSTRAINT fk_as_flow FOREIGN KEY (approval_workflow_id) REFERENCES approval_workflows(id) ON DELETE CASCADE,
    CONSTRAINT fk_as_user FOREIGN KEY (specific_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Bookings & Guests
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(50) NOT NULL UNIQUE,
    applicant_id BIGINT UNSIGNED NOT NULL,
    submitted_by_id BIGINT UNSIGNED NOT NULL,
    guest_house_id BIGINT UNSIGNED NOT NULL,
    room_type_id BIGINT UNSIGNED NOT NULL,
    guest_type_id BIGINT UNSIGNED NOT NULL,
    expenditure_head_id BIGINT UNSIGNED NULL,
    approval_workflow_id BIGINT UNSIGNED NULL,
    purpose TEXT NOT NULL,
    arrival_date DATE NOT NULL,
    arrival_time TIME NOT NULL DEFAULT '12:00:00',
    departure_date DATE NOT NULL,
    departure_time TIME NOT NULL DEFAULT '11:00:00',
    billable_nights INT NOT NULL DEFAULT 1,
    rooms_requested INT NOT NULL DEFAULT 1,
    total_guests INT NOT NULL DEFAULT 1,
    meal_preference ENUM('None', 'Breakfast', 'Breakfast & Dinner', 'All Meals (Breakfast, Lunch, Dinner)') DEFAULT 'None',
    special_requests TEXT NULL,
    accepted_terms TINYINT(1) DEFAULT 1,
    status ENUM(
        'Draft',
        'Submitted',
        'Verification Pending',
        'Approval Pending',
        'Approved',
        'Room Allocated',
        'Checked In',
        'Checked Out',
        'Closed',
        'Rejected',
        'Returned for Correction',
        'Cancelled',
        'Extension Pending'
    ) DEFAULT 'Draft',
    current_step_order INT NOT NULL DEFAULT 0,
    applied_tariff_per_night DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_room_charges DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_other_charges DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    balance_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_stage_applied ENUM('approval', 'check_in', 'check_out') DEFAULT 'check_out',
    payment_status ENUM('unpaid', 'partially_paid', 'paid', 'exempted', 'billed_to_department') DEFAULT 'unpaid',
    cancellation_reason TEXT NULL,
    cancelled_by_id BIGINT UNSIGNED NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bkg_ref (booking_reference),
    INDEX idx_bkg_app (applicant_id),
    INDEX idx_bkg_gh (guest_house_id),
    INDEX idx_bkg_status (status),
    INDEX idx_bkg_dates (arrival_date, departure_date),
    CONSTRAINT fk_bkg_app FOREIGN KEY (applicant_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bkg_sub FOREIGN KEY (submitted_by_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bkg_gh FOREIGN KEY (guest_house_id) REFERENCES guest_houses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bkg_rt FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bkg_gt FOREIGN KEY (guest_type_id) REFERENCES guest_types(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bkg_exp FOREIGN KEY (expenditure_head_id) REFERENCES expenditure_heads(id) ON DELETE SET NULL,
    CONSTRAINT fk_bkg_wf FOREIGN KEY (approval_workflow_id) REFERENCES approval_workflows(id) ON DELETE SET NULL,
    CONSTRAINT fk_bkg_cb FOREIGN KEY (cancelled_by_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE booking_guests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    designation VARCHAR(100) NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    id_document_type VARCHAR(50) NULL,
    id_document_number VARCHAR(100) NULL,
    is_primary TINYINT(1) DEFAULT 0,
    relationship VARCHAR(50) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bg_booking (booking_id),
    CONSTRAINT fk_bg_bkg FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Booking Rooms & Room Blocks
CREATE TABLE booking_rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    allocated_by_id BIGINT UNSIGNED NOT NULL,
    allocated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    checked_in_at TIMESTAMP NULL,
    checked_out_at TIMESTAMP NULL,
    status ENUM('Allocated', 'Occupied', 'Vacated', 'Released') DEFAULT 'Allocated',
    room_condition_on_checkin TEXT NULL,
    room_condition_on_checkout TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_br_bkg (booking_id),
    INDEX idx_br_room (room_id),
    INDEX idx_br_status (status),
    CONSTRAINT fk_br_bkg FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_br_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
    CONSTRAINT fk_br_user FOREIGN KEY (allocated_by_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE room_blocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id BIGINT UNSIGNED NOT NULL,
    guest_house_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    block_type ENUM('maintenance', 'deep_cleaning', 'vip_hold', 'administrative_block') DEFAULT 'maintenance',
    reason TEXT NOT NULL,
    blocked_by_id BIGINT UNSIGNED NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rb_room (room_id, is_active),
    INDEX idx_rb_dates (start_date, end_date),
    CONSTRAINT fk_rb_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    CONSTRAINT fk_rb_gh FOREIGN KEY (guest_house_id) REFERENCES guest_houses(id) ON DELETE CASCADE,
    CONSTRAINT fk_rb_user FOREIGN KEY (blocked_by_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Status History & Extensions
CREATE TABLE booking_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    from_status VARCHAR(50) NULL,
    to_status VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    remarks TEXT NULL,
    workflow_stage VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bsh_bkg_time (booking_id, created_at),
    CONSTRAINT fk_bsh_bkg FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_bsh_user FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE booking_extensions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    original_departure_date DATE NOT NULL,
    requested_departure_date DATE NOT NULL,
    additional_nights INT NOT NULL,
    additional_charges DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    reason TEXT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    requested_by_id BIGINT UNSIGNED NOT NULL,
    decided_by_id BIGINT UNSIGNED NULL,
    decision_remarks TEXT NULL,
    decided_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ext_bkg (booking_id, status),
    CONSTRAINT fk_ext_bkg FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_ext_req FOREIGN KEY (requested_by_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ext_dec FOREIGN KEY (decided_by_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Booking Documents
CREATE TABLE booking_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    document_title VARCHAR(150) NOT NULL,
    document_type ENUM('approval_letter', 'id_proof', 'duty_slip', 'payment_receipt', 'other') DEFAULT 'approval_letter',
    file_path VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NULL,
    file_size_bytes BIGINT NOT NULL DEFAULT 0,
    uploaded_by_id BIGINT UNSIGNED NOT NULL,
    is_confidential TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_doc_bkg (booking_id, document_type),
    CONSTRAINT fk_doc_bkg FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_doc_user FOREIGN KEY (uploaded_by_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Invoices, Payments, Refunds
CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    booking_id BIGINT UNSIGNED NOT NULL,
    invoice_date DATE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    additional_charges DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_or_waiver DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    balance_due DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('Unpaid', 'Partially Paid', 'Paid', 'Cancelled', 'Refunded') DEFAULT 'Unpaid',
    is_finalized TINYINT(1) DEFAULT 0,
    generated_by_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_inv_num (invoice_number),
    INDEX idx_inv_bkg (booking_id),
    INDEX idx_inv_status (status),
    CONSTRAINT fk_inv_bkg FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_user FOREIGN KEY (generated_by_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE invoice_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    item_description VARCHAR(255) NOT NULL,
    item_type ENUM('room_charge', 'tax', 'meal', 'damage', 'extension', 'adjustment') DEFAULT 'room_charge',
    unit_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantity INT NOT NULL DEFAULT 1,
    line_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_id BIGINT UNSIGNED NULL,
    tax_rate_applied DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ii_inv (invoice_id),
    CONSTRAINT fk_ii_inv FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_ii_tax FOREIGN KEY (tax_id) REFERENCES taxes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED NOT NULL,
    payment_method ENUM(
        'Cash',
        'Online (Payment Gateway)',
        'POS / Card',
        'UPI / QR',
        'Demand Draft / Cheque',
        'Book Transfer / University Account'
    ) DEFAULT 'Cash',
    transaction_reference VARCHAR(100) NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('Successful', 'Pending Gateway', 'Failed', 'Refunded') DEFAULT 'Successful',
    notes TEXT NULL,
    collected_by_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pay_rec (receipt_number),
    INDEX idx_pay_inv (invoice_id),
    INDEX idx_pay_bkg (booking_id),
    INDEX idx_pay_status (status),
    CONSTRAINT fk_pay_inv FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_pay_bkg FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_pay_user FOREIGN KEY (collected_by_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE refunds_or_adjustments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NULL,
    type ENUM('Refund', 'Adjustment', 'Waiver') DEFAULT 'Refund',
    amount DECIMAL(10,2) NOT NULL,
    reason TEXT NOT NULL,
    authorized_by_id BIGINT UNSIGNED NOT NULL,
    reference_number VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ref_inv (invoice_id),
    CONSTRAINT fk_ref_inv FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_ref_pay FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    CONSTRAINT fk_ref_auth FOREIGN KEY (authorized_by_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Notifications & Audit Logs
CREATE TABLE email_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_key VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_template TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notification_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NULL,
    recipient_email VARCHAR(150) NOT NULL,
    recipient_name VARCHAR(150) NULL,
    event VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message_body TEXT NULL,
    delivery_status ENUM('Sent', 'Queued', 'Failed') DEFAULT 'Sent',
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nl_bkg (booking_id),
    INDEX idx_nl_status (delivery_status),
    INDEX idx_nl_sent (sent_at),
    CONSTRAINT fk_nl_bkg FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id BIGINT UNSIGNED NULL,
    actor_name VARCHAR(150) NULL,
    actor_role VARCHAR(50) NULL,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(100) NOT NULL,
    auditable_type VARCHAR(150) NULL,
    auditable_id BIGINT UNSIGNED NULL,
    before_values JSON NULL,
    after_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_al_actor (actor_id),
    INDEX idx_al_module (module),
    INDEX idx_al_action (action),
    INDEX idx_al_auditable (auditable_type, auditable_id),
    INDEX idx_al_time (logged_at),
    CONSTRAINT fk_al_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

