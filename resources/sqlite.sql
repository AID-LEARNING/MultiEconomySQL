-- #!sqlite
-- #{ init
CREATE TABLE IF NOT EXISTS Economy (
   player TEXT PRIMARY KEY COLLATE NOCASE,
   money REAL
);
-- #&
CREATE INDEX IF NOT EXISTS idx_player ON Economy (player);
-- #}

-- #{ all
    -- #{ balance
    -- #	:player string
    SELECT money FROM Economy WHERE player = :player;
    -- #}
-- #}

-- #{ money
    -- #{ add
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT OR REPLACE INTO Economy (player, money)
    VALUES (:player, COALESCE((SELECT money FROM Economy WHERE player = :player), :default) + COALESCE(:amount, 0.0));
    -- #}

    -- #{ subtract
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT OR REPLACE INTO Economy (player, money)
    VALUES (:player, COALESCE((SELECT money FROM Economy WHERE player = :player), :default) - COALESCE(:amount, 0.0));
    -- #}

    -- #{ set
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT OR REPLACE INTO Economy (player, money)
    VALUES (:player, COALESCE(:amount, :default));
    -- #}

    -- #{ multiply
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT OR REPLACE INTO Economy (player, money)
    VALUES (:player, COALESCE((SELECT money FROM Economy WHERE player = :player), :default) * COALESCE(:amount, 1.0));
    -- #}

    -- #{ division
    -- #	:player string
    -- #	:amount float
    -- #	:default float
    INSERT OR REPLACE INTO Economy (player, money)
    VALUES (:player, COALESCE((SELECT money FROM Economy WHERE player = :player), :default) / COALESCE(:amount, 1.0));
    -- #}
    -- #{ balance
    -- #	:player string
    SELECT money FROM Economy WHERE player = :player;
    -- #}
    -- #{ top
    -- #	:limit int
    SELECT player, COALESCE(money, 0.0) AS money FROM Economy ORDER BY COALESCE(money, 0.0) DESC LIMIT :limit;
    -- #}
-- #}