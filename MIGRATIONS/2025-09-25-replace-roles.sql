-- Migration: Replace roles with new set
-- Date: 2025-09-25

START TRANSACTION;

-- 1) Normalize existing role values to the new set
UPDATE users SET role = 'admin'  WHERE role IN ('admin','school_administrator');
UPDATE users SET role = 'dean'   WHERE role IN ('department_head');
UPDATE users SET role = 'program_coordinator' WHERE role IN ();
-- NOTE: No direct legacy mapping to 'program_coordinator' was found.
--       If you want to map any legacy roles here, add them to the IN (...) above.

UPDATE users SET role = 'staff'  WHERE role IN ('accreditation_committee','school_accreditation_team','internal_accreditor');
UPDATE users SET role = 'faculty' WHERE role IN ('faculty','user');
UPDATE users SET role = 'external_accreditor' WHERE role IN ('external_accreditor');

-- 2) Update the ENUM definition
ALTER TABLE users 
  MODIFY role ENUM('admin','dean','program_coordinator','faculty','staff','external_accreditor') 
  NOT NULL DEFAULT 'faculty';

COMMIT;
