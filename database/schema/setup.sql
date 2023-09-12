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
    from_user INTEGER NOT NULL,
    to_user INTEGER NOT NULL,
    shared_key VARCHAR(64) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (from_user, to_user),
    FOREIGN KEY conversations(from_user) REFERENCES users(id),
    FOREIGN KEY conversations(to_user) REFERENCES users(id)
);

CREATE TABLE messages (
    id INTEGER AUTO_INCREMENT NOT NULL,
    conversation INTEGER NOT NULL,
    datetime DATETIME NOT NULL,
    content TEXT NOT NULL,
    sender INTEGER NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (conversation),
    FOREIGN KEY messages(conversation) REFERENCES conversations(id),
    FOREIGN KEY messages(sender) REFERENCES users(id)
);