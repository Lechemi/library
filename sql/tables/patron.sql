CREATE TABLE patron
(
    "user"   UUID PRIMARY KEY REFERENCES "user" (id),
    tax_code CHAR(16)    NOT NULL CHECK (tax_code ~* '^.+$'),
    n_delays SMALLINT    NOT NULL DEFAULT 0,
    category VARCHAR(50) NOT NULL REFERENCES patron_category ("name")
);

ALTER TABLE patron
    ADD CONSTRAINT check_alphanumeric
        CHECK (patron.tax_code ~ '^[A-Za-z0-9]{16}$');