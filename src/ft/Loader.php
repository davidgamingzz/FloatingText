<?php

namespace ft;

use ft\command\FloatingTextCommand;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase {

    /** @var Config */
    private $config;

    /** @var FloatingTextParticle[] */
    private $floatingTexts = [];

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->saveResource("text.yml");
        $this->config = new Config($this->getDataFolder() . "area.yml", Config::YAML);
        $this->getServer()->getCommandMap()->register("floatingtext", new FloatingTextCommand($this));
        $this->initParticles();
    }

    public function initParticles() {
        foreach($this->config->getAll() as $floatingText => $data) {
            $level = $this->getServer()->getLevelByName($data["level"]);
            if($level === null) {
                $this->getLogger()->warning("Failed to load floating text with the identifier of $floatingText due to invalid world");
                continue;
            }
            $position = new Position($data["x"], $data["y"], $data["z"], $data["level"]);
            $text = str_replace("&", TextFormat::ESCAPE, $data["text"]);
            $title = str_replace("&", TextFormat::ESCAPE, $data["title"]);
            $this->floatingTexts[(string)$floatingText] = new FloatingTextParticle($position, $text, $title);
            $level->addParticle($this->floatingTexts[(string)$floatingText]);
        }
    }

    /**
     * @param string $identifier
     *
     * @return null|FloatingTextParticle
     */
    public function getFloatingTextById(string $identifier): ?FloatingTextParticle {
        return $this->floatingTexts[$identifier] ?? null;
    }

    /**
     * @param string $identifier
     * @param string $text
     * @param string $title
     * @param Position $position
     */
    public function addFloatingText(string $identifier, string $text, string $title, Position $position) {
        $level = $position->getLevel();
        $this->config->set($identifier);
        $this->config->setNested($identifier . ".x", $position->getFloorX() + 0.5);
        $this->config->setNested($identifier . ".y", $position->getFloorY());
        $this->config->setNested($identifier . ".z", $position->getFloorX() + 0.5);
        $this->config->setNested($identifier . ".level", $level->getName());
        $this->config->setNested($identifier . ".text", $text);
        $this->config->setNested($identifier . ".title", $title);
        $this->config->save();
        $level->addParticle($this->floatingTexts[$identifier]);
        $text = str_replace("&", TextFormat::ESCAPE, $text);
        $title = str_replace("&", TextFormat::ESCAPE, $title);
        $this->floatingTexts[$identifier] = new FloatingTextParticle($position, $text, $title);
    }

    /**
     * @param string $identifier
     */
    public function removeFloatingText(string $identifier) {
        $floatingText = $this->floatingTexts[$identifier];
        $floatingText->setInvisible(true);
        $this->config->removeNested($identifier . ".x");
        $this->config->removeNested($identifier . ".y");
        $this->config->removeNested($identifier . ".z");
        $this->config->removeNested($identifier . ".level");
        $this->config->removeNested($identifier . ".text");
        $this->config->removeNested($identifier . ".title");
        $this->config->remove($identifier);
        $this->config->save();
        unset($this->floatingTexts[$identifier]);
    }
}