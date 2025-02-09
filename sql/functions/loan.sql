/*
    Registers a loan for the given patron with the given book.
    The loaned copy is picked with no criteria.
    If one or more preferred branches are specified, the copy has to belong
    to one of those.
 */
CREATE OR REPLACE FUNCTION make_loan(
    _book book.ISBN%TYPE,
    _patron patron.USER%TYPE,
    _preferred_branches INT[] DEFAULT NULL
)
    RETURNS TABLE
            (
                _loaned_copy book_copy.ID%TYPE,
                _loan_branch branch.ID%TYPE
            )
    LANGUAGE plpgsql
AS
$$
DECLARE
    _copy              BOOK_COPY%ROWTYPE;
    _available_copies  BOOK_COPY%ROWTYPE[];
    _possible_branches branch.ID%TYPE[];
BEGIN

    IF _preferred_branches IS NULL THEN
        _possible_branches := ARRAY(SELECT id FROM branch);
    ELSE
        _possible_branches := _preferred_branches;
    END IF;

    _available_copies := ARRAY(
            SELECT ROW (id, branch, book, removed)
            FROM book_copy
            WHERE book = _book
              AND id NOT IN (SELECT copy
                             FROM loan
                             WHERE returned IS NULL)
              AND removed = FALSE
                         );

    IF ARRAY_LENGTH(_available_copies, 1) IS NULL THEN
        RAISE EXCEPTION 'There are no copies available for book %.', _book;
    END IF;

    FOREACH _copy IN ARRAY _available_copies
        LOOP

            IF _copy.branch = ANY (_possible_branches) THEN
                INSERT INTO loan (patron, copy)
                VALUES (_patron, _copy.id);

                RETURN QUERY SELECT _copy.id, _copy.branch;

                RETURN;
            END IF;

        END LOOP;

    RAISE EXCEPTION 'No copies available in specified branches for book %.',
        _book;
END;
$$;