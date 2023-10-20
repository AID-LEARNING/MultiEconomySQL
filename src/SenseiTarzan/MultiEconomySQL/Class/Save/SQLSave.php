<?php

namespace SenseiTarzan\MultiEconomySQL\Class\Save;

use Generator;
use PHPUnit\Event\Code\Throwable;
use pmmp\thread\ThreadSafeArray;
use pocketmine\player\Player;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;
use SenseiTarzan\MultiEconomy\Class\Exception\EconomyUpdateException;
use SenseiTarzan\MultiEconomy\Class\Save\IDataSaveEconomy;
use SenseiTarzan\MultiEconomy\Component\EcoPlayerManager;
use SenseiTarzan\MultiEconomy\Component\MultiEconomyManager;
use SenseiTarzan\MultiEconomySQL\Main;
use SOFe\AwaitGenerator\Await;

class SQLSave extends IDataSaveEconomy
{

    private readonly DataConnector $dataConnector;
    public function __construct(Main $pl)
    {
        $this->dataConnector = libasynql::create($pl, $pl->getConfig()->getAll(), [
            "sqlite" => "sqlite.sql",
            "mysql" => "mysql.sql"
        ], true);
        $this->dataConnector->executeGeneric("init");
        $this->dataConnector->waitAll();
    }

    public function getName(): string
    {
        return "MYSQL";
    }

    public function createPromiseEconomy(Player|string $player): Generator
    {
        return Await::promise(function ($resolve, $reject) use ($player) : void{
            Await::g2c($this->dataConnector->asyncSelect("all.balance", ['player' => $player->getName()]), function (array $rows) use ($resolve)
            {
                $resolve(empty($rows) ? [] : $rows[0]);
            }, $reject);
        });
    }

    public function createPromiseGetBalance(Player|string $player, string $economy): Generator
    {
        return  Await::promise(function ($resolve, $reject) use($player, $economy): void{
            $data = EcoPlayerManager::getInstance()->getEcoPlayer($player)?->getEconomy($economy) ?? null;
            if ($data === null){
                Await::g2c($this->dataConnector->asyncSelect("money.balance", ['player' => $player->getName()]), function (array $rows) use ($resolve)
                {
                    $resolve(empty($rows) ? 0 : $rows[0]['money']);
                }, $reject);
                return;
            }
            $resolve($data);
        });
    }

    /**
     * @param string $id
     * @param string $type
     * @param mixed $data
     * @return Generator
     */
    public function createPromiseUpdate(string $id, string $type, mixed $data): Generator
    {
        return Await::promise(function ($resolve, $reject) use ($id, $type, $data) {
                $economyType = strtolower($data["economy"]);
                $default = MultiEconomyManager::getInstance()->getEconomy($economyType)?->getDefault() ?? 0;
                $amount = $data["amount"];
                $type = strtolower($type);
                Await::f2c(function () use ($id,$type, $economyType, $amount, $default): Generator{
                    yield from $this->dataConnector->asyncInsert("$economyType.$type", ["player" => $id, "amount" => $amount, "default" => $default]);
                    return  yield from $this->dataConnector->asyncSelect("$economyType.balance", ['player' => $id]);
                }, function (array $rows) use ($resolve, $economyType, $default) {
                    $resolve(empty($rows) ? $default : $rows[0][$economyType]);
                }, function (\Exception $error) use ($reject) {
                    $reject(new EconomyUpdateException($error->getMessage()));
                });
        });
    }

    function createPromiseTop(string $economy, int $limite): Generator
    {
        return Await::promise(function ($resolve, $reject) use ($economy, $limite): void{

            Await::f2c(function () use ($economy, $limite): Generator{
                $economy = mb_strtolower($economy);
                $rows = yield from $this->dataConnector->asyncSelect("$economy.top", ['limit' => $limite]);
                $top = new ThreadSafeArray();
                foreach ($rows as $row){
                    $top[$row['player']] = $row[$economy];
                }
                return $top;
            }, $resolve, $reject);
        });
    }
    public function close(): void{
        $this->dataConnector->waitAll();
        $this->dataConnector->close();
    }
}