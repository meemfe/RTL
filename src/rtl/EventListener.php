<?php
declare(strict_types=1);

/*
 * RTL - Corrects reversed RTL text in Minecraft: Bedrock Edition
 * Copyright (C) 2026 github.com/meemfe/RTL
 * Licensed under GPL-3.0
 */

namespace rtl;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\TextPacket;
use function is_string;

final class EventListener implements Listener {

        // Hardcoded because the class location varies across PMMP versions.
        private const TYPE_TRANSLATION = 2;

        public function __construct(
                private Processor $processor
        ) {}

        public function onDataPacketSend(DataPacketSendEvent $event): void {
                foreach ($event->getPackets() as $packet) {
                        if ($packet instanceof TextPacket) {
                                $this->correctPacket($packet);
                        }
                }
        }

        private function correctPacket(TextPacket $packet): void {
                if ($packet->type === self::TYPE_TRANSLATION) {
                        // Correct parameters, leave the translation key alone.
                        foreach ($packet->parameters as $i => $param) {
                                if (is_string($param)) {
                                        $packet->parameters[$i] = $this->processor->correct($param);
                                }
                        }
                } elseif ($packet->message !== "") {
                        $packet->message = $this->processor->correct($packet->message);
                }
        }

        public function onSignChange(SignChangeEvent $event): void {
                foreach ($event->getLines() as $i => $line) {
                        $event->setLine($i, $this->processor->correct($line));
                }
        }
}
