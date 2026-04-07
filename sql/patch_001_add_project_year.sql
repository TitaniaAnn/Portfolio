-- ============================================================
-- Patch 001 — Add year column to projects
-- Run this against existing installs that were set up before
-- the year field was added to schema.sql.
-- ============================================================

ALTER TABLE projects
  ADD COLUMN year YEAR NULL AFTER sort_order;
