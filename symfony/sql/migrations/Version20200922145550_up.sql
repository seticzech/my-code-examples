INSERT INTO bb_erp.core_modules (id, code, name)
VALUES
    (uuid_generate_v4(), 'core', 'Core module'),
    (uuid_generate_v4(), 'acl', 'Access control list module'),
    (uuid_generate_v4(), 'ltm', 'Location tracking module'),
    (uuid_generate_v4(), 'ttm', 'Time tracking module'),
    (uuid_generate_v4(), 'odm', 'Offers demands module'),
    (uuid_generate_v4(), 'lmm', 'Learning management module');

INSERT INTO bb_erp.core_languages (id, name, native_name, adverb, iso_639_1, locale, is_active)
VALUES
       (uuid_generate_v4(), 'Czech', 'Čeština', 'česky', 'cs', 'cs_CZ.utf-8', 't'),
       (uuid_generate_v4(), 'English', 'English', 'english', 'en', 'en_US.utf-8', 't');