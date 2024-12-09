/*
    Given a branch, returns a table containing expired loans
    and the corresponding patrons.
 */
CREATE OR REPLACE FUNCTION delays(my_branch branch.ID%TYPE)
    RETURNS TABLE
            (
                PATRON patron.USER%TYPE,
                BOOK   book.ISBN%TYPE,
                COPY   book_copy.ID%TYPE,
                DUE    loan.DUE%TYPE
            )
    LANGUAGE plpgsql
AS
$$
BEGIN
    RETURN QUERY
        SELECT loan.patron AS patron, bc.book AS book, bc.id AS copy, loan.due as due
        FROM loan
                 INNER JOIN library.book_copy bc ON bc.id = loan.copy
        WHERE returned IS NULL
          AND bc.branch = my_branch
          AND NOW() > loan.due;
END;
$$;