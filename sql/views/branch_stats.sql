-- Total number of book copies managed by each branch
CREATE VIEW managed_copies AS
SELECT branch.id AS branch, COUNT(bc.id) AS n_copies
FROM branch
         LEFT JOIN (select * from book_copy where removed is false) as bc ON branch.id = bc.branch
GROUP BY branch.id;

-- Total number of different books managed by each branch
CREATE VIEW managed_books AS
SELECT branch.id AS branch, COUNT(DISTINCT bc.book) AS n_books
FROM branch
         LEFT JOIN (select * from book_copy where removed is false) as bc ON branch.id = bc.branch
GROUP BY branch.id;

-- Total number of copies currently loaned by each branch
CREATE VIEW active_loans AS
WITH currently_loaned_copies AS (SELECT id, branch
                                 FROM book_copy
                                 WHERE book_copy.id IN (SELECT copy
                                                        FROM loan
                                                        WHERE returned IS NULL))
SELECT b.id as branch, count(c.id) as active_loans
FROM branch b
         LEFT JOIN currently_loaned_copies c ON b.id = c.branch
GROUP BY b.id;