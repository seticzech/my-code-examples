-- Initialize database server cluster

DO $$
BEGIN
    BEGIN
        RAISE NOTICE 'Creating "bb_adm" role';
        CREATE ROLE bb_adm WITH PASSWORD '%BB_ADM_PASSWORD%';

    EXCEPTION
        WHEN DUPLICATE_OBJECT THEN
            RAISE NOTICE 'Role bb_adm already exists';
    END;

    BEGIN
        RAISE NOTICE 'Creating "bb_erp" role';
        CREATE ROLE bb_erp WITH PASSWORD '%BB_ERP_PASSWORD%';

    EXCEPTION
        WHEN DUPLICATE_OBJECT THEN
            RAISE NOTICE 'Role bb_erp already exists';
    END;

    BEGIN
        RAISE NOTICE 'Creating "bb_sys" role';
        CREATE ROLE bb_sys WITH PASSWORD '%BB_SYS_PASSWORD%';

    EXCEPTION
        WHEN DUPLICATE_OBJECT THEN
            RAISE NOTICE 'Role bb_sys already exists';
    END;


    BEGIN
        RAISE NOTICE 'Creating "bb_usr" role';
        CREATE ROLE bb_usr WITH PASSWORD '%BB_USR_PASSWORD%';

    EXCEPTION
        WHEN DUPLICATE_OBJECT THEN
            RAISE NOTICE 'Role bb_usr already exists';
    END;

    RAISE NOTICE 'Set roles access rights';
    ALTER ROLE bb_adm WITH SUPERUSER CREATEDB CREATEROLE LOGIN;
    ALTER ROLE bb_sys WITH LOGIN;
    ALTER ROLE bb_erp WITH LOGIN;
    ALTER ROLE bb_usr WITH LOGIN;
END
$$;


