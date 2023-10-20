-- # !mysql
-- #{ init
CREATE TABLE Economy (
     player VARCHAR(255) PRIMARY KEY,
     money DOUBLE
);
-- #}
-- #{ optimize
OPTIMIZE TABLE Economy;
-- #}
-- #{ all
    -- #{ balance
    -- #	:player string
    SELECT player, money FROM Economy WHERE player = :player;
    -- #}
-- #}

-- #{ money
    -- #{ add
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT INTO Economy (player, money)
    VALUES (:player, :default + :amount)
    ON DUPLICATE KEY UPDATE money = money + :amount;
    -- #}

    -- #{ subtract
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT INTO Economy (player, money)
    VALUES (:player, :default - :amount)
    ON DUPLICATE KEY UPDATE money = money - :amount;
    -- #}

    -- #{ set
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT INTO Economy (player, money)
    VALUES (:player, :amount)
    ON DUPLICATE KEY UPDATE money = :amount;
    -- #}

    -- #{ multiply
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT INTO Economy (player, money)
    VALUES (:player, :default * :amount)
    ON DUPLICATE KEY UPDATE money = money * :amount;
    -- #}

    -- #{ division
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT INTO Economy (player, money)
    VALUES (:player, :default / :amount)
    ON DUPLICATE KEY UPDATE money = money / :amount;
    -- #}

    -- #{ balance
    -- # * Vérifie le solde d'argent pour un joueur donné.
    -- #	:player string
    SELECT money FROM Economy WHERE player = :player;
    -- #}

    -- #{ top
    -- #	:limit int
    SELECT player, money FROM Economy ORDER BY money DESC LIMIT :limit;
    -- #}
-- #}