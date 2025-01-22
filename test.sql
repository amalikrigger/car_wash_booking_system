CREATE TABLE item_types
(
    id SMALLINT PRIMARY KEY,
    name TEXT
);

CREATE TABLE item_metadata
(
    id   SMALLINT PRIMARY KEY,
    item_type SMALLINT REFERENCES item_types(id),
    name TEXT
);

CREATE TABLE item_metadata_value
(
    id_2 SMALLINT PRIMARY KEY,
    id SMALLINT REFERENCES item_metadata(id),
    value TEXT
);


-- ITEMS TABLE
CREATE TABLE items (
   id SMALLINT PRIMARY KEY,
   name VARCHAR(255) NOT NULL,
   type_id  INT          NOT NULL REFERENCES item_types (id)
);

CREATE TABLE item_metadata_2(
    item_id SMALLINT REFERENCES items(id),
    item_metadata_value SMALLINT REFERENCES item_metadata_value(id_2)
);


INSERT INTO item_types (id, name)
VALUES (1, 'Vehicle'),
       (2, 'Lawn Mower');


INSERT INTO item_metadata
    VALUES (1, 1, 'Make'),
           (2, 1, 'Model');

INSERT INTO item_metadata_value(id_2, id, value)
VALUES
    (1, 1, 'Ford'),
    (2, 1, 'GMC'),
    (3, 1, 'Tesla'),
    (4, 2, 'Ranger'),
    (5, 2, 'Sierra'),
    (6, 2, 'Model 3');

INSERT INTO items(id, name, type_id)
VALUES (1, 'Ford Ranger', 1),
    (2, 'Tesla Model 3', 1);


INSERT INTO item_metadata_2(item_id, item_metadata_value)
VALUES (1, 1),
       (1, 4),
       (2, 3),
       (2, 6);


SELECT * FROM item_metadata_2;


CREATE VIEW V
AS

SELECT DISTINCT im.name, imv.value
FROM item_metadata im
         JOIN item_metadata_value imv on im.id = imv.id
         JOIN item_metadata_2 i on imv.id_2 = i.item_metadata_value
WHERE im.item_type = 1
  AND EXISTS (SELECT *
              FROM item_metadata_2 i2
                       JOIN item_metadata_value imv2
                            ON i2.item_metadata_value = imv2.id_2
                                   AND imv2.value = 'Tesla'
              WHERE i.item_id = i2.item_id)
AND im.name = 'Model';



SELECT *
FROM item_metadata_2 i2
         JOIN item_metadata_value imv2
              ON i2.item_metadata_value = imv2.id_2 AND imv2.value = 'Ford'
WHERE i.item_metadata_value = i2.item_metadata_value

SELECT *
FROM items i
         JOIN item_types it on i.type_id = it.id
--          LEFT JOIN item_metadata im on it.id = im.item_type
 WHERE it.name = 'Vehicle'



CREATE TABLE T1 (id1 SMALLINT PRIMARY KEY , c2 jsonb);
INSERT INTO T1(id1, c2)  VALUES (1, '{"a": "apple", "b": 1}')
INSERT INTO T1(id1, c2)  VALUES (2, '1')

-- filter by b > 0
SELECT c2 ->>'b' FROM T1 WHERE (c2->>'b')::numeric > 0;
SELECT * FROM T1
