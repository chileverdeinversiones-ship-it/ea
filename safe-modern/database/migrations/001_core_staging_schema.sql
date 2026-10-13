-- SAFE Moderno: esquema inicial de referencia para STAGING.
-- NO ejecutar sobre la base SAFE original ni sobre producción.
-- Revisar y versionar cada cambio; probar primero en una base desechable.
SET NAMES utf8mb4;
SET time_zone = '-06:00';

CREATE TABLE app_users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(160) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_app_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clients (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_code VARCHAR(40) NOT NULL,
    person_type ENUM('individual','legal_entity') NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    cui_dpi VARCHAR(30) NULL,
    nit VARCHAR(30) NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    status ENUM('draft','active','blocked','closed') NOT NULL DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clients_code (client_code),
    KEY ix_clients_name (full_name),
    KEY ix_clients_nit (nit),
    KEY ix_clients_cui_dpi (cui_dpi),
    CONSTRAINT fk_clients_created_by FOREIGN KEY (created_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE credit_products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_code VARCHAR(40) NOT NULL,
    name VARCHAR(160) NOT NULL,
    annual_rate DECIMAL(9,6) NOT NULL,
    day_count_basis SMALLINT UNSIGNED NOT NULL DEFAULT 365,
    payment_frequency ENUM('weekly','biweekly','monthly','quarterly','custom') NOT NULL DEFAULT 'monthly',
    late_fee_method ENUM('fixed','annual_rate','daily_rate','manual_review') NOT NULL DEFAULT 'manual_review',
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_credit_products_code (product_code),
    CONSTRAINT ck_credit_products_rate CHECK (annual_rate >= 0),
    CONSTRAINT ck_credit_products_basis CHECK (day_count_basis IN (360,365,366)),
    CONSTRAINT fk_credit_products_created_by FOREIGN KEY (created_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE loans (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    loan_number VARCHAR(50) NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    principal_original DECIMAL(15,2) NOT NULL,
    principal_outstanding DECIMAL(15,2) NOT NULL,
    interest_due DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    late_fee_due DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('application','approved','disbursed','delinquent','restructured','paid_off','cancelled') NOT NULL DEFAULT 'application',
    disbursed_at DATETIME NULL,
    maturity_date DATE NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_loans_number (loan_number),
    KEY ix_loans_client_status (client_id, status),
    CONSTRAINT ck_loans_principal CHECK (principal_original >= 0 AND principal_outstanding >= 0),
    CONSTRAINT ck_loans_due CHECK (interest_due >= 0 AND late_fee_due >= 0),
    CONSTRAINT fk_loans_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_loans_product FOREIGN KEY (product_id) REFERENCES credit_products(id),
    CONSTRAINT fk_loans_created_by FOREIGN KEY (created_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payment_receipts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    receipt_number VARCHAR(60) NOT NULL,
    loan_id BIGINT UNSIGNED NOT NULL,
    amount_received DECIMAL(15,2) NOT NULL,
    effective_at DATETIME NOT NULL,
    status ENUM('pending','posted','reversed') NOT NULL DEFAULT 'pending',
    idempotency_key CHAR(64) NOT NULL,
    received_by BIGINT UNSIGNED NOT NULL,
    posted_at DATETIME NULL,
    reversed_by BIGINT UNSIGNED NULL,
    reversal_reason VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payment_receipt_number (receipt_number),
    UNIQUE KEY uq_payment_idempotency (idempotency_key),
    KEY ix_payment_loan_effective (loan_id, effective_at),
    CONSTRAINT ck_payment_amount CHECK (amount_received > 0),
    CONSTRAINT fk_payment_loan FOREIGN KEY (loan_id) REFERENCES loans(id),
    CONSTRAINT fk_payment_received_by FOREIGN KEY (received_by) REFERENCES app_users(id),
    CONSTRAINT fk_payment_reversed_by FOREIGN KEY (reversed_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payment_allocations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    receipt_id BIGINT UNSIGNED NOT NULL,
    concept ENUM('late_fee','interest','principal','excess') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_receipt_concept (receipt_id, concept),
    CONSTRAINT ck_payment_allocation_amount CHECK (amount >= 0),
    CONSTRAINT fk_payment_alloc_receipt FOREIGN KEY (receipt_id) REFERENCES payment_receipts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id BIGINT UNSIGNED NULL,
    event_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(100) NULL,
    correlation_id CHAR(36) NOT NULL,
    details JSON NULL,
    occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_audit_entity (entity_type, entity_id),
    KEY ix_audit_actor_time (actor_user_id, occurred_at),
    KEY ix_audit_correlation (correlation_id),
    CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: no se incluyen aún asientos contables, captaciones, solicitudes, 5 C,
-- garantías, avalúos, plan de pagos ni autenticación HTTP; se diseñan después
-- de acordar el catálogo contable y validar reglas reales del producto.
