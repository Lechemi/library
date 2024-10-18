CREATE TABLE patron_category
(
    "name"     VARCHAR(50) PRIMARY KEY CHECK ("name" ~* '^.+$'),
    loan_limit SMALLINT NOT NULL
);