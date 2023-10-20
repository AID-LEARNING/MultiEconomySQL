<?php

namespace SenseiTarzan\MultiEconomySQL;

use pocketmine\plugin\PluginBase;
use SenseiTarzan\MultiEconomy\libs\SenseiTarzan\DataBase\Component\DataManager;
use SenseiTarzan\MultiEconomySQL\Class\Save\SQLSave;

class Main extends PluginBase
{

    protected function onLoad(): void
    {
        $this->saveResource("config.yml");
    }

    protected function onEnable(): void
    {
        DataManager::getInstance()->setDataSystem(new SQLSave($this), true);
    }

    protected function onDisable(): void
    {
        if (DataManager::getInstance()->getDataSystem() instanceof SQLSave){
            DataManager::getInstance()->getDataSystem()->close();
        }
    }
}