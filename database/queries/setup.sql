CREATE TABLE users (
    id INTEGER AUTO_INCREMENT NOT NULL,
	email VARCHAR(254) NOT NULL,
    salt VARCHAR(32) NOT NULL,
    hash VARCHAR(64) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (email)
);

CREATE TABLE conversations (
    id INTEGER AUTO_INCREMENT NOT NULL,
    first_involved_user VARCHAR(254) NOT NULL,
    second_involved_user VARCHAR(254) NOT NULL,
    shared_key VARCHAR(64) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (first_involved_user, second_involved_user)
)