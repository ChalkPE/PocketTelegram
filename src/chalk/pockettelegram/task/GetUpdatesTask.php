<?php

/*
 * Copyright (C) 2015  ChalkPE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @author ChalkPE <chalkpe@gmail.com>
 * @since 2015-10-20 23:43
 */

namespace chalk\pockettelegram\task;

use chalk\pockettelegram\PocketTelegram;
use chalk\pockettelegram\event\TelegramMessageEvent;
use chalk\pockettelegram\model\Update;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class GetUpdatesTask extends PluginTask {
    /** @var Update|null */
    private $lastUpdate = null;

    public function __construct(){
        parent::__construct(PocketTelegram::getInstance());
    }

    public function onRun($currentTick){
        PocketTelegram::request("getUpdates", is_null($this->lastUpdate) ? [] : [
            'offset' => $this->lastUpdate->getUpdateId() + 1
        ], function($raw){
            $response = json_decode($raw);
            if(!isset($response->ok) or $response->ok !== true) return;

            foreach($response->result as $result){
                $update = Update::create($result);
                $this->lastUpdate = $update;

                if(is_null($update->getMessage())) continue;
                Server::getInstance()->getPluginManager()->callEvent(new TelegramMessageEvent($update->getMessage()));
            }

            PocketTelegram::getUpdates();
        });
    }
}