-- Total number of catalog copies managed by each branch
CREATE VIEW managed_copies AS
SELECT branch.id AS branch, COUNT(book_copy.id) AS n_copies
FROM branch
         LEFT JOIN book_copy ON branch.id = book_copy.branch
WHERE removed IS FALSE
   OR removed IS NULL
GROUP BY branch.id;

-- Total number of different books managed by each branch
CREATE VIEW managed_books AS
SELECT branch.id AS branch, COUNT(DISTINCT book_copy.book) AS n_copies
FROM branch
         LEFT JOIN book_copy ON branch.id = book_copy.branch
WHERE removed IS FALSE
   OR removed IS NULL
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