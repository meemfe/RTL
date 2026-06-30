<?php
declare(strict_types=1);

/*
 * RTL - Corrects reversed RTL text in Minecraft: Bedrock Edition
 * Copyright (C) 2026 github.com/meemfe/RTL
 * Licensed under GPL-3.0
 */

namespace rtl;

use pocketmine\plugin\PluginBase;

final class Main extends PluginBase {

        private Processor $processor;

        protected function onEnable(): void {
                $this->processor = new Processor();
                $this->getServer()->getPluginManager()->registerEvents(
                        new EventListener($this->processor),
                        $this
                );
        }

        public function getProcessor(): Processor {
                return $this->processor;
        }
}
