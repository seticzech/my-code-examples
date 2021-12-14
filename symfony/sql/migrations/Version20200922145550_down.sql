DELETE FROM bb_erp.core_languages WHERE iso_639_1 IN ('cs', 'en');

DELETE FROM bb_erp.core_modules WHERE code IN ('lmm', 'odm', 'ttm', 'ltm', 'acl', 'core');