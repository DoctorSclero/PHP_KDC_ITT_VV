CREATE DATABASE IF NOT EXISTS itt_kdc;
USE itt_kdc;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER AUTO_INCREMENT NOT NULL,
	email VARCHAR(254) NOT NULL,
    salt VARCHAR(32) NOT NULL,
    hash VARCHAR(64) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (email)
);

CREATE TABLE IF NOT EXISTS conversations (
    id INTEGER AUTO_INCREMENT NOT NULL,
    from_user INTEGER NOT NULL,
    to_user INTEGER NOT NULL,
    shared_key VARCHAR(64) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (from_user, to_user),
    FOREIGN KEY (from_user) REFERENCES users(id),
    FOREIGN KEY (to_user) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS messages (
    id INTEGER AUTO_INCREMENT NOT NULL,
    conversation INTEGER NOT NULL,
    datetime DATETIME NOT NULL,
    content TEXT NOT NULL,
    sender INTEGER NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (conversation, datetime),
    FOREIGN KEY (conversation) REFERENCES conversations(id),
    FOREIGN KEY (sender) REFERENCES users(id)
);