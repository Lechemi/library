CREATE TABLE patron
(
    "user"   UUID               NOT NULL
        PRIMARY KEY
        REFERENCES "user",
    tax_code VARCHAR(100)       NOT NULL
        UNIQUE
        CHECK ((tax_code)::TEXT ~ '^[A-Z0-9]{16}$'::TEXT),
    n_delays SMALLINT DEFAULT 0 NOT NULL,
    category VARCHAR(50)        NOT NULL
        REFERENCES patron_category
);