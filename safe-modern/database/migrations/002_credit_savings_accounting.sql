-- SAFE Moderno: tablas de referencia adicionales.
-- SOLO para base de staging desechable después de revisar la migración 001.
-- No ejecutar en producción ni sobre la base legacy.
-- Las reglas regulatorias/contables se parametrizan tras confirmar las aplicables.

CREATE TABLE credit_applications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_number VARCHAR(50) NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    requested_amount DECIMAL(15,2) NOT NULL,
    requested_term_months SMALLINT UNSIGNED NOT NULL,
    purpose VARCHAR(500) NOT NULL,
    monthly_income DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    monthly_expenses DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    other_debt_payments DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft','submitted','in_review','committee','approved','rejected','withdrawn','disbursed') NOT NULL DEFAULT 'draft',
    recommendation TEXT NULL,
    submitted_at DATETIME NULL,
    decided_at DATETIME NULL,
    decided_by BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_credit_application_number (application_number),
    KEY ix_credit_application_status (status, created_at),
    CONSTRAINT ck_credit_application_amount CHECK (requested_amount > 0),
    CONSTRAINT ck_credit_application_term CHECK (requested_term_months > 0),
    CONSTRAINT ck_credit_application_income CHECK (monthly_income >= 0 AND monthly_expenses >= 0 AND other_debt_payments >= 0),
    CONSTRAINT fk_application_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_application_product FOREIGN KEY (product_id) REFERENCES credit_products(id),
    CONSTRAINT fk_application_created_by FOREIGN KEY (created_by) REFERENCES app_users(id),
    CONSTRAINT fk_application_decided_by FOREIGN KEY (decided_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE credit_five_cs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED NOT NULL,
    character_score DECIMAL(5,2) NULL,
    character_notes TEXT NULL,
    capacity_score DECIMAL(5,2) NULL,
    capacity_notes TEXT NULL,
    capital_score DECIMAL(5,2) NULL,
    capital_notes TEXT NULL,
    collateral_score DECIMAL(5,2) NULL,
    collateral_notes TEXT NULL,
    conditions_score DECIMAL(5,2) NULL,
    conditions_notes TEXT NULL,
    assessed_by BIGINT UNSIGNED NOT NULL,
    assessed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_five_cs_application (application_id, assessed_at),
    CONSTRAINT fk_five_cs_application FOREIGN KEY (application_id) REFERENCES credit_applications(id),
    CONSTRAINT fk_five_cs_assessed_by FOREIGN KEY (assessed_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE credit_guarantees (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED NOT NULL,
    loan_id BIGINT UNSIGNED NULL,
    guarantee_type ENUM('real_estate','vehicle','pledge','guarantor','deposit','other') NOT NULL,
    description TEXT NOT NULL,
    owner_name VARCHAR(200) NULL,
    declared_value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    realizable_value DECIMAL(15,2) NULL,
    document_reference VARCHAR(190) NULL,
    encumbrances TEXT NULL,
    status ENUM('proposed','under_review','accepted','released','rejected') NOT NULL DEFAULT 'proposed',
    expiry_date DATE NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_guarantee_application (application_id),
    KEY ix_guarantee_loan (loan_id),
    CONSTRAINT ck_guarantee_value CHECK (declared_value >= 0),
    CONSTRAINT fk_guarantee_application FOREIGN KEY (application_id) REFERENCES credit_applications(id),
    CONSTRAINT fk_guarantee_loan FOREIGN KEY (loan_id) REFERENCES loans(id),
    CONSTRAINT fk_guarantee_created_by FOREIGN KEY (created_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE collateral_appraisals (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    guarantee_id BIGINT UNSIGNED NOT NULL,
    appraiser_name VARCHAR(200) NOT NULL,
    appraisal_date DATE NOT NULL,
    market_value DECIMAL(15,2) NOT NULL,
    realizable_value DECIMAL(15,2) NULL,
    report_reference VARCHAR(190) NULL,
    findings TEXT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_appraisal_guarantee_date (guarantee_id, appraisal_date),
    CONSTRAINT ck_appraisal_market_value CHECK (market_value >= 0),
    CONSTRAINT fk_appraisal_guarantee FOREIGN KEY (guarantee_id) REFERENCES credit_guarantees(id),
    CONSTRAINT fk_appraisal_reviewer FOREIGN KEY (reviewed_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE loan_schedule_versions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    loan_id BIGINT UNSIGNED NOT NULL,
    version_number INT UNSIGNED NOT NULL,
    change_reason ENUM('initial','extra_payment_reduce_installment','extra_payment_reduce_term','restructure','correction') NOT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at DATETIME NULL,
    effective_date DATE NOT NULL,
    principal_after_change DECIMAL(15,2) NOT NULL,
    rate_basis_points INT UNSIGNED NOT NULL,
    day_count_basis SMALLINT UNSIGNED NOT NULL,
    policy_version VARCHAR(50) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_schedule_version (loan_id, version_number),
    CONSTRAINT fk_schedule_loan FOREIGN KEY (loan_id) REFERENCES loans(id),
    CONSTRAINT fk_schedule_approved_by FOREIGN KEY (approved_by) REFERENCES app_users(id),
    CONSTRAINT fk_schedule_created_by FOREIGN KEY (created_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE loan_schedule_installments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    schedule_version_id BIGINT UNSIGNED NOT NULL,
    installment_number INT UNSIGNED NOT NULL,
    due_date DATE NOT NULL,
    principal_due DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    interest_due DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    fees_due DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_due DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (id),
    UNIQUE KEY uq_installment_number (schedule_version_id, installment_number),
    KEY ix_installment_due_status (due_date, status),
    CONSTRAINT fk_installment_schedule FOREIGN KEY (schedule_version_id) REFERENCES loan_schedule_versions(id),
    CONSTRAINT ck_installment_amounts CHECK (principal_due >= 0 AND interest_due >= 0 AND fees_due >= 0 AND total_due >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE savings_products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_code VARCHAR(40) NOT NULL,
    name VARCHAR(160) NOT NULL,
    product_type ENUM('on_demand','term_deposit','member_contribution') NOT NULL,
    annual_rate DECIMAL(9,6) NOT NULL DEFAULT 0,
    minimum_opening_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    early_withdrawal_allowed TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_savings_product_code (product_code),
    CONSTRAINT ck_savings_rate CHECK (annual_rate >= 0),
    CONSTRAINT ck_savings_minimum CHECK (minimum_opening_balance >= 0),
    CONSTRAINT fk_savings_product_created_by FOREIGN KEY (created_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE savings_accounts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    account_number VARCHAR(60) NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    accrued_interest DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending','active','blocked','matured','closed') NOT NULL DEFAULT 'pending',
    opened_at DATETIME NULL,
    maturity_date DATE NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_savings_account_number (account_number),
    KEY ix_savings_client_status (client_id, status),
    CONSTRAINT ck_savings_balance CHECK (balance >= 0 AND accrued_interest >= 0),
    CONSTRAINT fk_savings_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_savings_product FOREIGN KEY (product_id) REFERENCES savings_products(id),
    CONSTRAINT fk_savings_created_by FOREIGN KEY (created_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE savings_movements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    account_id BIGINT UNSIGNED NOT NULL,
    movement_type ENUM('deposit','withdrawal','transfer_in','transfer_out','interest_credit','fee','reversal') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    reference_number VARCHAR(80) NOT NULL,
    status ENUM('pending','posted','reversed') NOT NULL DEFAULT 'pending',
    effective_at DATETIME NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_savings_movement_reference (reference_number),
    KEY ix_savings_movement_account_time (account_id, effective_at),
    CONSTRAINT ck_savings_movement_amount CHECK (amount > 0),
    CONSTRAINT fk_savings_movement_account FOREIGN KEY (account_id) REFERENCES savings_accounts(id),
    CONSTRAINT fk_savings_movement_user FOREIGN KEY (created_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gl_accounts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    account_code VARCHAR(40) NOT NULL,
    account_name VARCHAR(200) NOT NULL,
    account_type ENUM('asset','liability','equity','income','expense') NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    allows_posting TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_gl_account_code (account_code),
    CONSTRAINT fk_gl_parent FOREIGN KEY (parent_id) REFERENCES gl_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gl_journals (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    journal_number VARCHAR(60) NOT NULL,
    journal_date DATE NOT NULL,
    source_module VARCHAR(50) NOT NULL,
    source_reference VARCHAR(100) NOT NULL,
    description VARCHAR(500) NOT NULL,
    status ENUM('draft','posted','reversed') NOT NULL DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    posted_by BIGINT UNSIGNED NULL,
    posted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_gl_journal_number (journal_number),
    UNIQUE KEY uq_gl_source_reference (source_module, source_reference),
    KEY ix_gl_journal_date_status (journal_date, status),
    CONSTRAINT fk_gl_journal_created_by FOREIGN KEY (created_by) REFERENCES app_users(id),
    CONSTRAINT fk_gl_journal_posted_by FOREIGN KEY (posted_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gl_journal_lines (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    journal_id BIGINT UNSIGNED NOT NULL,
    account_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(300) NOT NULL,
    debit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    credit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    client_id BIGINT UNSIGNED NULL,
    loan_id BIGINT UNSIGNED NULL,
    savings_account_id BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    KEY ix_gl_lines_account (account_id, journal_id),
    CONSTRAINT ck_gl_line_one_side CHECK ((debit > 0 AND credit = 0) OR (credit > 0 AND debit = 0)),
    CONSTRAINT fk_gl_line_journal FOREIGN KEY (journal_id) REFERENCES gl_journals(id),
    CONSTRAINT fk_gl_line_account FOREIGN KEY (account_id) REFERENCES gl_accounts(id),
    CONSTRAINT fk_gl_line_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_gl_line_loan FOREIGN KEY (loan_id) REFERENCES loans(id),
    CONSTRAINT fk_gl_line_savings FOREIGN KEY (savings_account_id) REFERENCES savings_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La contabilización debe implementarse como servicio transaccional que valida
-- SUM(debit)=SUM(credit), período abierto, permisos y clave idempotente antes
-- de cambiar gl_journals.status a posted.
