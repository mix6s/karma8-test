DROP TABLE IF EXISTS users;
CREATE TABLE users
(
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    validts INT NOT NULL,
    confirmed SMALLINT NOT NULL,
    PRIMARY KEY (username)
);

DROP TABLE IF EXISTS emails;
CREATE TABLE IF NOT EXISTS emails
(
    email VARCHAR(255) NOT NULL,
    checked SMALLINT NOT NULL,
    valid SMALLINT NOT NULL,
    last_check INT DEFAULT NULL,
    PRIMARY KEY (email)
);

DROP TABLE IF EXISTS sent_notifications;
CREATE TABLE IF NOT EXISTS sent_notifications
(
    username VARCHAR(255) NOT NULL,
    validts INT NOT NULL,
    PRIMARY KEY (username, validts)
);

