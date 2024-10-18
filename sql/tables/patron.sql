CREATE TABLE patron
(
    "user"   UUID PRIMARY KEY REFERENCES "user" (id),
    tax_code VARCHAR(100) NOT NULL CHECK (tax_code ~* '^.+$'),
    n_delays SMALLINT     NOT NULL DEFAULT 0,
    category VARCHAR(50)  NOT NULL REFERENCES patron_category ("name")
);