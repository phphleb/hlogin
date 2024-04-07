/* MySQL/MariaDB */

CREATE TABLE IF NOT EXISTS users (
    id int(11) NOT NULL AUTO_INCREMENT,
    regtype int(2) NOT NULL DEFAULT '0',
    confirm int(1) NOT NULL DEFAULT '0',
    email varchar(100) NOT NULL,
    login varchar(100) DEFAULT NULL,
    password varchar(255) NOT NULL,
    name varchar(100) DEFAULT NULL,
    surname varchar(100) DEFAULT NULL,
    phone varchar(30) DEFAULT NULL,
    address varchar(255) DEFAULT NULL,
    promocode varchar(100) DEFAULT NULL,
    ip varchar(50) DEFAULT NULL,
    subscription varchar(1) NOT NULL DEFAULT '0',
    period int(11) NOT NULL DEFAULT '0',
    regdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    newemail varchar(100) default NULL,
    hash varchar(100) DEFAULT NULL,
    code varchar(100) DEFAULT NULL,
    sessionkey varchar(255) DEFAULT NULL,
    PRIMARY KEY AUTO_INCREMENT (id),
    UNIQUE KEY email_address (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS userlogs (
    id int(11) NOT NULL AUTO_INCREMENT,
    parent int(11) NOT NULL,
    regtype int(2) NOT NULL,
    action varchar(25) DEFAULT NULL,
    email varchar(100) NOT NULL,
    ip varchar(50) DEFAULT NULL,
    name varchar(100) DEFAULT NULL,
    surname varchar(100) DEFAULT NULL,
    phone varchar(30) DEFAULT NULL,
    address varchar(255) DEFAULT NULL,
    logdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    description varchar(255) DEFAULT NULL,
    moderatorid int(11) DEFAULT NULL,
    PRIMARY KEY AUTO_INCREMENT (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS adminlogs (
    id int(11) NOT NULL AUTO_INCREMENT,
    changedata varchar(255) DEFAULT NULL,
    previousdata varchar(255) DEFAULT NULL,
    fromtype varchar(10) DEFAULT NULL,
    description varchar(255) DEFAULT NULL,
    moderatorid int(11) DEFAULT NULL,
    logdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY AUTO_INCREMENT (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


/* PostgreSQL */

CREATE TABLE IF NOT EXISTS  users (
    id SERIAL PRIMARY KEY,
    regtype integer NOT NULL DEFAULT '0',
    login varchar(100) DEFAULT NULL,
    confirm integer NOT NULL DEFAULT '0',
    email varchar(100) NOT NULL,
    password varchar(255) NOT NULL,
    name varchar(100) DEFAULT NULL,
    surname varchar(100) DEFAULT NULL,
    phone varchar(30) DEFAULT NULL,
    address varchar(255) DEFAULT NULL,
    promocode varchar(100) DEFAULT NULL,
    ip varchar(50) DEFAULT NULL,
    subscription integer NOT NULL DEFAULT '0',
    period integer NOT NULL DEFAULT '0',
    regdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    newemail varchar(100) default NULL,
    hash varchar(100) DEFAULT NULL,
    code varchar(100) DEFAULT NULL,
    sessionkey varchar(255) DEFAULT NULL,
    UNIQUE (email)
);

CREATE TABLE IF NOT EXISTS userlogs (
    id BIGSERIAL PRIMARY KEY,
    parent integer NOT NULL,
    regtype integer NOT NULL,
    action varchar(25) DEFAULT NULL,
    email varchar(100) NOT NULL,
    ip varchar(50) DEFAULT NULL,
    name varchar(100) DEFAULT NULL,
    surname varchar(100) DEFAULT NULL,
    phone varchar(30) DEFAULT NULL,
    address varchar(255) DEFAULT NULL,
    logdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    description varchar(255) DEFAULT NULL,
    moderatorid integer DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS adminlogs (
    id BIGSERIAL PRIMARY KEY,
    changedata varchar(255) DEFAULT NULL,
    previousdata varchar(255) DEFAULT NULL,
    fromtype varchar(10) DEFAULT NULL,
    description varchar(255) DEFAULT NULL,
    moderatorid int(11) DEFAULT NULL,
    logdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
);


/* SQLite */

CREATE TABLE IF NOT EXISTS users (
     id INTEGER PRIMARY KEY AUTOINCREMENT,
     regtype INTEGER NOT NULL DEFAULT 0,
     confirm INTEGER NOT NULL DEFAULT 0,
     email TEXT NOT NULL,
     login TEXT,
     password TEXT NOT NULL,
     name TEXT,
     surname TEXT,
     phone TEXT,
     address TEXT,
     promocode TEXT,
     ip TEXT,
     subscription TEXT NOT NULL DEFAULT '0',
     period INTEGER NOT NULL DEFAULT 0,
     regdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     newemail TEXT,
     hash TEXT,
     code TEXT,
     sessionkey TEXT,
     UNIQUE(email)
    );

CREATE TABLE IF NOT EXISTS userlogs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    parent INTEGER NOT NULL,
    regtype INTEGER NOT NULL,
    action TEXT,
    email TEXT NOT NULL,
    ip TEXT,
    name TEXT,
    surname TEXT,
    phone TEXT,
    address TEXT,
    logdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    moderatorid INTEGER
);

CREATE TABLE IF NOT EXISTS adminlogs (
     id INTEGER PRIMARY KEY AUTOINCREMENT,
     changedata TEXT,
     previousdata TEXT,
     fromtype TEXT,
     description TEXT,
     moderatorid INTEGER,
     logdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
