
CREATE TABLE ACCOUNT (
    ACC_ID integer not null primary key auto_increment,
    ACC_NAME varchar(100) not null
);

INSERT INTO ACCOUNT (ACC_NAME) VALUES ('Bird is the word'),
                                      ('Here is Jonny'),
                                      ('Henrique Angelo');
