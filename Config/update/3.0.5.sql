-- Widen the 404 report columns: they hold request data (path, user agent, referer),
-- which is unbounded, while VARCHAR(255) was not. An overflowing value raised
-- SQLSTATE 22001 and turned the 404 into a 500.
-- ALTER, not DROP: the recorded 404 history is kept.

ALTER TABLE `rewriteurl_error_url`
    MODIFY `url_source` VARCHAR(1024) NOT NULL,
    MODIFY `user_agent` VARCHAR(512) NOT NULL;

ALTER TABLE `rewriteurl_error_url_referer`
    MODIFY `referer` VARCHAR(1024) NOT NULL;
