-- Verify if tenant exists
-- Returns: bool

DROP FUNCTION IF EXISTS bb_sys.tenant_verify(i_tenant_id uuid);

CREATE OR REPLACE FUNCTION bb_sys.tenant_verify(i_tenant_id uuid)
    RETURNS bool
    SECURITY DEFINER
AS $$
BEGIN
    RETURN exists(
        SELECT t.id
        FROM bb_sys.tenants t
        WHERE t.id = i_tenant_id AND t.is_active = 't' AND t.deleted_at IS NULL
    );
END
$$ LANGUAGE plpgsql;

GRANT EXECUTE ON FUNCTION bb_sys.tenant_verify(i_tenant_id uuid) TO bb_usr;
