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

INSERT INTO users (email, salt, hash) VALUES
('pietro.bomba@gmail.com', 'alkdjsfdaadlajlhdfkhjaebdvajndlfhwhiva', 'asjdhfkajhsdfkjh')
('luca.bomba@gmail.com', 'asdfasdfasdfasdfasccvzxcvzcxvzcd', 'asjdhfkaasdgajhsdfkjh')
('gianni.bomba@gmail.com', 'alkfasdadgasdfdvasdadndlfhwhiva', 'sdgadsfaewvczxcvarg')

INSERT INTO conversations (from_user, to_user, shared_key) VALUES
(
    SELECT id FROM users WHERE email = 'pietro.bomba@gmail.com',
    SELECT id FROM users WHERE email = 'luca.bomba@gmail.com',
    'dlkajf;alksdjf;alkjdslkaj;dsf'
)