-- Dummy Admin Data
-- Insert one admin record for testing purposes

INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    010101010,
    'admin01',
    'first_name',
    '',
    'active',
    '{"all": true}',
    NULL
);

INSERT INTO smm_admins (
    chatid,
    username,
    first_name,
    last_name,
    status,
    permissions,
    msg_id
) VALUES (
    123456789,
    'admin02',
    'first_name',
    'last_name',
    'active',
    '{"all": true}',
    NULL
);