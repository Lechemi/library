CREATE TABLE patron
(
    "user"   UUID               NOT NULL
        PRIMARY KEY
        CONSTRAINT user_id_reference
            REFERENCES "user",
    tax_code VARCHAR(100)       NOT NULL
        CONSTRAINT tax_code_unique
            UNIQUE
        CONSTRAINT patron_tax_code_check
            CHECK ((tax_code)::TEXT ~* '^.+$'::TEXT)
        CONSTRAINT check_alphanumeric
            CHECK ((tax_code)::TEXT ~ '^[A-Za-z0-9]{16}$'::TEXT),
    n_delays SMALLINT DEFAULT 0 NOT NULL,
    category VARCHAR(50)        NOT NULL
        REFERENCES patron_category,
    removed  BOOLEAN  DEFAULT FALSE
);